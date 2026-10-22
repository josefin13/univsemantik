<?php
require_once 'auth.php';
wajib_role(['Administrator', 'Operator']);

$prodi_list = ambil_semua(
    "SELECT p.id_prodi, p.nama_prodi, f.nama_fakultas
     FROM prodi p
     JOIN fakultas f ON p.id_fakultas = f.id_fakultas
     ORDER BY f.nama_fakultas, p.nama_prodi"
);
$prodi_valid = array_map('intval', array_column($prodi_list, 'id_prodi'));

function tanggal_valid($t) {
    $d = DateTime::createFromFormat('Y-m-d', $t);
    return $d && $d->format('Y-m-d') === $t;
}

$aksi  = isset($_GET['aksi']) ? $_GET['aksi'] : 'list';
$error = '';
$data  = [
    'npm'            => '',
    'nama_mahasiswa' => '',
    'jenis_kelamin'  => 'L',
    'tempat_lahir'   => '',
    'tanggal_lahir'  => '',
    'tanggal_masuk'  => date('Y-m-d'),
    'alamat'         => '',
    'id_prodi'       => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $do = isset($_POST['do']) ? $_POST['do'] : '';

    if ($do === 'hapus') {
        try {
            q("DELETE FROM mahasiswa WHERE npm = ?", "s", trim(isset($_POST['npm']) ? $_POST['npm'] : ''));
            flash('success', 'Data mahasiswa berhasil dihapus.');
        } catch (mysqli_sql_exception $ex) {
            flash('danger', pesan_db($ex));
        }
        header("Location: mahasiswa.php");
        exit();
    }

    if ($do === 'tambah' || $do === 'ubah') {
        foreach ($data as $k => $v) {
            if (isset($_POST[$k])) {
                $data[$k] = trim($_POST[$k]);
            }
        }
        $password = trim(isset($_POST['password']) ? $_POST['password'] : '');

        if ($do === 'tambah' && !preg_match('/^[A-Za-z0-9]{3,20}$/', $data['npm'])) {
            $error = 'NPM wajib diisi (3-20 huruf/angka, tanpa spasi).';
        } elseif ($data['nama_mahasiswa'] === '' || strlen($data['nama_mahasiswa']) > 100) {
            $error = 'Nama mahasiswa wajib diisi (maksimal 100 karakter).';
        } elseif (!in_array($data['jenis_kelamin'], ['L', 'P'], true)) {
            $error = 'Jenis kelamin tidak valid.';
        } elseif ($data['tempat_lahir'] === '' || strlen($data['tempat_lahir']) > 50) {
            $error = 'Tempat lahir wajib diisi (maksimal 50 karakter).';
        } elseif (!tanggal_valid($data['tanggal_lahir'])) {
            $error = 'Tanggal lahir tidak valid.';
        } elseif (!tanggal_valid($data['tanggal_masuk'])) {
            $error = 'Tanggal masuk tidak valid.';
        } elseif (!in_array((int)$data['id_prodi'], $prodi_valid, true)) {
            $error = 'Program studi wajib dipilih.';
        } elseif ($do === 'tambah' && strlen($password) < 6) {
            $error = 'Password wajib diisi minimal 6 karakter.';
        } elseif ($do === 'ubah' && $password !== '' && strlen($password) < 6) {
            $error = 'Password baru minimal 6 karakter (kosongkan jika tidak ingin mengubah).';
        }

        if ($error === '') {
            try {
                $id_prodi = (int)$data['id_prodi'];
                if ($do === 'tambah') {
                    q("INSERT INTO mahasiswa (npm, password, nama_mahasiswa, jenis_kelamin, tempat_lahir, tanggal_lahir, tanggal_masuk, alamat, id_prodi)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                      "ssssssssi",
                      $data['npm'], password_hash($password, PASSWORD_DEFAULT), $data['nama_mahasiswa'],
                      $data['jenis_kelamin'], $data['tempat_lahir'], $data['tanggal_lahir'],
                      $data['tanggal_masuk'], $data['alamat'], $id_prodi);
                    flash('success', 'Mahasiswa baru berhasil ditambahkan.');
                } elseif ($password !== '') {
                    q("UPDATE mahasiswa SET nama_mahasiswa = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?,
                              tanggal_masuk = ?, alamat = ?, id_prodi = ?, password = ?
                       WHERE npm = ?",
                      "ssssssiss",
                      $data['nama_mahasiswa'], $data['jenis_kelamin'], $data['tempat_lahir'], $data['tanggal_lahir'],
                      $data['tanggal_masuk'], $data['alamat'], $id_prodi, password_hash($password, PASSWORD_DEFAULT),
                      $data['npm']);
                    flash('success', 'Data mahasiswa dan password berhasil diperbarui.');
                } else {
                    q("UPDATE mahasiswa SET nama_mahasiswa = ?, jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?,
                              tanggal_masuk = ?, alamat = ?, id_prodi = ?
                       WHERE npm = ?",
                      "ssssssis",
                      $data['nama_mahasiswa'], $data['jenis_kelamin'], $data['tempat_lahir'], $data['tanggal_lahir'],
                      $data['tanggal_masuk'], $data['alamat'], $id_prodi, $data['npm']);
                    flash('success', 'Data mahasiswa berhasil diperbarui.');
                }
                header("Location: mahasiswa.php");
                exit();
            } catch (mysqli_sql_exception $ex) {
                $error = pesan_db($ex);
            }
        }
        // Ada error: tampilkan kembali form dengan isian sebelumnya
        $aksi = $do;
    }
}

