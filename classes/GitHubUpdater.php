<?php
/**
 * GitHubUpdater Class
 * Mengambil data pembaruan dari GitHub API (commits, releases, issues, pull requests)
 * @package PELITA
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/github.php';

class GitHubUpdater
{
    private const FETCH_TYPES = ['commits', 'releases', 'issues', 'pulls'];
    private const MAX_RETRIES = 3;
    private const RETRY_DELAY_MS = 1000;

    private string $token;
    private string $owner;
    private string $repo;
    private string $org;
    private array $fetchTypes;
    private string $dataDir;
    private int $cacheTtl;
    private int $perPage;
    private array $logs = [];
    private array $rateLimit = [];
    private bool $authenticated = false;
    private string $logFile;

    public function __construct()
    {
        $this->token = GITHUB_TOKEN;
        $this->owner = GITHUB_OWNER;
        $this->repo = GITHUB_REPO;
        $this->org = GITHUB_ORG;
        $this->cacheTtl = GITHUB_CACHE_TTL;
        $this->perPage = min(GITHUB_PER_PAGE, 100);
        $this->dataDir = GITHUB_DATA_DIR;
        $this->authenticated = !empty($this->token);

        $rawTypes = array_map('trim', explode(',', GITHUB_FETCH_TYPES));
        $this->fetchTypes = array_intersect($rawTypes, self::FETCH_TYPES);

        if (!is_dir($this->dataDir)) {
            mkdir($this->dataDir, 0755, true);
        }

        $this->logFile = $this->dataDir . '/github_fetch_' . date('Y-m-d') . '.log';
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
        $this->authenticated = !empty($token);
    }

    public function setRepository(string $owner, string $repo): void
    {
        $this->owner = $owner;
        $this->repo = $repo;
    }

    public function setOrganization(string $org): void
    {
        $this->org = $org;
    }

    public function setFetchTypes(array $types): void
    {
        $this->fetchTypes = array_intersect($types, self::FETCH_TYPES);
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function getRateLimit(): array
    {
        return $this->rateLimit;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * Ambil semua tipe pembaruan yang dikonfigurasi
     */
    public function fetchAll(): array
    {
        $results = [];

        foreach ($this->fetchTypes as $type) {
            try {
                $results[$type] = $this->fetch($type);
            } catch (Exception $e) {
                $results[$type] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'data' => []
                ];
                $this->log("ERROR fetch $type: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Ambil data pembaruan berdasarkan tipe
     */
    public function fetch(string $type): array
    {
        if (!in_array($type, self::FETCH_TYPES, true)) {
            throw new InvalidArgumentException("Unknown fetch type: $type");
        }

        $endpoint = $this->resolveEndpoint($type);
        if (empty($endpoint)) {
            throw new RuntimeException("Cannot resolve endpoint for type '$type'. Set repository or organization.");
        }

        $cacheKey = "github_{$type}_" . md5($endpoint);
        $cached = $this->loadCache($cacheKey);

        if ($cached !== null) {
            $this->log("Cache HIT for $type");
            return [
                'success' => true,
                'source' => 'cache',
                'type' => $type,
                'data' => $cached
            ];
        }

        $this->log("Cache MISS for $type, fetching from API...");
        $data = $this->fetchFromApi($endpoint);
        $processed = $this->processData($type, $data);

        $this->saveCache($cacheKey, $processed);

        return [
            'success' => true,
            'source' => 'api',
            'type' => $type,
            'data' => $processed
        ];
    }

    private function resolveEndpoint(string $type): string
    {
        $hasRepo = !empty($this->owner) && !empty($this->repo);
        $hasOrg = !empty($this->org);

        if (!$hasRepo && !$hasOrg) {
            return '';
        }

        $perPage = $this->perPage;

        return match ($type) {
            'commits' => $hasRepo
                ? "/repos/{$this->owner}/{$this->repo}/commits?per_page={$perPage}"
                : "/orgs/{$this->org}/events?per_page={$perPage}",
            'releases' => $hasRepo
                ? "/repos/{$this->owner}/{$this->repo}/releases?per_page={$perPage}"
                : '',
            'issues' => $hasRepo
                ? "/repos/{$this->owner}/{$this->repo}/issues?state=all&per_page={$perPage}"
                : "/orgs/{$this->org}/issues?filter=all&state=all&per_page={$perPage}",
            'pulls' => $hasRepo
                ? "/repos/{$this->owner}/{$this->repo}/pulls?state=all&per_page={$perPage}"
                : '',
        };
    }

    private function fetchFromApi(string $endpoint): array
    {
        $url = GITHUB_API_BASE . $endpoint;
        $allData = [];
        $page = 1;

        do {
            $pageUrl = $url . (str_contains($url, '?') ? '&' : '?') . "page=$page";
            $response = $this->makeRequest($pageUrl);

            if (empty($response)) {
                break;
            }

            $allData = array_merge($allData, $response);
            $page++;

            if ($this->perPage > 0 && count($response) < $this->perPage) {
                break;
            }
        } while ($page <= 5);

        return $allData;
    }

    private function makeRequest(string $url): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->buildHeaders(),
            CURLOPT_USERAGENT => GITHUB_USER_AGENT,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ]);

        $attempt = 0;

        do {
            $attempt++;
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);

            if ($error) {
                $this->log("Network error (attempt $attempt): $error");
                if ($attempt < self::MAX_RETRIES) {
                    usleep(self::RETRY_DELAY_MS * 1000);
                    continue;
                }
                curl_close($ch);
                throw new RuntimeException("Network request failed after $attempt attempts: $error");
            }

            $this->updateRateLimit($ch);
            curl_close($ch);

            return $this->handleResponse($httpCode, $response, $url);
        } while ($attempt < self::MAX_RETRIES);

        curl_close($ch);
        return [];
    }

    private function buildHeaders(): array
    {
        $headers = [
            'Accept: application/vnd.github.v3+json',
            'Content-Type: application/json',
        ];

        if ($this->authenticated) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        return $headers;
    }

    private function handleResponse(int $httpCode, string $body, string $url): array
    {
        if ($httpCode === 204) {
            return [];
        }

        if ($httpCode === 404) {
            throw new RuntimeException("Resource not found: $url");
        }

        if ($httpCode === 403) {
            $remaining = $this->rateLimit['remaining'] ?? 0;
            $reset = $this->rateLimit['reset'] ?? time();

            if ($remaining === 0) {
                $waitTime = max(0, $reset - time());
                throw new RuntimeException(
                    "GitHub API rate limit exceeded. Resets in {$waitTime}s. "
                    . ($this->authenticated ? 'Authenticated' : 'Unauthenticated')
                    . " request."
                );
            }

            throw new RuntimeException("Access forbidden (403): $url. Check token permissions.");
        }

        if ($httpCode === 401) {
            throw new RuntimeException("Authentication failed (401). Check your GitHub token.");
        }

        if ($httpCode >= 500) {
            throw new RuntimeException("GitHub server error ($httpCode): $url");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new RuntimeException("Unexpected HTTP status $httpCode from $url");
        }

        $decoded = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Failed to parse GitHub API response: " . json_last_error_msg());
        }

        return $decoded ?? [];
    }

    private function updateRateLimit($ch): void
    {
        $remaining = curl_getinfo($ch, CURLINFO_HEADER_OUT);

        $this->rateLimit = [
            'limit' => (int)(curl_getinfo($ch, CURLINFO_RESPONSE_CODE) ?: 0),
            'remaining' => 0,
            'reset' => 0,
            'used' => 0,
        ];

        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        if ($headerSize > 0) {
            $headers = substr(curl_getinfo($ch, CURLINFO_HEADER_OUT) ?: '', 0, $headerSize);
        }
    }

    public function fetchRateLimitStatus(): array
    {
        try {
            $data = $this->makeRequest(GITHUB_API_BASE . '/rate_limit');
            return $data['rate'] ?? $this->rateLimit;
        } catch (Exception $e) {
            $this->log("Failed to fetch rate limit status: " . $e->getMessage());
            return $this->rateLimit;
        }
    }

    private function processData(string $type, array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $processor = match ($type) {
            'commits' => 'processCommits(...)',
            'releases' => 'processReleases(...)',
            'issues' => 'processIssues(...)',
            'pulls' => 'processPulls(...)',
        };

        return match ($type) {
            'commits' => $this->processCommits($data),
            'releases' => $this->processReleases($data),
            'issues' => $this->processIssues($data),
            'pulls' => $this->processPulls($data),
        };
    }

    private function processCommits(array $commits): array
    {
        return array_map(fn($c) => [
            'sha' => $c['sha'] ?? '',
            'message' => explode("\n", $c['commit']['message'] ?? '')[0],
            'author_name' => $c['commit']['author']['name'] ?? '',
            'author_email' => $c['commit']['author']['email'] ?? '',
            'author_username' => $c['author']['login'] ?? '',
            'date' => $c['commit']['author']['date'] ?? '',
            'url' => $c['html_url'] ?? '',
        ], $commits);
    }

    private function processReleases(array $releases): array
    {
        return array_map(fn($r) => [
            'id' => $r['id'] ?? 0,
            'tag_name' => $r['tag_name'] ?? '',
            'name' => $r['name'] ?? '',
            'body_preview' => mb_substr($r['body'] ?? '', 0, 500),
            'prerelease' => $r['prerelease'] ?? false,
            'draft' => $r['draft'] ?? false,
            'published_at' => $r['published_at'] ?? '',
            'url' => $r['html_url'] ?? '',
            'author' => $r['author']['login'] ?? '',
        ], $releases);
    }

    private function processIssues(array $issues): array
    {
        return array_map(fn($i) => [
            'id' => $i['id'] ?? 0,
            'number' => $i['number'] ?? 0,
            'title' => $i['title'] ?? '',
            'state' => $i['state'] ?? '',
            'labels' => array_map(fn($l) => $l['name'] ?? '', $i['labels'] ?? []),
            'created_at' => $i['created_at'] ?? '',
            'updated_at' => $i['updated_at'] ?? '',
            'url' => $i['html_url'] ?? '',
            'user' => $i['user']['login'] ?? '',
            'comments' => $i['comments'] ?? 0,
        ], $issues);
    }

    private function processPulls(array $pulls): array
    {
        return array_map(fn($p) => [
            'id' => $p['id'] ?? 0,
            'number' => $p['number'] ?? 0,
            'title' => $p['title'] ?? '',
            'state' => $p['state'] ?? '',
            'draft' => $p['draft'] ?? false,
            'created_at' => $p['created_at'] ?? '',
            'updated_at' => $p['updated_at'] ?? '',
            'merged_at' => $p['merged_at'] ?? null,
            'url' => $p['html_url'] ?? '',
            'user' => $p['user']['login'] ?? '',
            'head_branch' => $p['head']['ref'] ?? '',
            'base_branch' => $p['base']['ref'] ?? '',
        ], $pulls);
    }

    public function saveToDatabase(array $results): int
    {
        $db = Database::getInstance();
        $saved = 0;

        foreach ($results as $type => $result) {
            if (empty($result['success']) || empty($result['data'])) {
                continue;
            }

            foreach ($result['data'] as $item) {
                try {
                    $item['fetch_type'] = $type;
                    $item['fetched_at'] = date('Y-m-d H:i:s');

                    if (!isset($item['id'])) {
                        $item['id'] = crc32(json_encode($item));
                    }

                    $db->insert('github_updates', $item);
                    $saved++;
                } catch (Exception $e) {
                    $this->log("DB insert error ($type): " . $e->getMessage());
                }
            }
        }

        $this->log("Saved $saved records to database");
        return $saved;
    }

    public function saveToJson(array $results): string
    {
        $filename = $this->dataDir . '/github_updates_' . date('Y-m-d_H-i-s') . '.json';
        $export = [
            'fetched_at' => date('Y-m-d H:i:s'),
            'repository' => !empty($this->owner) ? "{$this->owner}/{$this->repo}" : null,
            'organization' => $this->org ?: null,
            'results' => $results,
        ];

        file_put_contents(
            $filename,
            json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        );

        $this->log("Saved results to JSON: $filename");
        return $filename;
    }

    private function loadCache(string $key): ?array
    {
        $path = $this->dataDir . '/' . $key . '.cache';

        if (!file_exists($path)) {
            return null;
        }

        $age = time() - filemtime($path);
        if ($age > $this->cacheTtl) {
            unlink($path);
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $decoded = json_decode($content, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function saveCache(string $key, array $data): void
    {
        $path = $this->dataDir . '/' . $key . '.cache';
        file_put_contents($path, json_encode($data, JSON_UNESCAPED_UNICODE), LOCK_EX);
    }

    public function clearCache(?string $type = null): int
    {
        $cleared = 0;
        $pattern = $type
            ? $this->dataDir . "/github_{$type}_*.cache"
            : $this->dataDir . '/github_*.cache';

        foreach (glob($pattern) ?: [] as $file) {
            unlink($file);
            $cleared++;
        }

        $this->log("Cleared $cleared cache files" . ($type ? " for type '$type'" : ''));
        return $cleared;
    }

    public function readRecentLogs(int $maxLines = 50): array
    {
        if (!file_exists($this->logFile)) {
            return [];
        }

        $lines = file($this->logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($lines, -$maxLines);
    }

    private function log(string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $msg = "[$timestamp] $message";
        $this->logs[] = $msg;
        file_put_contents($this->logFile, $msg . "\n", FILE_APPEND | LOCK_EX);

        if (php_sapi_name() === 'cli') {
            echo $msg . "\n";
        }
    }
}
