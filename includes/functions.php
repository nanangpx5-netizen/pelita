<?php
/**
 * Helper Functions
 * @package PELITA
 * @version 1.0.0
 */

/**
 * Generate base URL
 */
function base_url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL
 */
function asset_url(string $path): string
{
    return base_url('public/assets/' . ltrim($path, '/'));
}

/**
 * Redirect to URL
 */
function redirect(string $path, int $code = 302): void
{
    header('Location: ' . base_url($path), true, $code);
    exit;
}

/**
 * Sanitize input
 */
function sanitize(string $input): string
{
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize array
 */
function sanitize_array(array $data): array
{
    return array_map(function ($value) {
        return is_string($value) ? sanitize($value) : $value;
    }, $data);
}

/**
 * Validate email
 */
function validate_email(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Indonesia)
 */
function validate_phone(string $phone): bool
{
    return preg_match('/^(\+62|62|0)[0-9]{9,13}$/', preg_replace('/\s+/', '', $phone));
}

/**
 * Format date to Indonesian
 */
function format_tanggal(string $date, string $format = 'd F Y'): string
{
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $hari = [
        'Sunday' => 'Minggu',
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu'
    ];

    $timestamp = strtotime($date);
    $result = date($format, $timestamp);

    // Replace month names
    foreach ($bulan as $num => $nama) {
        $result = str_replace(date('F', mktime(0, 0, 0, $num, 1)), $nama, $result);
    }

    // Replace day names
    foreach ($hari as $en => $id) {
        $result = str_replace($en, $id, $result);
    }

    return $result;
}

/**
 * Format datetime
 */
function format_waktu(string $datetime): string
{
    return date('d/m/Y H:i', strtotime($datetime));
}

/**
 * Get nama bulan
 */
function get_nama_bulan(int $bulan): string
{
    $nama = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];
    return $nama[$bulan] ?? '';
}

/**
 * Flash message
 */
function flash(string $key, ?string $message = null)
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
    } else {
        $msg = $_SESSION['flash'][$key] ?? null;
        unset($_SESSION['flash'][$key]);
        return $msg;
    }
}

/**
 * Check if flash exists
 */
function has_flash(string $key): bool
{
    return isset($_SESSION['flash'][$key]);
}

/**
 * Generate pagination
 */
function paginate(int $total, int $page, int $perPage, string $baseUrl): array
{
    $totalPages = ceil($total / $perPage);

    return [
        'total' => $total,
        'per_page' => $perPage,
        'current_page' => $page,
        'total_pages' => $totalPages,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages,
        'prev_url' => $page > 1 ? $baseUrl . '?page=' . ($page - 1) : null,
        'next_url' => $page < $totalPages ? $baseUrl . '?page=' . ($page + 1) : null
    ];
}

/**
 * Get client IP address
 */
function get_client_ip(): string
{
    $keys = ['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];

    foreach ($keys as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = explode(',', $_SERVER[$key])[0];
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }

    return '0.0.0.0';
}

/**
 * JSON response
 */
function json_response(array $data, int $code = 200): void
{
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Check if request is AJAX
 */
function is_ajax(): bool
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

/**
 * Get reference data
 */
function get_ref_data(string $table): array
{
    $db = Database::getInstance();
    return $db->fetchAll("SELECT * FROM {$table} ORDER BY id ASC");
}

/**
 * Debug dump
 */
function dd(...$vars): void
{
    echo '<pre style="background:#1e1e1e;color:#dcdcdc;padding:15px;margin:10px;border-radius:5px;overflow:auto;">';
    foreach ($vars as $var) {
        var_dump($var);
    }
    echo '</pre>';
    exit;
}

/**
 * Relative time (e.g., "5 menit yang lalu")
 */
function time_ago(string $datetime): string
{
    $diff = time() - strtotime($datetime);
    if ($diff < 60)
        return "$diff detik yang lalu";
    if ($diff < 3600)
        return floor($diff / 60) . " menit yang lalu";
    if ($diff < 86400)
        return floor($diff / 3600) . " jam yang lalu";
    if ($diff < 604800)
        return floor($diff / 86400) . " hari yang lalu";
    return date('d/m/Y', strtotime($datetime));
}

