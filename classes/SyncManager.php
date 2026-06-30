<?php
/**
 * SyncManager Class
 * Menangani sinkronisasi data dari Local ke Cloud (One-Way Sync)
 * @package PELITA
 * @version 3.0.0
 *
 * v3.0: + Conflict detection via source_hash
 *       + Duplicate prevention (INSERT IGNORE + source_id)
 *       + getPendingCount() & getLastSyncTime() for dashboard
 *       + Data type casting SQLite → MySQL
 */

class SyncManager
{
    private PDO $local;
    private ?PDO $remote = null;
    private bool $connected = false;
    private array $logs = [];
    private string $log_file;
    private string $conflict_log;

    private const ALLOWED_TABLES = ['buku_tamu', 'kepuasan'];
    private const BATCH_SIZE = 50;
    private const MAX_RETRIES = 3;

    public function __construct()
    {
        $log_dir = dirname(__DIR__) . '/logs';
        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0755, true);
        }
        $this->log_file = $log_dir . '/sync_' . date('Y-m-d') . '.log';
        $this->conflict_log = $log_dir . '/sync_conflicts.log';

        $this->local = Database::getInstance()->getConnection();
        $this->connectRemote();
    }

    private function connectRemote(): void
    {
        $host = getenv('REMOTE_DB_HOST');
        $name = getenv('REMOTE_DB_NAME');
        $user = getenv('REMOTE_DB_USER');

        if (empty($host) || empty($name)) {
            $this->log("Remote DB configuration missing. Skipping connection.");
            return;
        }

        $timeout = (int) (getenv('REMOTE_DB_TIMEOUT') ?: 15);

        for ($attempt = 1; $attempt <= self::MAX_RETRIES; $attempt++) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4",
                    $host,
                    getenv('REMOTE_DB_PORT') ?: '3306',
                    $name
                );

                $this->remote = new PDO($dsn, $user, getenv('REMOTE_DB_PASS'), [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_TIMEOUT => $timeout
                ]);

                $this->connected = true;
                $this->log("Connected to Remote DB ($host)");
                return;
            } catch (PDOException $e) {
                $this->log("Connection attempt $attempt/" . self::MAX_RETRIES . " failed: " . $e->getMessage());
                if ($attempt < self::MAX_RETRIES) {
                    sleep(2);
                }
            }
        }

        $this->log("Failed to connect to Remote DB after " . self::MAX_RETRIES . " attempts.");
        $this->connected = false;
    }

    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Test koneksi ke remote DB
     */
    public function testConnection(): array
    {
        if (!$this->connected) {
            return ['status' => false, 'message' => 'Remote DB not connected', 'logs' => $this->logs];
        }

        try {
            $this->remote->query("SELECT 1");
            $tables = [];
            foreach (self::ALLOWED_TABLES as $table) {
                try {
                    $count = (int) $this->remote->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                    $tables[$table] = ['exists' => true, 'count' => $count];
                } catch (Exception $e) {
                    $tables[$table] = ['exists' => false, 'error' => $e->getMessage()];
                }
            }

            return [
                'status' => true,
                'message' => 'Connection OK',
                'host' => getenv('REMOTE_DB_HOST'),
                'database' => getenv('REMOTE_DB_NAME'),
                'tables' => $tables,
                'logs' => $this->logs
            ];
        } catch (Exception $e) {
            return ['status' => false, 'message' => $e->getMessage(), 'logs' => $this->logs];
        }
    }

    /**
     * Hitung record pending sync per tabel
     */
    public function getPendingCount(): array
    {
        $result = [];
        foreach (self::ALLOWED_TABLES as $table) {
            try {
                $count = (int) $this->local->query(
                    "SELECT COUNT(*) FROM `$table` WHERE `synced_at` IS NULL"
                )->fetchColumn();
                $result[$table] = $count;
            } catch (Exception $e) {
                $result[$table] = -1;
            }
        }
        return $result;
    }

    /**
     * Ambil waktu sync terakhir
     */
    public function getLastSyncTime(): ?string
    {
        $latest = null;
        foreach (self::ALLOWED_TABLES as $table) {
            try {
                $time = $this->local->query(
                    "SELECT MAX(`synced_at`) FROM `$table` WHERE `synced_at` IS NOT NULL"
                )->fetchColumn();
                if ($time && ($latest === null || $time > $latest)) {
                    $latest = $time;
                }
            } catch (Exception $e) {
                // skip
            }
        }
        return $latest ?: null;
    }

    /**
     * Baca log file terakhir (max N baris)
     */
    public function readRecentLogs(int $maxLines = 50): array
    {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        return array_slice($lines, -$maxLines);
    }

    /**
     * Jalankan sinkronisasi semua tabel
     */
    public function sync(): array
    {
        if (!$this->connected) {
            return ['status' => false, 'message' => 'Remote DB not connected', 'logs' => $this->logs];
        }

        $stats = [
            'buku_tamu' => 0,
            'kepuasan' => 0,
            'skipped' => 0,
            'conflicts' => 0,
            'errors' => []
        ];

        $stats['buku_tamu'] = $this->syncTable('buku_tamu', [
            'tahun',
            'bulan',
            'hari',
            'waktu',
            'nama',
            'email',
            'alamat',
            'nohp',
            'umur',
            'asal',
            'jenis_kelamin',
            'pendidikan',
            'pekerjaan',
            'keperluan',
            'keperluan_lain',
            'nomor_antrian',
            'created_at'
        ], $stats['errors'], $stats['skipped'], $stats['conflicts']);

        $stats['kepuasan'] = $this->syncTable('kepuasan', [
            'tahun',
            'bulan',
            'hari',
            'waktu',
            'email',
            'rating',
            'komentar',
            'created_at'
        ], $stats['errors'], $stats['skipped'], $stats['conflicts']);

        $this->log(sprintf(
            "Sync completed. Buku Tamu: %d, Kepuasan: %d, Skipped: %d, Conflicts: %d, Errors: %d",
            $stats['buku_tamu'],
            $stats['kepuasan'],
            $stats['skipped'],
            $stats['conflicts'],
            count($stats['errors'])
        ));

        return ['status' => true, 'stats' => $stats, 'logs' => $this->logs];
    }

    private function syncTable(string $table, array $columns, array &$errors, int &$skipped, int &$conflicts): int
    {
        if (!in_array($table, self::ALLOWED_TABLES, true)) {
            $this->log("SECURITY: Rejected sync for unknown table '$table'");
            return 0;
        }

        $totalSynced = 0;

        do {
            $batchSynced = $this->syncBatch($table, $columns, $errors, $skipped, $conflicts);
            $totalSynced += $batchSynced;
        } while ($batchSynced >= self::BATCH_SIZE);

        if ($totalSynced > 0) {
            $this->log("Total synced for $table: $totalSynced records");
        }

        return $totalSynced;
    }

    /**
     * Generate hash unik dari data row untuk deteksi duplikat
     */
    private function generateHash(array $data): string
    {
        // Sort keys untuk konsistensi, lalu hash
        ksort($data);
        return hash('sha256', json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * Cast tipe data SQLite → MySQL
     */
    private function castData(string $table, array $data): array
    {
        // tahun harus integer untuk MySQL YEAR type
        if (isset($data['tahun'])) {
            $data['tahun'] = (int) $data['tahun'];
        }
        // umur harus integer
        if (isset($data['umur'])) {
            $data['umur'] = (int) $data['umur'];
        }
        // Validasi enum jenis_kelamin
        if (isset($data['jenis_kelamin']) && !in_array($data['jenis_kelamin'], ['Laki-laki', 'Perempuan'])) {
            $data['jenis_kelamin'] = 'Laki-laki';
        }
        // Validasi enum rating
        if (isset($data['rating']) && !in_array($data['rating'], ['Sangat Puas', 'Puas', 'Kurang Puas'])) {
            $data['rating'] = 'Puas';
        }
        return $data;
    }

    private function syncBatch(string $table, array $columns, array &$errors, int &$skipped, int &$conflicts): int
    {
        $syncedCount = 0;

        $colList = implode(', ', array_map(fn($c) => "`$c`", $columns));
        $sql = "SELECT `id`, $colList FROM `$table` WHERE `synced_at` IS NULL ORDER BY `id` ASC LIMIT " . self::BATCH_SIZE;
        $stmt = $this->local->query($sql);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return 0;
        }

        $this->log("Found " . count($rows) . " unsynced records in $table");

        // Prepare INSERT with source tracking (ON DUPLICATE KEY = skip)
        $allCols = array_merge($columns, ['source_id', 'source_hash']);
        $colNames = implode(', ', array_map(fn($c) => "`$c`", $allCols));
        $placeholders = implode(', ', array_map(fn($c) => ":$c", $allCols));
        $insertSql = "INSERT INTO `$table` ($colNames) VALUES ($placeholders)
                       ON DUPLICATE KEY UPDATE `source_id` = `source_id`";

        $updateSql = "UPDATE `$table` SET `synced_at` = :now WHERE `id` = :id";

        // Check existing hashes for conflict detection
        $checkSql = "SELECT `id`, `source_hash` FROM `$table` WHERE `source_id` = :sid LIMIT 1";

        foreach ($rows as $row) {
            try {
                $insertData = [];
                foreach ($columns as $col) {
                    $insertData[$col] = $row[$col];
                }

                // Cast tipe data
                $insertData = $this->castData($table, $insertData);

                // Generate hash
                $hash = $this->generateHash($insertData);
                $insertData['source_id'] = $row['id'];
                $insertData['source_hash'] = $hash;

                // Check apakah source_id sudah ada di remote (conflict detection)
                $checkStmt = $this->remote->prepare($checkSql);
                $checkStmt->execute(['sid' => $row['id']]);
                $existing = $checkStmt->fetch();

                if ($existing) {
                    if ($existing['source_hash'] === $hash) {
                        // Data sudah sama persis → skip, mark as synced
                        $this->markSynced($row['id'], $table);
                        $skipped++;
                        continue;
                    } else {
                        // Data berbeda → CONFLICT
                        $conflicts++;
                        $this->logConflict($table, $row['id'], $existing['source_hash'], $hash);
                        // Update remote dengan data terbaru
                        $updateRemoteSql = "UPDATE `$table` SET " .
                            implode(', ', array_map(fn($c) => "`$c` = :$c", $columns)) .
                            ", `source_hash` = :source_hash WHERE `source_id` = :source_id";
                        $updateData = $insertData;
                        $updateStmt = $this->remote->prepare($updateRemoteSql);
                        $updateStmt->execute($updateData);
                        $this->markSynced($row['id'], $table);
                        $syncedCount++;
                        continue;
                    }
                }

                // Insert baru ke remote
                $this->remote->beginTransaction();
                $remoteStmt = $this->remote->prepare($insertSql);
                $remoteStmt->execute($insertData);
                $this->remote->commit();

                $this->markSynced($row['id'], $table);
                $syncedCount++;

            } catch (Exception $e) {
                if ($this->remote->inTransaction()) {
                    $this->remote->rollBack();
                }
                $errorMsg = "Error syncing $table ID={$row['id']}: " . $e->getMessage();
                $this->log($errorMsg);
                $errors[] = $errorMsg;
                continue;
            }
        }

        return $syncedCount;
    }

    private function markSynced(int $id, string $table): void
    {
        $stmt = $this->local->prepare("UPDATE `$table` SET `synced_at` = :now WHERE `id` = :id");
        $stmt->execute(['now' => date('Y-m-d H:i:s'), 'id' => $id]);
    }

    private function logConflict(string $table, int $localId, string $oldHash, string $newHash): void
    {
        $msg = sprintf(
            "[%s] CONFLICT in %s ID=%d: remote_hash=%s, local_hash=%s (remote updated with local data)",
            date('Y-m-d H:i:s'),
            $table,
            $localId,
            substr($oldHash, 0, 12),
            substr($newHash, 0, 12)
        );
        $this->log("CONFLICT: $table ID=$localId → remote updated");
        file_put_contents($this->conflict_log, $msg . "\n", FILE_APPEND | LOCK_EX);
    }

    private function log(string $message): void
    {
        $msg = "[" . date('Y-m-d H:i:s') . "] $message";
        $this->logs[] = $msg;
        file_put_contents($this->log_file, $msg . "\n", FILE_APPEND | LOCK_EX);
        if (php_sapi_name() === 'cli') {
            echo $msg . "\n";
        }
    }
}
