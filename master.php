<?php
require_once 'auth.php';
wajib_role(['Administrator']);

$tab = (isset($_GET['tab']) && $_GET['tab'] === 'prodi') ? 'prodi' : 'fakultas';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    cek_csrf();
    $do          = isset($_POST['do']) ? $_POST['do'] : '';
    $tab_kembali = (strpos($do, 'prodi_') === 0) ? 'prodi' : 'fakultas';

    try {
        switch ($do) {
            case 'fak_tambah':
            case 'fak_ubah':
                $nama = trim(isset($_POST['nama']) ? $_POST['nama'] : '');
                if ($nama === '' || strlen($nama) > 100) {
                    throw new InvalidArgumentException('Nama fakultas wajib diisi (maksimal 100 karakter).');
                }
                if ($do === 'fak_tambah') {
                    q("INSERT INTO fakultas (nama_fakultas) VALUES (?)", "s", $nama);
                    flash('success', 'Fakultas berhasil ditambahkan.');
                } else {
                    q("UPDATE fakultas SET nama_fakultas = ? WHERE id_fakultas = ?", "si", $nama, (int)$_POST['id']);
                    flash('success', 'Fakultas berhasil diperbarui.');
                }
                break;

            case 'fak_hapus':
                q("DELETE FROM fakultas WHERE id_fakultas = ?", "i", (int)$_POST['id']);
                flash('success', 'Fakultas berhasil dihapus.');
                break;

            case 'prodi_tambah':
            case 'prodi_ubah':
                $nama = trim(isset($_POST['nama']) ? $_POST['nama'] : '');
                $id_f = (int)(isset($_POST['id_fakultas']) ? $_POST['id_fakultas'] : 0);
                if ($nama === '' || strlen($nama) > 100) {
                    throw new InvalidArgumentException('Nama program studi wajib diisi (maksimal 100 karakter).');
                }
                if ($id_f <= 0) {
                    throw new InvalidArgumentException('Fakultas wajib dipilih.');
                }
                if ($do === 'prodi_tambah') {
                    q("INSERT INTO prodi (nama_prodi, id_fakultas) VALUES (?, ?)", "si", $nama, $id_f);
                    flash('success', 'Program studi berhasil ditambahkan.');
                } else {
                    q("UPDATE prodi SET nama_prodi = ?, id_fakultas = ? WHERE id_prodi = ?", "sii", $nama, $id_f, (int)$_POST['id']);
                    flash('success', 'Program studi berhasil diperbarui.');
                }
                break;

            case 'prodi_hapus':
                q("DELETE FROM prodi WHERE id_prodi = ?", "i", (int)$_POST['id']);
                flash('success', 'Program studi berhasil dihapus.');
                break;
        }
    } catch (mysqli_sql_exception $ex) {
        flash('danger', pesan_db($ex));
    } catch (InvalidArgumentException $ex) {
        flash('danger', $ex->getMessage());
    }

    header("Location: master.php?tab=" . $tab_kembali);
    exit();
}

$fakultas = ambil_semua(
    "SELECT f.id_fakultas, f.nama_fakultas, COUNT(p.id_prodi) AS jml_prodi
     FROM fakultas f
     LEFT JOIN prodi p ON p.id_fakultas = f.id_fakultas
     GROUP BY f.id_fakultas, f.nama_fakultas
     ORDER BY f.nama_fakultas"
);
$prodi = ambil_semua(
    "SELECT p.id_prodi, p.nama_prodi, p.id_fakultas, f.nama_fakultas, COUNT(m.npm) AS jml_mhs
     FROM prodi p
     JOIN fakultas f ON p.id_fakultas = f.id_fakultas
     LEFT JOIN mahasiswa m ON m.id_prodi = p.id_prodi
     GROUP BY p.id_prodi, p.nama_prodi, p.id_fakultas, f.nama_fakultas
     ORDER BY f.nama_fakultas, p.nama_prodi"
);

// Baris yang sedang diubah (jika ada)
$edit_id  = (int)(isset($_GET['edit']) ? $_GET['edit'] : 0);
$edit_fak = null;
$edit_prd = null;
if ($edit_id > 0) {
    $sumber = ($tab === 'prodi') ? $prodi : $fakultas;
    $kunci  = ($tab === 'prodi') ? 'id_prodi' : 'id_fakultas';
    foreach ($sumber as $s) {
        if ((int)$s[$kunci] === $edit_id) {
            if ($tab === 'prodi') { $edit_prd = $s; } else { $edit_fak = $s; }
        }
    }
}

$halaman = 'master';
$judul   = 'Fakultas & Prodi';
require 'header.php';
?>

<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'fakultas' ? 'active' : ''; ?>" href="master.php?tab=fakultas">
            <i class="fa-solid fa-building-columns me-1"></i> Fakultas (<?= count($fakultas); ?>)
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'prodi' ? 'active' : ''; ?>" href="master.php?tab=prodi">
            <i class="fa-solid fa-book-open me-1"></i> Program Studi (<?= count($prodi); ?>)
        </a>
    </li>
