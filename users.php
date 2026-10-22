<?php
require_once 'auth.php';
wajib_role(['Administrator']);

$id_saya    = (int)(isset($_SESSION['id_user']) ? $_SESSION['id_user'] : 0);
$roles      = ambil_semua("SELECT id_role, nama_role FROM roles ORDER BY id_role");
$role_valid = array_map('intval', array_column($roles, 'id_role'));

$aksi  = isset($_GET['aksi']) ? $_GET['aksi'] : 'list';
$error = '';
$data  = ['id_user' => '', 'username' => '', 'nama_lengkap' => '', 'id_role' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $do = isset($_POST['do']) ? $_POST['do'] : '';

    if ($do === 'hapus') {
        $id = (int)(isset($_POST['id_user']) ? $_POST['id_user'] : 0);
        if ($id === $id_saya) {
            flash('danger', 'Anda tidak bisa menghapus akun yang sedang dipakai login.');
        } else {
            try {
                q("DELETE FROM users WHERE id_user = ?", "i", $id);
                flash('success', 'User berhasil dihapus.');
            } catch (mysqli_sql_exception $ex) {
                flash('danger', pesan_db($ex));
            }
        }
        header("Location: users.php");
        exit();
    }

    if ($do === 'tambah' || $do === 'ubah') {
        foreach ($data as $k => $v) {
            if (isset($_POST[$k])) {
                $data[$k] = trim($_POST[$k]);
            }
        }
        $password = trim(isset($_POST['password']) ? $_POST['password'] : '');
        $id       = (int)$data['id_user'];

        // Admin yang sedang login tidak boleh menurunkan role dirinya sendiri
        if ($do === 'ubah' && $id === $id_saya) {
            $cur = ambil_satu("SELECT id_role FROM users WHERE id_user = ?", "i", $id);
            if ($cur) {
                $data['id_role'] = (string)$cur['id_role'];
            }
        }

        if (!preg_match('/^[A-Za-z0-9_.-]{3,50}$/', $data['username'])) {
            $error = 'Username wajib diisi (3-50 karakter: huruf, angka, titik, strip, underscore).';
        } elseif ($data['nama_lengkap'] === '' || strlen($data['nama_lengkap']) > 100) {
            $error = 'Nama lengkap wajib diisi (maksimal 100 karakter).';
        } elseif (!in_array((int)$data['id_role'], $role_valid, true)) {
            $error = 'Role wajib dipilih.';
        } elseif ($do === 'tambah' && strlen($password) < 6) {
            $error = 'Password wajib diisi minimal 6 karakter.';
        } elseif ($do === 'ubah' && $password !== '' && strlen($password) < 6) {
            $error = 'Password baru minimal 6 karakter (kosongkan jika tidak ingin mengubah).';
        }

        if ($error === '') {
            try {
                $id_role = (int)$data['id_role'];
                if ($do === 'tambah') {
                    q("INSERT INTO users (username, password, nama_lengkap, id_role) VALUES (?, ?, ?, ?)",
                      "sssi", $data['username'], password_hash($password, PASSWORD_DEFAULT), $data['nama_lengkap'], $id_role);
                    flash('success', 'User baru berhasil ditambahkan.');
                } elseif ($password !== '') {
                    q("UPDATE users SET username = ?, nama_lengkap = ?, id_role = ?, password = ? WHERE id_user = ?",
                      "ssisi", $data['username'], $data['nama_lengkap'], $id_role, password_hash($password, PASSWORD_DEFAULT), $id);
                    flash('success', 'Data user dan password berhasil diperbarui.');
                } else {
                    q("UPDATE users SET username = ?, nama_lengkap = ?, id_role = ? WHERE id_user = ?",
                      "ssii", $data['username'], $data['nama_lengkap'], $id_role, $id);
                    flash('success', 'Data user berhasil diperbarui.');
                }
                if ($do === 'ubah' && $id === $id_saya) {
                    $_SESSION['nama_lengkap'] = $data['nama_lengkap'];
                }
                header("Location: users.php");
                exit();
            } catch (mysqli_sql_exception $ex) {
                $error = pesan_db($ex);
            }
        }
        $aksi = $do;
    }
}

