<?php
/**
 * Script Pengambil Data Pembaruan GitHub
 * Dapat dijalankan manual atau via Cron Job / Task Scheduler
 *
 * Penggunaan:
 *   php scripts/fetch_github_updates.php
 *   php scripts/fetch_github_updates.php --type=commits
 *   php scripts/fetch_github_updates.php --owner=bpsjember --repo=pelita
 *   php scripts/fetch_github_updates.php --test
 *   php scripts/fetch_github_updates.php --cache-clear
 *
 * @package PELITA
 * @version 1.0.0
 */

require_once __DIR__ . '/../config/app.php';
require_once CLASSES_PATH . '/Database.php';
require_once CLASSES_PATH . '/EnvLoader.php';
require_once CLASSES_PATH . '/GitHubUpdater.php';

EnvLoader::load(dirname(__DIR__) . '/.env');

$opts = getopt('', [
    'owner:',
    'repo:',
    'org:',
    'token:',
    'type:',
    'test',
    'cache-clear',
    'help',
]);

if (isset($opts['help'])) {
    echo "GitHub Updater - PELITA\n";
    echo "Pengambilan data pembaruan dari GitHub API\n\n";
    echo "Options:\n";
    echo "  --owner=<owner>    Nama pemilik repository (user/organization)\n";
    echo "  --repo=<repo>      Nama repository\n";
    echo "  --org=<org>        Nama organisasi GitHub (alternatif dari owner/repo)\n";
    echo "  --token=<token>    Personal Access Token GitHub\n";
    echo "  --type=<types>     Tipe data: commits,releases,issues,pulls (default: dari config)\n";
    echo "  --test             Uji koneksi saja (health check)\n";
    echo "  --cache-clear      Hapus semua cache\n";
    echo "  --help             Tampilkan bantuan ini\n";
    exit(0);
}

$updater = new GitHubUpdater();

if (!empty($opts['owner'])) {
    $owner = $opts['owner'];
    $repo = $opts['repo'] ?? $updater->repo;
    $updater->setRepository($owner, $repo);
}

if (!empty($opts['org'])) {
    $updater->setOrganization($opts['org']);
}

if (!empty($opts['token'])) {
    $updater->setToken($opts['token']);
}

if (!empty($opts['type'])) {
    $updater->setFetchTypes(explode(',', $opts['type']));
}

if (isset($opts['cache-clear'])) {
    $type = $opts['type'] ?? null;
    $updater->clearCache($type);
    exit(0);
}

echo "====== PELITA - GITHUB UPDATER ======\n";
echo "Time: " . date('Y-m-d H:i:s') . "\n";

if (!$updater->isAuthenticated()) {
    echo "Warning: No GitHub token set. Rate limit: 60 req/hour (unauthenticated).\n";
}

if (!$updater->isAuthenticated() && empty($opts['test'])) {
    echo "Recommendation: Set GITHUB_TOKEN in .env or use --token for authenticated access.\n";
}

if (isset($opts['test'])) {
    echo "\n--- Rate Limit Check ---\n";
    $rateLimit = $updater->fetchRateLimitStatus();
    echo "       Limit: " . ($rateLimit['limit'] ?? 'N/A') . "\n";
    echo "    Remaining: " . ($rateLimit['remaining'] ?? 'N/A') . "\n";
    echo "  Used: " . ($rateLimit['used'] ?? 'N/A') . "\n";
    echo "  Authenticated: " . ($updater->isAuthenticated() ? 'Yes' : 'No') . "\n";
    exit(0);
}

try {
    $results = $updater->fetchAll();

    echo "\n--- Results ---\n";

    foreach ($results as $type => $result) {
        $status = $result['success'] ? 'OK' : 'FAILED';
        $count = count($result['data'] ?? []);
        $source = $result['source'] ?? 'unknown';
        echo str_pad($type, 10) . " | $status | $count items | source: $source\n";
    }

    $jsonFile = $updater->saveToJson($results);
    echo "\nJSON saved: $jsonFile\n";

    echo "\n--- Recent Logs ---\n";
    foreach ($updater->readRecentLogs(5) as $log) {
        echo "  $log\n";
    }
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}
