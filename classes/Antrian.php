<?php
/**
 * Antrian Model Class
 * Manages queue ticket system for services
 * @package PELITA
 * @version 1.0.0
 */

class Antrian {
    private Database $db;
    private string $table = 'antrian';
    private string $layananTable = 'ref_layanan';

    private const STATUS_MENUNGGU = 'menunggu';
    private const STATUS_DIPANGGIL = 'dipanggil';
    private const STATUS_SELESAI = 'selesai';
    private const STATUS_BATAL = 'batal';
    private const ESTIMASI_MENIT_PER_ORANG = 15;

    private const VALID_STATUSES = [
        self::STATUS_MENUNGGU,
        self::STATUS_DIPANGGIL,
        self::STATUS_SELESAI,
        self::STATUS_BATAL,
    ];

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function getLayananList(): array {
        return $this->db->fetchAll(
            "SELECT * FROM {$this->layananTable} WHERE is_active = 1 ORDER BY kode ASC"
        );
    }

    public function getLayananByKode(string $kode): ?array {
        return $this->db->fetch(
            "SELECT * FROM {$this->layananTable} WHERE kode = :kode AND is_active = 1",
            ['kode' => $kode]
        );
    }

    public function ambilAntrian(string $kodeLayanan, ?string $nama = null, ?string $nohp = null): ?array {
        $layanan = $this->getLayananByKode($kodeLayanan);
        if (!$layanan) {
            return null;
        }

        $today = date('Y-m-d');
        $countToday = $this->countByLayananAndDate($kodeLayanan, $today);

        if ($countToday >= $layanan['max_harian']) {
            return null;
        }

        $nomorUrut = $countToday + 1;
        $nomorAntrian = $this->generateNomorAntrian($kodeLayanan, $nomorUrut, $today);

        $id = $this->db->insert($this->table, [
            'kode_layanan' => $kodeLayanan,
            'nomor_urut' => $nomorUrut,
            'tanggal' => $today,
            'nomor_antrian' => $nomorAntrian,
            'status' => self::STATUS_MENUNGGU,
            'nama_pemohon' => $nama,
            'nohp_pemohon' => $nohp,
        ]);

        return $this->getById($id);
    }

    public function generateNomorAntrian(string $kodeLayanan, int $nomorUrut, string $date): string {
        $parts = explode('-', $date);
        $yymm = substr($parts[0], -2) . $parts[1] . $parts[2];
        return sprintf('%s-%03d-%s', $kodeLayanan, $nomorUrut, $yymm);
    }

    public function countByLayananAndDate(string $kodeLayanan, string $date): int {
        return $this->db->count(
            $this->table,
            "kode_layanan = :kode AND tanggal = :tanggal AND status != :batal",
            ['kode' => $kodeLayanan, 'tanggal' => $date, 'batal' => self::STATUS_BATAL]
        );
    }

    public function getWaitingCount(string $kodeLayanan, ?string $date = null): int {
        $date = $date ?: date('Y-m-d');
        return $this->db->count(
            $this->table,
            "kode_layanan = :kode AND tanggal = :tanggal AND status = :status",
            ['kode' => $kodeLayanan, 'tanggal' => $date, 'status' => self::STATUS_MENUNGGU]
        );
    }

    public function getEstimasiTunggu(string $kodeLayanan, ?string $date = null): int {
        $waiting = $this->getWaitingCount($kodeLayanan, $date);
        return $waiting * self::ESTIMASI_MENIT_PER_ORANG;
    }

    public function getById(int $id): ?array {
        return $this->db->fetch(
            "SELECT a.*, l.nama AS nama_layanan
             FROM {$this->table} a
             LEFT JOIN {$this->layananTable} l ON a.kode_layanan = l.kode
             WHERE a.id = :id",
            ['id' => $id]
        );
    }

    public function getByNomorAntrian(string $nomorAntrian): ?array {
        return $this->db->fetch(
            "SELECT a.*, l.nama AS nama_layanan
             FROM {$this->table} a
             LEFT JOIN {$this->layananTable} l ON a.kode_layanan = l.kode
             WHERE a.nomor_antrian = :nomor",
            ['nomor' => $nomorAntrian]
        );
    }

    public function panggilBerikutnya(string $kodeLayanan): ?array {
        $today = date('Y-m-d');
        $row = $this->db->fetch(
            "SELECT * FROM {$this->table}
             WHERE kode_layanan = :kode AND tanggal = :tanggal AND status = :status
             ORDER BY nomor_urut ASC LIMIT 1",
            ['kode' => $kodeLayanan, 'tanggal' => $today, 'status' => self::STATUS_MENUNGGU]
        );

        if (!$row) {
            return null;
        }

        $this->updateStatus($row['id'], self::STATUS_DIPANGGIL);
        return $this->getById($row['id']);
    }

    public function selesai(int $id): bool {
        return $this->updateStatus($id, self::STATUS_SELESAI);
    }

    public function batal(int $id): bool {
        return $this->updateStatus($id, self::STATUS_BATAL);
    }

    private function updateStatus(int $id, string $status): bool {
        if (!in_array($status, self::VALID_STATUSES, true)) {
            return false;
        }

        $updates = ['status' => $status];

        if ($status === self::STATUS_DIPANGGIL) {
            $updates['waktu_panggil'] = date('Y-m-d H:i:s');
        } elseif ($status === self::STATUS_SELESAI) {
            $updates['waktu_selesai'] = date('Y-m-d H:i:s');
        }

        return $this->db->update($this->table, $updates, "id = :id", ['id' => $id]) > 0;
    }

