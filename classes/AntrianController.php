<?php
/**
 * Antrian Controller
 * Handles business logic for queue ticket system
 * @package PELITA
 */

require_once __DIR__ . '/Antrian.php';
require_once __DIR__ . '/../includes/functions.php';

class AntrianController {
    private Antrian $model;
    private array $errors = [];

    public function __construct() {
        $this->model = new Antrian();
    }

    public function ambil(array $input): array {
        $this->errors = [];
        $data = sanitize_array($input);
        $this->validateAmbil($data);

        if (!empty($this->errors)) {
            return ['success' => false, 'errors' => $this->errors];
        }

        try {
            $result = $this->model->ambilAntrian(
                $data['kode_layanan'],
                $data['nama_pemohon'] ?? null,
                $data['nohp_pemohon'] ?? null
            );

            if (!$result) {
                return [
                    'success' => false,
                    'errors' => ['layanan' => 'Layanan tidak tersedia atau kuota habis untuk hari ini.']
                ];
            }

            $layanan = $this->model->getLayananByKode($data['kode_layanan']);
            $waitingCount = $this->model->getWaitingCount($data['kode_layanan']);
            $estimasi = $this->model->getEstimasiTunggu($data['kode_layanan']);

            return [
                'success' => true,
                'message' => 'Nomor antrian berhasil diambil.',
                'data' => $result,
                'layanan' => $layanan,
                'waiting_count' => $waitingCount,
                'estimasi_menit' => $estimasi,
            ];
        } catch (Exception $e) {
            error_log("[PELITA] Antrian Error: " . $e->getMessage());
            return [
                'success' => false,
                'errors' => ['system' => 'Terjadi kesalahan sistem. Silakan coba lagi.']
            ];
        }
    }

    private function validateAmbil(array $data): void {
        if (empty($data['kode_layanan'])) {
            $this->errors['kode_layanan'] = 'Pilihan layanan wajib diisi.';
        } else {
            $layanan = $this->model->getLayananByKode($data['kode_layanan']);
            if (!$layanan) {
                $this->errors['kode_layanan'] = 'Layanan tidak valid.';
            }
        }

        if (!empty($data['nohp_pemohon']) && !validate_phone($data['nohp_pemohon'])) {
            $this->errors['nohp_pemohon'] = 'Format nomor HP tidak valid.';
        }
    }

    public function panggilBerikutnya(string $kodeLayanan): array {
        try {
            $result = $this->model->panggilBerikutnya($kodeLayanan);

            if (!$result) {
                return [
                    'success' => false,
                    'message' => 'Tidak ada antrian yang menunggu untuk layanan ini.'
                ];
            }

            return [
                'success' => true,
                'message' => "Nomor {$result['nomor_antrian']} dipanggil.",
                'data' => $result,
            ];
        } catch (Exception $e) {
            error_log("[PELITA] Antrian Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem.'
            ];
        }
    }

    public function selesai(int $id): array {
        try {
            $existing = $this->model->getById($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Data antrian tidak ditemukan.'];
            }

            $ok = $this->model->selesai($id);
            if ($ok) {
                return ['success' => true, 'message' => "Antrian {$existing['nomor_antrian']} ditandai selesai."];
            }

            return ['success' => false, 'message' => 'Gagal memperbarui status.'];
        } catch (Exception $e) {
            error_log("[PELITA] Antrian Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem.'];
        }
    }

    public function batal(int $id): array {
        try {
            $existing = $this->model->getById($id);
            if (!$existing) {
                return ['success' => false, 'message' => 'Data antrian tidak ditemukan.'];
            }

            $ok = $this->model->batal($id);
            if ($ok) {
                return ['success' => true, 'message' => "Antrian {$existing['nomor_antrian']} dibatalkan."];
            }

            return ['success' => false, 'message' => 'Gagal memperbarui status.'];
        } catch (Exception $e) {
            error_log("[PELITA] Antrian Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem.'];
        }
    }

    public function getLayananList(): array {
        return $this->model->getLayananList();
    }

    public function getById(int $id): ?array {
        return $this->model->getById($id);
    }

    public function getStats(?string $date = null): array {
        return $this->model->getStats($date);
    }

    public function getLayananStats(?string $date = null): array {
        return $this->model->getLayananStats($date);
    }

    public function getFiltered(
        ?string $kodeLayanan = null,
        ?string $status = null,
        ?string $date = null,
        int $page = 1,
        int $limit = ITEMS_PER_PAGE
    ): array {
        return $this->model->getFiltered($kodeLayanan, $status, $date, $page, $limit);
    }

    public function getTotalFiltered(
        ?string $kodeLayanan = null,
        ?string $status = null,
        ?string $date = null
    ): int {
        return $this->model->getTotalFiltered($kodeLayanan, $status, $date);
    }

    public function getNomorSaatIni(string $kodeLayanan, ?string $date = null): ?array {
        return $this->model->getNomorSaatIni($kodeLayanan, $date);
    }
}