</ul>

<?php if ($tab === 'fakultas'): ?>
    <!-- TAB FAKULTAS -->
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <?= $edit_fak ? 'Ubah Fakultas' : 'Tambah Fakultas'; ?>
                </div>
                <div class="card-body">
                    <form method="POST" action="master.php">
                        <?= csrf_field(); ?>
                        <input type="hidden" name="do" value="<?= $edit_fak ? 'fak_ubah' : 'fak_tambah'; ?>">
                        <?php if ($edit_fak): ?>
                            <input type="hidden" name="id" value="<?= (int)$edit_fak['id_fakultas']; ?>">
                        <?php endif; ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Fakultas</label>
                            <input type="text" name="nama" class="form-control" maxlength="100"
                                   value="<?= e($edit_fak ? $edit_fak['nama_fakultas'] : ''); ?>" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan</button>
                        <?php if ($edit_fak): ?>
                            <a href="master.php?tab=fakultas" class="btn btn-outline-secondary">Batal</a>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-dark">
                                <tr><th>No.</th><th>Nama Fakultas</th><th>Jumlah Prodi</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($fakultas as $f): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= e($f['nama_fakultas']); ?></td>
                                        <td><?= (int)$f['jml_prodi']; ?></td>
                                        <td class="text-nowrap">
                                            <a href="master.php?tab=fakultas&amp;edit=<?= (int)$f['id_fakultas']; ?>" class="btn btn-sm btn-warning" title="Ubah">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form method="POST" action="master.php" class="d-inline"
                                                  onsubmit="return confirm('Hapus fakultas ini? Semua program studi di dalamnya juga ikut terhapus.');">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="do" value="fak_hapus">
                                                <input type="hidden" name="id" value="<?= (int)$f['id_fakultas']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$fakultas): ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">Belum ada fakultas.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- TAB PROGRAM STUDI -->
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white fw-bold">
                    <?= $edit_prd ? 'Ubah Program Studi' : 'Tambah Program Studi'; ?>
                </div>
                <div class="card-body">
                    <?php if (!$fakultas): ?>
                        <div class="alert alert-warning mb-0">
                            Belum ada fakultas. Tambahkan dulu di tab <a href="master.php?tab=fakultas">Fakultas</a>.
                        </div>
                    <?php else: ?>
                        <form method="POST" action="master.php">
                            <?= csrf_field(); ?>
                            <input type="hidden" name="do" value="<?= $edit_prd ? 'prodi_ubah' : 'prodi_tambah'; ?>">
                            <?php if ($edit_prd): ?>
                                <input type="hidden" name="id" value="<?= (int)$edit_prd['id_prodi']; ?>">
                            <?php endif; ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Nama Program Studi</label>
                                <input type="text" name="nama" class="form-control" maxlength="100"
                                       value="<?= e($edit_prd ? $edit_prd['nama_prodi'] : ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Fakultas</label>
                                <select name="id_fakultas" class="form-select" required>
                                    <option value="">-- Pilih Fakultas --</option>
                                    <?php foreach ($fakultas as $f): ?>
                                        <option value="<?= (int)$f['id_fakultas']; ?>"
                                            <?= ($edit_prd && (int)$edit_prd['id_fakultas'] === (int)$f['id_fakultas']) ? 'selected' : ''; ?>>
                                            <?= e($f['nama_fakultas']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan</button>
                            <?php if ($edit_prd): ?>
                                <a href="master.php?tab=prodi" class="btn btn-outline-secondary">Batal</a>
                            <?php endif; ?>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0 align-middle">
                            <thead class="table-dark">
                                <tr><th>No.</th><th>Program Studi</th><th>Fakultas</th><th>Jumlah Mhs</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                <?php $no = 1; foreach ($prodi as $p): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= e($p['nama_prodi']); ?></td>
                                        <td><?= e($p['nama_fakultas']); ?></td>
                                        <td><?= (int)$p['jml_mhs']; ?></td>
                                        <td class="text-nowrap">
                                            <a href="master.php?tab=prodi&amp;edit=<?= (int)$p['id_prodi']; ?>" class="btn btn-sm btn-warning" title="Ubah">
                                                <i class="fa-solid fa-pen"></i>
                                            </a>
                                            <form method="POST" action="master.php" class="d-inline"
                                                  onsubmit="return confirm('Hapus program studi ini?');">
                                                <?= csrf_field(); ?>
                                                <input type="hidden" name="do" value="prodi_hapus">
                                                <input type="hidden" name="id" value="<?= (int)$p['id_prodi']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus"><i class="fa-solid fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$prodi): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-4">Belum ada program studi.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require 'footer.php'; ?>
