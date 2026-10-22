<?php
// auth.php - dipakai di semua halaman setelah login
ob_start();
session_start();
require_once 'koneksi.php';

// Agar error database bisa ditangkap dengan try/catch
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Wajib login
if (!isset($_SESSION['is_login']) || $_SESSION['is_login'] !== true) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role_nama'];
$nama = $_SESSION['nama_lengkap'];

// Escape output agar aman dari XSS
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Batasi halaman hanya untuk role tertentu
function wajib_role(array $boleh) {
    global $role;
    if (!in_array($role, $boleh, true)) {
        http_response_code(403);
        exit('Akses ditolak. Halaman ini tidak tersedia untuk role Anda. <a href="dashboard.php">Kembali</a>');
    }
}

// Pesan notifikasi (ditampilkan di header.php)
function flash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

// Proteksi CSRF untuk semua form POST
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
function csrf_field() {
    return '<input type="hidden" name="csrf" value="' . e($_SESSION['csrf']) . '">';
}
function cek_csrf() {
    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Token form tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}

// Jalankan prepared statement
function q($sql, $types = '', ...$params) {
    global $koneksi;
    $st = mysqli_prepare($koneksi, $sql);
    if ($types !== '') {
        mysqli_stmt_bind_param($st, $types, ...$params);
    }
    mysqli_stmt_execute($st);
    return $st;
}

// Ambil semua baris hasil SELECT
function ambil_semua($sql, $types = '', ...$params) {
    $res = mysqli_stmt_get_result(q($sql, $types, ...$params));
    $rows = [];
    while ($r = mysqli_fetch_assoc($res)) {
        $rows[] = $r;
    }
    return $rows;
}

// Ambil satu baris hasil SELECT (atau null)
function ambil_satu($sql, $types = '', ...$params) {
    $rows = ambil_semua($sql, $types, ...$params);
    return $rows ? $rows[0] : null;
}

// Pesan error database yang ramah
function pesan_db(mysqli_sql_exception $ex) {
    switch ($ex->getCode()) {
        case 1062: return 'Data dengan nilai tersebut sudah ada (duplikat).';
        case 1451: return 'Data tidak bisa dihapus karena masih dipakai oleh data lain.';
        case 1452: return 'Data terkait (prodi/fakultas/role) tidak valid.';
        default:   return 'Terjadi kesalahan database: ' . $ex->getMessage();
    }
}
