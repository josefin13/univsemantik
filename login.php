<?php
session_start();
require_once 'koneksi.php';

// Jika sudah login, langsung alihkan ke dashboard
if (isset($_SESSION['is_login']) &&$_SESSION['is_login'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error = '';$login_type = isset($_GET['type']) &&$_GET['type'] == 'mahasiswa' ? 'mahasiswa' : 'users';

// Proses Form Login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_type =$_POST['login_type'];
    $identifier = trim($_POST['identifier']); // NPM atau Username
    $password   = trim($_POST['password']);

    if (empty($identifier) || empty($password)) {$error = "Semua kolom wajib diisi!";
    } else {
        if ($login_type == 'mahasiswa') {
            // Login Mahasiswa via tabel `mahasiswa`
            $query = mysqli_prepare($koneksi, "SELECT m.*, p.nama_prodi, f.nama_fakultas 
                                               FROM mahasiswa m 
                                               JOIN prodi p ON m.id_prodi = p.id_prodi 
                                               JOIN fakultas f ON p.id_fakultas = f.id_fakultas 
                                               WHERE m.npm = ?");
            mysqli_stmt_bind_param($query, "s", $identifier);
            mysqli_stmt_execute($query);
            $result = mysqli_stmt_get_result($query);

            if ($row = mysqli_fetch_assoc($result)) {
                // Verifikasi password (mendukung password hash atau plaintext)
                if (password_verify($password, $row['password']) || $password === $row['password']) {  
                    $_SESSION['is_login']     = true;
                    $_SESSION['user_type']    = 'Mahasiswa';
                    $_SESSION['role_nama']    = 'Mahasiswa';
                    $_SESSION['npm']          =$row['npm'];
                    $_SESSION['nama_lengkap'] =$row['nama_mahasiswa'];
                    $_SESSION['nama_prodi']   =$row['nama_prodi'];
                    $_SESSION['nama_fakultas']=$row['nama_fakultas'];

                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Password Mahasiswa salah!";
                }
            } else {
                $error = "NPM Mahasiswa tidak ditemukan!";
            }
        } else {
            // Login Operator / Admin via tabel `users`
            $query = mysqli_prepare($koneksi, "SELECT u.*, r.nama_role 
                                               FROM users u 
                                               JOIN roles r ON u.id_role = r.id_role 
                                               WHERE u.username = ?");
            mysqli_stmt_bind_param($query, "s", $identifier);
            mysqli_stmt_execute($query);
            $result = mysqli_stmt_get_result($query);

            if ($row = mysqli_fetch_assoc($result)) {
                if (password_verify($password, $row['password']) || $password === $row['password']) {
                    $_SESSION['is_login']     = true;
                    $_SESSION['user_type']    = 'User';
                    $_SESSION['id_user']      =$row['id_user'];
                    $_SESSION['role_nama']    =$row['nama_role']; // Administrator / Operator
                    $_SESSION['nama_lengkap'] =$row['nama_lengkap'];

                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Password Pengguna salah!";
                }
            } else {
                $error = "Username tidak ditemukan!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login System - Universitas Semantic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="text-center mb-4">
        <h3 class="fw-bold text-primary"><i class="fa-solid fa-graduation-cap me-2"></i>Universitas Semantic</h3>
        <p class="text-muted small">Silakan login untuk mengakses sistem</p>
    </div>

    <!-- Tab Pilihan Login -->
    <ul class="nav nav-pills nav-justified mb-3" id="pills-tab">
        <li class="nav-item">
            <a class="nav-link <?= $login_type == 'mahasiswa' ? 'active' : '' ?>" href="login.php?type=mahasiswa">
                <i class="fa-solid fa-user-graduate me-1"></i> Mahasiswa
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $login_type == 'users' ? 'active' : '' ?>" href="login.php?type=users">
                <i class="fa-solid fa-user-gear me-1"></i> Staf / Admin
            </a>
        </li>
    </ul>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show p-2 small" role="alert">
            <i class="fa-solid fa-circle-exclamation me-1"></i> <?= $error; ?>
            <button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <form action="login.php?type=<?= $login_type; ?>" method="POST">
        <input type="hidden" name="login_type" value="<?= $login_type; ?>">

        <div class="mb-3">
            <label class="form-label small fw-semibold">
                <?= $login_type == 'mahasiswa' ? 'NPM (Nomor Pokok Mahasiswa)' : 'Username Staf/Admin' ?>
            </label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid <?= $login_type == 'mahasiswa' ? 'fa-id-card' : 'fa-user' ?>"></i></span>
                <input type="text" name="identifier" class="form-control" placeholder="<?= $login_type == 'mahasiswa' ? 'Masukkan NPM' : 'Masukkan Username' ?>" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 fw-bold py-2 mt-2">
            <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk Sekarang
        </button>
    </form>

    <div class="text-center mt-4">
        <a href="index.php" class="text-decoration-none small text-muted">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Landing Page
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.bundle.min.js"></script>
</body>
</html>