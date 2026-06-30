<?php
/**
 * PELITA - GitHubUpdater Unit Tests
 * Run: php tests/GitHubUpdaterTest.php
 * @package PELITA
 * @version 1.0.0
 */

declare(strict_types=1);

$_SERVER['HTTP_HOST'] = 'localhost';

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/github.php';
require_once CLASSES_PATH . '/GitHubUpdater.php';

$results = ['passed' => 0, 'failed' => 0];

function test_assert(bool $condition, string $message): void {
    global $results;
    if ($condition) {
        $results['passed']++;
        echo "[PASS] $message\n";
        return;
    }
    $results['failed']++;
    echo "[FAIL] $message\n";
}

function test_exception(callable $fn, string $expectedClass, string $message): void {
    try {
        $fn();
        test_assert(false, "$message (no exception thrown)");
    } catch (Throwable $e) {
        test_assert($e instanceof $expectedClass, "$message - got " . get_class($e) . ": " . $e->getMessage());
    }
}

$updater = new GitHubUpdater();

echo "====== GITHUB UPDATER TESTS ======\n\n";

// === Test 1-3: Authentication ===
echo "--- Authentication ---\n";
test_assert(!$updater->isAuthenticated(), 'Default: not authenticated');

$updater->setToken('ghp_test123token');
test_assert($updater->isAuthenticated(), 'After setToken: authenticated');

$updater->setToken('');
test_assert(!$updater->isAuthenticated(), 'After empty token: not authenticated');

// === Test 4-7: Configuration ===
echo "\n--- Configuration ---\n";
$updater->setRepository('bpsjember', 'pelita');
test_assert(true, 'setRepository accepts owner and repo');

$updater->setOrganization('bpsjember');
test_assert(true, 'setOrganization accepts org name');

$updater->setFetchTypes(['commits', 'releases']);
test_assert(true, 'setFetchTypes accepts valid types');

$updater->setFetchTypes(['commits', 'invalid_type']);
// Should silently ignore invalid types
$ref = new ReflectionClass(GitHubUpdater::class);
$typesProp = $ref->getProperty('fetchTypes');
$typesProp->setAccessible(true);
$currentTypes = $typesProp->getValue($updater);
test_assert(!in_array('invalid_type', $currentTypes, true), 'setFetchTypes ignores invalid types');

// === Test 8-11: Error handling ===
echo "\n--- Error Handling ---\n";
test_exception(
    fn() => $updater->fetch('stars'),
    InvalidArgumentException::class,
    'fetch() throws for unknown type'
);

// Reset target and test endpoint resolution
$u2 = new GitHubUpdater();
test_exception(
    fn() => $u2->fetch('commits'),
    RuntimeException::class,
    'fetch() throws without repo/org'
);

$u2->setOrganization('test-org');
test_exception(
    fn() => $u2->fetch('releases'),
    RuntimeException::class,
    'fetch(releases) throws without repo'
);

test_exception(
    fn() => $u2->fetch('pulls'),
    RuntimeException::class,
    'fetch(pulls) throws without repo'
);

// === Test 12-13: fetchAll ===
echo "\n--- Fetch All ---\n";
$updater->setFetchTypes(['commits', 'releases']);
$results_all = $updater->fetchAll();
test_assert(is_array($results_all), 'fetchAll returns array');
test_assert(isset($results_all['commits']), 'fetchAll has commits key');
test_assert(isset($results_all['releases']), 'fetchAll has releases key');

$updater->setFetchTypes(['commits', 'releases', 'pulls']);
$results_partial = $updater->fetchAll();
test_assert(is_array($results_partial), 'fetchAll partial failure returns array');
foreach ($results_partial as $type => $result) {
    test_assert(isset($result['success']), "Result for $type has success key");
    test_assert(isset($result['data']), "Result for $type has data key");
}

// === Test 14-15: Rate Limit ===
echo "\n--- Rate Limit ---\n";
test_assert(is_array($updater->getRateLimit()), 'getRateLimit returns array');

$status = $updater->fetchRateLimitStatus();
test_assert(is_array($status), 'fetchRateLimitStatus returns array');

// === Test 16-17: Cache ===
echo "\n--- Cache ---\n";
$cleared = $updater->clearCache('commits');
test_assert(is_int($cleared), 'clearCache with type returns int');

$clearedAll = $updater->clearCache();
test_assert(is_int($clearedAll) && $clearedAll >= 0, 'clearCache all returns int >= 0');

// === Test 18: Logs ===
echo "\n--- Logs ---\n";
test_assert(is_array($updater->readRecentLogs(10)), 'readRecentLogs returns array');

// === Test 19: JSON Export ===
echo "\n--- JSON Export ---\n";
$testResults = [
    'commits' => ['success' => true, 'data' => [], 'source' => 'test'],
    'releases' => ['success' => true, 'data' => [], 'source' => 'test'],
];
$jsonPath = $updater->saveToJson($testResults);
test_assert(file_exists($jsonPath), 'saveToJson creates file');
$content = file_get_contents($jsonPath);
$decoded = json_decode($content, true);
test_assert(isset($decoded['results']), 'JSON has results key');
test_assert(isset($decoded['fetched_at']), 'JSON has fetched_at key');
unlink($jsonPath);

// === Test 20-23: Data Processing ===
echo "\n--- Data Processing ---\n";

$ref = new ReflectionClass(GitHubUpdater::class);

$processCommits = $ref->getMethod('processCommits');
$processCommits->setAccessible(true);

