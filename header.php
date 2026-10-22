<?php
// header.php - kerangka halaman (sidebar + judul).
// Atur $halaman dan $judul sebelum require file ini.
$halaman = isset($halaman) ? $halaman : 'dashboard';
$judul   = isset($judul) ? $judul : 'Dashboard Utama';
$flash   = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
unset($_SESSION['flash']);

function aktif($a, $b) {
    return $a === $b ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($judul); ?> - Universitas Semantic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            background-color: #212529;
        }
        .sidebar .nav-link {
            color: #c2c7d0;
            border-radius: 5px;
            margin-bottom: 4px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #fff;
            background-color: #0d6efd;
        }
        @media print {
            .sidebar, .no-print { display: none !important; }
            main { margin-left: 0 !important; width: 100% !important; max-width: 100% !important; flex: 0 0 100% !important; }
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Navigation (menu mengikuti role) -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3">
            <div class="text-white text-center pb-3 mb-3 border-bottom">
                <i class="fa-solid fa-graduation-cap fa-2x text-primary mb-2"></i>
                <h6 class="fw-bold mb-0">Universitas Semantic</h6>
                <small class="text-muted d-block">Role: <span class="badge bg-info text-dark"><?= e($role); ?></span></small>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link <?= aktif($halaman, 'dashboard'); ?>" href="dashboard.php">
                        <i class="fa-solid fa-gauge me-2"></i> Dashboard
                    </a>
                </li>

                <?php if ($role === 'Mahasiswa'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="window.print(); return false;">
                            <i class="fa-solid fa-file-pdf me-2"></i> Cetak Biodata
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($role === 'Operator' || $role === 'Administrator'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= aktif($halaman, 'mahasiswa'); ?>" href="mahasiswa.php">
                            <i class="fa-solid fa-users-gear me-2"></i> Data Mahasiswa Prodi
                        </a>
                    </li>
                <?php endif; ?>

                <?php if ($role === 'Administrator'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= aktif($halaman, 'users'); ?>" href="users.php">
                            <i class="fa-solid fa-user-shield me-2"></i> Kelola User Staf
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= aktif($halaman, 'master'); ?>" href="master.php">
                            <i class="fa-solid fa-building-columns me-2"></i> Fakultas &amp; Prodi
                        </a>
                    </li>
                <?php endif; ?>

                <li class="nav-item mt-4 border-top pt-2">
                    <a class="nav-link text-danger" href="logout.php" onclick="return confirm('Yakin ingin keluar?')">
                        <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Area Konten Utama -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4 bg-light min-vh-100">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-4 border-bottom">
                <h1 class="h3"><?= e($judul); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="navbar-text me-3 fw-semibold">
                        Halo, <i class="fa-solid fa-circle-user text-primary ms-1"></i> <?= e($nama); ?>
                    </span>
                </div>
            </div>

            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['type']); ?> alert-dismissible fade show no-print" role="alert">
                    <?= e($flash['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
