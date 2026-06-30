<?php
/**
 * BukuTamu Controller
 * Handles business logic and validation for Guest Book
 * @package PELITA
 */

require_once __DIR__ . '/BukuTamu.php';
require_once __DIR__ . '/../includes/functions.php';

class BukuTamuController {
    private BukuTamu $model;
    private array $errors = [];

    public function __construct() {
        $this->model = new BukuTamu();
    }

    /**
     * Handle form submission
     */
    public function store(array $input): array {
        $this->errors = [];
        
        // 1. Sanitize Input
        $data = sanitize_array($input);
        
        // 2. Validate Input
        $this->validate($data);

        // 3. If valid, save to database
        if (empty($this->errors)) {
            try {
                // Prepare data for model
                $insertData = $this->prepareData($data);
                
                $id = $this->model->create($insertData);
                $nomorAntrian = $this->model->generateNomorAntrian();

                return [
                    'success' => true,
                    'message' => 'Data berhasil disimpan',
                    'nomor_antrian' => $nomorAntrian,
                    'data_id' => $id
                ];

            } catch (Exception $e) {
                error_log("[PELITA] Controller Error: " . $e->getMessage());
                return [
                    'success' => false,
                    'errors' => ['system' => 'Terjadi kesalahan sistem. Silakan coba lagi.']
                ];
            }
        }

        return [
            'success' => false,
            'errors' => $this->errors
        ];
    }

    /**
     * Validate input data
     */
    private function validate(array $data): void {
        // Nama
        if (empty($data['nama'])) {
            $this->errors['nama'] = 'Nama wajib diisi';
        } elseif (strlen($data['nama']) < 3) {
            $this->errors['nama'] = 'Nama minimal 3 karakter';
        }

        // No HP
        if (empty($data['nohp'])) {
            $this->errors['nohp'] = 'No. HP / WhatsApp wajib diisi';
        } elseif (!validate_phone($data['nohp'])) {
            $this->errors['nohp'] = 'Format No. HP tidak valid (08xxx / 628xxx)';
        }

        // Instansi
        if (empty($data['instansi'])) {
            $this->errors['instansi'] = 'Instansi/Asal wajib diisi';
        }

        // Keperluan
        if (empty($data['keperluan'])) {
            $this->errors['keperluan'] = 'Tujuan kunjungan wajib dipilih';
        }

        // Email (Optional but must be valid if filled)
        if (!empty($data['email']) && !validate_email($data['email'])) {
            $this->errors['email'] = 'Format email tidak valid';
        }
    }

    /**
     * Prepare data for storage
     */
    private function prepareData(array $data): array {
        // Format details into keperluan_lain
        $details = [];
        if (!empty($data['orang_ditemui'])) $details[] = "Bertemu: " . $data['orang_ditemui'];
        
        if (!empty($data['jam_datang'])) {
            $jam = $data['jam_datang'];
            if (!empty($data['jam_selesai'])) $jam .= ' - ' . $data['jam_selesai'];
            $details[] = "Waktu: " . $jam;
        }
        
        if (!empty($data['rincian'])) $details[] = "Detail: " . $data['rincian'];
        
        $keperluanLain = implode(" | ", $details);
        
        return [
            'nama' => $data['nama'],
            'email' => $data['email'] ?? '',
            'nohp' => $data['nohp'],
            'alamat' => $data['alamat'] ?? '-',
            'umur' => (int) ($data['umur'] ?? 0),
            'asal' => $data['instansi'],
            'jenis_kelamin' => $data['jenis_kelamin'] ?? 'Laki-laki',
            'pendidikan' => $data['pendidikan'] ?? '-',
            'pekerjaan' => $data['pekerjaan'] ?? '-',
            'keperluan' => $data['keperluan'],
            'keperluan_lain' => $keperluanLain,
            'tanggal' => $data['tanggal_kunjungan'] ?? date('Y-m-d'),
            'waktu' => $data['jam_datang'] ?? date('H:i:s')
        ];
    }
}