// Muat data untuk form ubah
if ($aksi === 'ubah' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $row = ambil_satu("SELECT id_user, username, nama_lengkap, id_role FROM users WHERE id_user = ?", "i",
                      (int)(isset($_GET['id']) ? $_GET['id'] : 0));
    if (!$row) {
        flash('danger', 'User tidak ditemukan.');
        header("Location: users.php");
        exit();
    }
    $data = $row;
}

$halaman = 'users';
$judul   = 'Kelola User Staf';
require 'header.php';
?>

<?php if ($aksi === 'tambah' || $aksi === 'ubah'): ?>
    <!-- FORM TAMBAH / UBAH USER -->
    <?php $edit_diri = ($aksi === 'ubah' && (int)$data['id_user'] === $id_saya); ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fa-solid fa-user-gear me-2"></i> <?= $aksi === 'tambah' ? 'Tambah User Staf' : 'Ubah User Staf'; ?>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error); ?></div>
            <?php endif; ?>

            <form method="POST" action="users.php?aksi=<?= e($aksi); ?>">
                <?= csrf_field(); ?>
                <input type="hidden" name="do" value="<?= e($aksi); ?>">
                <input type="hidden" name="id_user" value="<?= e($data['id_user']); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Username</label>
                        <input type="text" name="username" class="form-control" maxlength="50" value="<?= e($data['username']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" maxlength="100" value="<?= e($data['nama_lengkap']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Role</label>
                        <select name="id_role" class="form-select" <?= $edit_diri ? 'disabled' : 'required'; ?>>
                            <option value="">-- Pilih Role --</option>
                            <?php foreach ($roles as $r): ?>
                                <option value="<?= (int)$r['id_role']; ?>" <?= (string)$data['id_role'] === (string)$r['id_role'] ? 'selected' : ''; ?>>
                                    <?= e($r['nama_role']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if ($edit_diri): ?>
                            <div class="form-text">Role akun yang sedang Anda pakai tidak bisa diubah.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">
                            Password <?= $aksi === 'ubah' ? '<small class="text-muted fw-normal">(kosongkan jika tidak diubah)</small>' : ''; ?>
                        </label>
                        <input type="password" name="password" class="form-control" autocomplete="new-password" <?= $aksi === 'tambah' ? 'required' : ''; ?>>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan</button>
                    <a href="users.php" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- DAFTAR USER -->
    <?php
    $rows = ambil_semua(
        "SELECT u.id_user, u.username, u.nama_lengkap, u.created_at, r.nama_role
         FROM users u
         JOIN roles r ON u.id_role = r.id_role
         ORDER BY r.id_role, u.username"
    );
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-bold"><i class="fa-solid fa-user-shield me-2"></i> Daftar User Staf (<?= count($rows); ?>)</span>
            <a href="users.php?aksi=tambah" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i> Tambah User</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No.</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Role</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($rows as $r): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= e($r['username']); ?><?= (int)$r['id_user'] === $id_saya ? ' <span class="badge bg-secondary">Anda</span>' : ''; ?></td>
                                <td><?= e($r['nama_lengkap']); ?></td>
                                <td><span class="badge <?= $r['nama_role'] === 'Administrator' ? 'bg-dark' : 'bg-success'; ?>"><?= e($r['nama_role']); ?></span></td>
                                <td><?= $r['created_at'] ? date('d/m/Y', strtotime($r['created_at'])) : '-'; ?></td>
                                <td class="text-nowrap">
                                    <a href="users.php?aksi=ubah&amp;id=<?= (int)$r['id_user']; ?>" class="btn btn-sm btn-warning" title="Ubah">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <?php if ((int)$r['id_user'] !== $id_saya): ?>
                                        <form method="POST" action="users.php" class="d-inline"
                                              onsubmit="return confirm('Hapus user <?= e(addslashes($r['username'])); ?>?');">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="do" value="hapus">
                                            <input type="hidden" name="id_user" value="<?= (int)$r['id_user']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$rows): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">Belum ada user.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require 'footer.php'; ?>