$rawCommits = [[
    'sha' => 'abc123',
    'commit' => [
        'message' => "Fix bug\n\nDetails",
        'author' => ['name' => 'User', 'email' => 'u@t.com', 'date' => '2026-01-15T10:00:00Z'],
    ],
    'author' => ['login' => 'user1'],
    'html_url' => 'https://github.com/o/r/commit/abc123',
]];

$processed = $processCommits->invoke($updater, $rawCommits);
test_assert(count($processed) === 1, 'processCommits: count');
test_assert($processed[0]['sha'] === 'abc123', 'processCommits: sha');
test_assert($processed[0]['message'] === 'Fix bug', 'processCommits: message (first line)');
test_assert($processed[0]['author_username'] === 'user1', 'processCommits: username');

$processReleases = $ref->getMethod('processReleases');
$processReleases->setAccessible(true);

$rawReleases = [[
    'id' => 1, 'tag_name' => 'v1.0', 'name' => 'First',
    'body' => 'Notes', 'prerelease' => false, 'draft' => false,
    'published_at' => '2026-01-01T00:00:00Z',
    'html_url' => 'https://github.com/o/r/releases/v1.0',
    'author' => ['login' => 'dev'],
]];

$rp = $processReleases->invoke($updater, $rawReleases);
test_assert(count($rp) === 1, 'processReleases: count');
test_assert($rp[0]['tag_name'] === 'v1.0', 'processReleases: tag');
test_assert($rp[0]['prerelease'] === false, 'processReleases: prerelease');

$processIssues = $ref->getMethod('processIssues');
$processIssues->setAccessible(true);

$rawIssues = [[
    'id' => 42, 'number' => 1, 'title' => 'Bug', 'state' => 'open',
    'labels' => [['name' => 'bug'], ['name' => 'critical']],
    'created_at' => '2026-01-10T08:00:00Z',
    'updated_at' => '2026-01-11T09:00:00Z',
    'html_url' => 'https://github.com/o/r/issues/1',
    'user' => ['login' => 'reporter'],
    'comments' => 3,
]];

$ip = $processIssues->invoke($updater, $rawIssues);
test_assert(count($ip) === 1, 'processIssues: count');
test_assert($ip[0]['title'] === 'Bug', 'processIssues: title');
test_assert(in_array('bug', $ip[0]['labels'], true), 'processIssues: labels');
test_assert($ip[0]['comments'] === 3, 'processIssues: comments');

$processPulls = $ref->getMethod('processPulls');
$processPulls->setAccessible(true);

$rawPulls = [[
    'id' => 99, 'number' => 5, 'title' => 'Feature', 'state' => 'open',
    'draft' => false, 'created_at' => '2026-02-01T10:00:00Z',
    'updated_at' => '2026-02-02T10:00:00Z', 'merged_at' => null,
    'html_url' => 'https://github.com/o/r/pull/5',
    'user' => ['login' => 'contributor'],
    'head' => ['ref' => 'feature'], 'base' => ['ref' => 'main'],
]];

$pp = $processPulls->invoke($updater, $rawPulls);
test_assert(count($pp) === 1, 'processPulls: count');
test_assert($pp[0]['state'] === 'open', 'processPulls: state');
test_assert($pp[0]['head_branch'] === 'feature', 'processPulls: head branch');
test_assert($pp[0]['base_branch'] === 'main', 'processPulls: base branch');

// === Test 24: Empty data processing ===
echo "\n--- Empty Data Processing ---\n";
foreach (['processCommits', 'processReleases', 'processIssues', 'processPulls'] as $methodName) {
    $m = $ref->getMethod($methodName);
    $m->setAccessible(true);
    $result = $m->invoke($updater, []);
    test_assert(empty($result), "$methodName returns empty for empty input");
}

// === Test 25-26: Endpoint Resolution ===
echo "\n--- Endpoint Resolution ---\n";
$resolver = $ref->getMethod('resolveEndpoint');
$resolver->setAccessible(true);

$updater->setRepository('owner', 'repo');
$ep_commits = $resolver->invoke($updater, 'commits');
test_assert(str_contains($ep_commits, 'repos/owner/repo/commits'), 'Endpoint: repo commits');

$ep_releases = $resolver->invoke($updater, 'releases');
test_assert(str_contains($ep_releases, 'repos/owner/repo/releases'), 'Endpoint: repo releases');

$ep_issues = $resolver->invoke($updater, 'issues');
test_assert(str_contains($ep_issues, 'repos/owner/repo/issues'), 'Endpoint: repo issues');

$ep_pulls = $resolver->invoke($updater, 'pulls');
test_assert(str_contains($ep_pulls, 'repos/owner/repo/pulls'), 'Endpoint: repo pulls');

$uOrg = new GitHubUpdater();
$uOrg->setOrganization('my-org');
$ep_org = $resolver->invoke($uOrg, 'commits');
test_assert(str_contains($ep_org, 'orgs/my-org/events'), 'Endpoint: org events');

$ep_org_issues = $resolver->invoke($uOrg, 'issues');
test_assert(str_contains($ep_org_issues, 'orgs/my-org/issues'), 'Endpoint: org issues');

$ep_empty = $resolver->invoke($uOrg, 'releases');
test_assert(empty($ep_empty), 'Endpoint: releases requires repo');

// === Test 27: Token not in logs ===
echo "\n--- Security ---\n";
$updater->setToken('ghp_super_secret_token_12345');
$updater->fetchAll();
foreach ($updater->getLogs() as $log) {
    test_assert(
        !str_contains($log, 'ghp_super_secret_token_12345'),
        'Token not exposed in logs'
    );
}

// === Summary ===
echo "\n====== SUMMARY ======\n";
echo "Passed: {$results['passed']}\n";
echo "Failed: {$results['failed']}\n";

exit($results['failed'] > 0 ? 1 : 0);
