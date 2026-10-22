<?php
require_once 'koneksi.php';

// Mengambil data statistik singkat untuk ditampilkan di landing page
$q_mhs = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM mahasiswa");
$total_mhs = mysqli_fetch_assoc($q_mhs)['total'] ?? 0;

$q_prodi = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM prodi");
$total_prodi = mysqli_fetch_assoc($q_prodi)['total'] ?? 0;

$q_fakultas = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM fakultas");
$total_fakultas = mysqli_fetch_assoc($q_fakultas)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Akademik - Universitas Semantic</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 80px 0;
        }
        .card-portal {
            transition: transform 0.3s ease, shadow 0.3s ease;
            border: none;
            border-radius: 12px;
        }
        .card-portal:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        .stat-card {
            border-left: 5px solid #2a5298;
            border-radius: 8px;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fa-solid fa-graduation-cap me-2"></i>Universitas Semantic
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="#fitur">Portal Akses</a></li>
                    <li class="nav-item"><a class="nav-link" href="#statistik">Statistik</a></li>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-light btn-sm mt-1 mt-lg-0" href="login.php">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Sistem Informasi Manajemen Mahasiswa</h1>
            <p class="lead mb-4">Layanan Akademik Terpadu Berbasis Peran untuk Mahasiswa, Operator Program Studi, dan Administrator Universitas Semantic.</p>
            <a href="#fitur" class="btn btn-warning btn-lg fw-semibold shadow">Pilih Portal Login <i class="fa-solid fa-arrow-down ms-2"></i></a>
        </div>
    </section>

    <!-- Stat Counter -->
    <section id="statistik" class="container my-5">
        <div class="row g-4 text-center">
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-3 bg-white">
                    <div class="card-body">
                        <i class="fa-solid fa-user-graduate fa-2x text-primary mb-2"></i>
                        <h3 class="fw-bold"><?= number_format($total_mhs); ?></h3>
                        <p class="text-muted mb-0">Mahasiswa Terdaftar</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-3 bg-white">
                    <div class="card-body">
                        <i class="fa-solid fa-book-open fa-2x text-success mb-2"></i>
                        <h3 class="fw-bold"><?= number_format($total_prodi); ?></h3>
                        <p class="text-muted mb-0">Program Studi</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card stat-card shadow-sm p-3 bg-white">
                    <div class="card-body">
                        <i class="fa-solid fa-building-columns fa-2x text-warning mb-2"></i>
                        <h3 class="fw-bold"><?= number_format($total_fakultas); ?></h3>
                        <p class="text-muted mb-0">Fakultas</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Portal Akses Login (Role Based) -->
    <section id="fitur" class="container my-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Portal Akses Pengguna</h2>
            <p class="text-muted">Silakan pilih akses sesuai dengan peran (role) Anda di sistem</p>
        </div>

        <div class="row g-4 justify-content-center">
            <!-- Portal Mahasiswa -->
            <div class="col-md-4">
                <div class="card card-portal shadow-sm text-center p-4 bg-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3">
                            <span class="badge bg-primary-subtle text-primary p-3 rounded-circle">
                                <i class="fa-solid fa-user-graduate fa-3x"></i>
                            </span>
                        </div>
                        <h4 class="card-title fw-bold">Portal Mahasiswa</h4>
                        <p class="card-text text-muted flex-grow-1">Akses biodata diri, riwayat perkuliahan, cetak laporan data mahasiswa per program studi.</p>
                        <a href="login.php?type=mahasiswa" class="btn btn-primary w-100 mt-3">Login Mahasiswa <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>

            <!-- Portal Operator / Prodi -->
            <div class="col-md-4">
                <div class="card card-portal shadow-sm text-center p-4 bg-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3">
                            <span class="badge bg-success-subtle text-success p-3 rounded-circle">
                                <i class="fa-solid fa-user-gear fa-3x"></i>
                            </span>
                        </div>
                        <h4 class="card-title fw-bold">Portal Operator / Prodi</h4>
                        <p class="card-text text-muted flex-grow-1">Pengelolaan data mahasiswa per program studi, pembaruan biodata, serta verifikasi kelengkapan berkas.</p>
                        <a href="login.php?type=users" class="btn btn-success w-100 mt-3">Login Operator <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>

            <!-- Portal Administrator -->
            <div class="col-md-4">
                <div class="card card-portal shadow-sm text-center p-4 bg-white h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3">
                            <span class="badge bg-dark-subtle text-dark p-3 rounded-circle">
                                <i class="fa-solid fa-user-shield fa-3x"></i>
                            </span>
                        </div>
                        <h4 class="card-title fw-bold">Portal Administrator</h4>
                        <p class="card-text text-muted flex-grow-1">Pengelolaan akun pengguna (users), manajemen data fakultas, prodi, serta kontrol hak akses penuh.</p>
                        <a href="login.php?type=users" class="btn btn-dark w-100 mt-3">Login Admin <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">&copy; <?= date('Y'); ?> Universitas Semantic Create By Arip Irawadi. All Rights Reserved.</p>
            <small class="text-muted">Sistem Informasi Manajemen Mahasiswa Per Program Studi</small>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.bundle.min.js"></script>
</body>
</html>