    public function panggil(int $id): bool {
        return $this->updateStatus($id, self::STATUS_DIPANGGIL);
    }

    public function getFiltered(
        ?string $kodeLayanan = null,
        ?string $status = null,
        ?string $date = null,
        int $page = 1,
        int $limit = ITEMS_PER_PAGE
    ): array {
        $offset = ($page - 1) * $limit;
        $params = [];
        $conditions = [];

        if ($kodeLayanan) {
            $conditions[] = "a.kode_layanan = :kode";
            $params['kode'] = $kodeLayanan;
        }

        if ($status) {
            $conditions[] = "a.status = :status";
            $params['status'] = $status;
        }

        if ($date) {
            $conditions[] = "a.tanggal = :tanggal";
            $params['tanggal'] = $date;
        }

        $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT a.*, l.nama AS nama_layanan
                FROM {$this->table} a
                LEFT JOIN {$this->layananTable} l ON a.kode_layanan = l.kode
                {$where}
                ORDER BY a.id DESC
                LIMIT {$limit} OFFSET {$offset}";

        return $this->db->fetchAll($sql, $params);
    }

    public function getTotalFiltered(
        ?string $kodeLayanan = null,
        ?string $status = null,
        ?string $date = null
    ): int {
        $params = [];
        $conditions = [];

        if ($kodeLayanan) {
            $conditions[] = "a.kode_layanan = :kode";
            $params['kode'] = $kodeLayanan;
        }

        if ($status) {
            $conditions[] = "a.status = :status";
            $params['status'] = $status;
        }

        if ($date) {
            $conditions[] = "a.tanggal = :tanggal";
            $params['tanggal'] = $date;
        }

        $where = $conditions ? "WHERE " . implode(" AND ", $conditions) : "";

        $sql = "SELECT COUNT(*)
                FROM {$this->table} a
                LEFT JOIN {$this->layananTable} l ON a.kode_layanan = l.kode
                {$where}";

        return (int) $this->db->fetch($sql, $params)['COUNT(*)'];
    }

    public function getStats(?string $date = null): array {
        $date = $date ?: date('Y-m-d');

        if ($this->db->isSqlite()) {
            $todayCondition = "a.tanggal = :date";
        } else {
            $todayCondition = "a.tanggal = :date";
        }

        $params = ['date' => $date];

        $total = (int) $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} a WHERE {$todayCondition}",
            $params
        )['cnt'];

        $menunggu = (int) $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} a WHERE {$todayCondition} AND a.status = :s",
            array_merge($params, ['s' => self::STATUS_MENUNGGU])
        )['cnt'];

        $dipanggil = (int) $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} a WHERE {$todayCondition} AND a.status = :s",
            array_merge($params, ['s' => self::STATUS_DIPANGGIL])
        )['cnt'];

        $selesai = (int) $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} a WHERE {$todayCondition} AND a.status = :s",
            array_merge($params, ['s' => self::STATUS_SELESAI])
        )['cnt'];

        $batal = (int) $this->db->fetch(
            "SELECT COUNT(*) as cnt FROM {$this->table} a WHERE {$todayCondition} AND a.status = :s",
            array_merge($params, ['s' => self::STATUS_BATAL])
        )['cnt'];

        return [
            'total' => $total,
            'menunggu' => $menunggu,
            'dipanggil' => $dipanggil,
            'selesai' => $selesai,
            'batal' => $batal,
        ];
    }

    public function getLayananStats(?string $date = null): array {
        $date = $date ?: date('Y-m-d');

        $sql = "SELECT l.kode, l.nama, l.max_harian,
                       COUNT(a.id) as total_ambil,
                       SUM(CASE WHEN a.status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
                       SUM(CASE WHEN a.status = 'dipanggil' THEN 1 ELSE 0 END) as dipanggil,
                       SUM(CASE WHEN a.status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                       SUM(CASE WHEN a.status = 'batal' THEN 1 ELSE 0 END) as batal
                FROM {$this->layananTable} l
                LEFT JOIN {$this->table} a ON l.kode = a.kode_layanan AND a.tanggal = :date
                WHERE l.is_active = 1
                GROUP BY l.kode
                ORDER BY l.kode ASC";

        return $this->db->fetchAll($sql, ['date' => $date]);
    }

    public function getNomorSaatIni(string $kodeLayanan, ?string $date = null): ?array {
        $date = $date ?: date('Y-m-d');
        return $this->db->fetch(
            "SELECT nomor_antrian, nomor_urut
             FROM {$this->table}
             WHERE kode_layanan = :kode AND tanggal = :date AND status = :status
             ORDER BY nomor_urut DESC LIMIT 1",
            ['kode' => $kodeLayanan, 'date' => $date, 'status' => self::STATUS_DIPANGGIL]
        );
    }

    public function getAntrianByDate(string $date): array {
        return $this->db->fetchAll(
            "SELECT a.*, l.nama AS nama_layanan
             FROM {$this->table} a
             LEFT JOIN {$this->layananTable} l ON a.kode_layanan = l.kode
             WHERE a.tanggal = :date
             ORDER BY a.kode_layanan ASC, a.nomor_urut ASC",
            ['date' => $date]
        );
    }

    public function delete(int $id): bool {
        return $this->db->delete($this->table, "id = :id", ['id' => $id]) > 0;
    }
}