// Muat data untuk form ubah
if ($aksi === 'ubah' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    $row = ambil_satu("SELECT * FROM mahasiswa WHERE npm = ?", "s", isset($_GET['npm']) ? $_GET['npm'] : '');
    if (!$row) {
        flash('danger', 'Data mahasiswa tidak ditemukan.');
        header("Location: mahasiswa.php");
        exit();
    }
    $data = array_intersect_key($row, $data);
}

$halaman = 'mahasiswa';
$judul   = 'Data Mahasiswa Prodi';
require 'header.php';
?>

<?php if ($aksi === 'tambah' || $aksi === 'ubah'): ?>
    <!-- FORM TAMBAH / UBAH MAHASISWA -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="fa-solid fa-user-pen me-2"></i> <?= $aksi === 'tambah' ? 'Tambah Mahasiswa' : 'Ubah Data Mahasiswa'; ?>
        </div>
        <div class="card-body">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= e($error); ?></div>
            <?php endif; ?>
            <?php if (!$prodi_list): ?>
                <div class="alert alert-warning">Belum ada program studi. Tambahkan dulu di menu <a href="master.php?tab=prodi">Fakultas &amp; Prodi</a>.</div>
            <?php endif; ?>

            <form method="POST" action="mahasiswa.php?aksi=<?= e($aksi); ?>">
                <?= csrf_field(); ?>
                <input type="hidden" name="do" value="<?= e($aksi); ?>">

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">NPM</label>
                        <input type="text" name="npm" class="form-control" value="<?= e($data['npm']); ?>"
                               <?= $aksi === 'ubah' ? 'readonly' : 'required'; ?>>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Nama Mahasiswa</label>
                        <input type="text" name="nama_mahasiswa" class="form-control" maxlength="100" value="<?= e($data['nama_mahasiswa']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select" required>
                            <option value="L" <?= $data['jenis_kelamin'] === 'L' ? 'selected' : ''; ?>>Laki-Laki</option>
                            <option value="P" <?= $data['jenis_kelamin'] === 'P' ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" maxlength="50" value="<?= e($data['tempat_lahir']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="<?= e($data['tanggal_lahir']); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Tanggal Masuk</label>
                        <input type="date" name="tanggal_masuk" class="form-control" value="<?= e($data['tanggal_masuk']); ?>" required>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-semibold">Program Studi</label>
                        <select name="id_prodi" class="form-select" required>
                            <option value="">-- Pilih Program Studi --</option>
                            <?php foreach ($prodi_list as $p): ?>
                                <option value="<?= (int)$p['id_prodi']; ?>" <?= (string)$data['id_prodi'] === (string)$p['id_prodi'] ? 'selected' : ''; ?>>
                                    <?= e($p['nama_prodi']); ?> (<?= e($p['nama_fakultas']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Alamat</label>
                        <textarea name="alamat" class="form-control" rows="2"><?= e($data['alamat']); ?></textarea>
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
                    <a href="mahasiswa.php" class="btn btn-outline-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- DAFTAR MAHASISWA -->
    <?php
    $cari = trim(isset($_GET['cari']) ? $_GET['cari'] : '');
    $like = '%' . $cari . '%';
    $rows = ambil_semua(
        "SELECT m.*, p.nama_prodi, f.nama_fakultas
         FROM mahasiswa m
         JOIN prodi p ON m.id_prodi = p.id_prodi
         JOIN fakultas f ON p.id_fakultas = f.id_fakultas
         WHERE m.npm LIKE ? OR m.nama_mahasiswa LIKE ?
         ORDER BY p.nama_prodi, m.npm ASC",
        "ss", $like, $like
    );
    ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
            <span class="fw-bold"><i class="fa-solid fa-users me-2"></i> Daftar Mahasiswa (<?= count($rows); ?>)</span>
            <div class="d-flex gap-2 no-print">
                <form method="GET" class="d-flex gap-2">
                    <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari NPM / nama" value="<?= e($cari); ?>">
                    <button class="btn btn-sm btn-outline-secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <button class="btn btn-sm btn-outline-primary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Cetak</button>
                <a href="mahasiswa.php?aksi=tambah" class="btn btn-sm btn-primary"><i class="fa-solid fa-plus me-1"></i> Tambah</a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No.</th>
                            <th>NPM</th>
                            <th>Nama Mahasiswa</th>
                            <th>JK</th>
                            <th>Tempat/Tgl Lahir</th>
                            <th>Program Studi</th>
                            <th>Tgl Masuk</th>
                            <th>Alamat</th>
                            <th class="no-print">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($rows as $r): ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td><?= e($r['npm']); ?></td>
                                <td><?= e($r['nama_mahasiswa']); ?></td>
                                <td><?= e($r['jenis_kelamin']); ?></td>
                                <td><?= e($r['tempat_lahir']) . ', ' . date('d/m/Y', strtotime($r['tanggal_lahir'])); ?></td>
                                <td><?= e($r['nama_prodi']); ?><br><small class="text-muted"><?= e($r['nama_fakultas']); ?></small></td>
                                <td><?= date('d/m/Y', strtotime($r['tanggal_masuk'])); ?></td>
                                <td><?= e($r['alamat']); ?></td>
                                <td class="no-print text-nowrap">
                                    <a href="mahasiswa.php?aksi=ubah&amp;npm=<?= urlencode($r['npm']); ?>" class="btn btn-sm btn-warning" title="Ubah">
                                        <i class="fa-solid fa-pen"></i>
                                    </a>
                                    <form method="POST" action="mahasiswa.php" class="d-inline"
                                          onsubmit="return confirm('Hapus mahasiswa <?= e(addslashes($r['nama_mahasiswa'])); ?>?');">
                                        <?= csrf_field(); ?>
                                        <input type="hidden" name="do" value="hapus">
                                        <input type="hidden" name="npm" value="<?= e($r['npm']); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$rows): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">Tidak ada data mahasiswa.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require 'footer.php'; ?>
