<?php
/**
 * Database Class - Singleton Pattern with MySQL and SQLite support
 * @package PELITA
 * @version 1.1.0
 */

require_once __DIR__ . '/../config/database.php';

class Database {
    private static ?Database $instance = null;
    private PDO $pdo;
    private int $queryCount = 0;
    private string $driver;

    private function __construct() {
        $this->driver = $this->resolveDriver();

        try {
            if ($this->driver === 'sqlite') {
                $this->connectSqlite();
            } else {
                $this->connectMysql();
            }
        } catch (PDOException $e) {
            error_log("[PELITA] Database Error: " . $e->getMessage());

            $message = "Koneksi database gagal. Silakan hubungi administrator.";
            $isDebug = filter_var(getenv('APP_DEBUG') ?: 'false', FILTER_VALIDATE_BOOLEAN);
            if ($isDebug) {
                $message .= " Detail: " . $e->getMessage();
            }

            throw new Exception($message, 0, $e);
        }
    }

    private function resolveDriver(): string {
        $driver = strtolower(trim((string) DB_TYPE));

        // Accept common typo to avoid unexpected fallback to MySQL.
        if ($driver === 'sqllite') {
            $driver = 'sqlite';
        }

        return $driver === 'sqlite' ? 'sqlite' : 'mysql';
    }

    private function connectMysql(): void {
        $dsn = sprintf(
            "mysql:host=%s;port=%s;dbname=%s;charset=%s",
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );
        
        $initCommandAttr = defined('Pdo\\Mysql::ATTR_INIT_COMMAND')
            ? Pdo\Mysql::ATTR_INIT_COMMAND
            : PDO::MYSQL_ATTR_INIT_COMMAND;

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            $initCommandAttr => "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATION
        ];
        
        $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    private function connectSqlite(): void {
        if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            throw new PDOException("Driver PDO SQLite tidak tersedia. Aktifkan extension pdo_sqlite.");
        }

        $rawPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, DB_SQLITE_PATH);
        $isAbsolutePath = preg_match('/^[a-zA-Z]:\\\\|^\//', $rawPath) === 1;
        $path = $isAbsolutePath
            ? $rawPath
            : dirname(__DIR__) . DIRECTORY_SEPARATOR . ltrim($rawPath, '\\/');

        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
                throw new PDOException("Gagal membuat direktori database SQLite: {$dir}");
            }
        }
        
        $dsn = "sqlite:" . $path;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];
        
        $this->pdo = new PDO($dsn, null, null, $options);
        // Enable Foreign Keys for SQLite
        $this->pdo->exec("PRAGMA foreign_keys = ON;");
    }

    public function isSqlite(): bool {
        return $this->driver === 'sqlite';
    }

    public function isMysql(): bool {
        return $this->driver === 'mysql';
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO {
        return $this->pdo;
    }

    public function query(string $sql, array $params = []): PDOStatement {
        $this->queryCount++;
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch(string $sql, array $params = []): ?array {
        $result = $this->query($sql, $params)->fetch();
        return $result ?: null;
    }

    public function fetchAll(string $sql, array $params = []): array {
        return $this->query($sql, $params)->fetchAll();
    }

    public function insert(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return (int) $this->pdo->lastInsertId();
    }

    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = implode(', ', array_map(fn($k) => "{$k} = :{$k}", array_keys($data)));
        
        $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
        $stmt = $this->query($sql, array_merge($data, $whereParams));
        
        return $stmt->rowCount();
    }

    public function delete(string $table, string $where, array $params = []): int {
        $sql = "DELETE FROM {$table} WHERE {$where}";
        return $this->query($sql, $params)->rowCount();
    }

    public function count(string $table, string $where = '1=1', array $params = []): int {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE {$where}";
        return (int) $this->query($sql, $params)->fetchColumn();
    }

    public function beginTransaction(): bool {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool {
        return $this->pdo->commit();
    }
}
