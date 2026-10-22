<?php
require_once 'auth.php';

$halaman = 'dashboard';
$judul   = 'Dashboard Utama';
require 'header.php';
?>

            <!-- TAMPILAN DASHBOARD MAHASISWA -->
            <?php if ($role === 'Mahasiswa'): ?>
                <?php
                $mhs = ambil_satu(
                    "SELECT m.*, p.nama_prodi, f.nama_fakultas
                     FROM mahasiswa m
                     JOIN prodi p ON m.id_prodi = p.id_prodi
                     JOIN fakultas f ON p.id_fakultas = f.id_fakultas
                     WHERE m.npm = ?",
                    "s", $_SESSION['npm']
                );
                ?>
                <?php if (!$mhs): ?>
                    <div class="alert alert-warning">Data biodata tidak ditemukan.</div>
                <?php else: ?>
                    <div class="card shadow-sm border-0 mb-4" id="profil">
                        <div class="card-header bg-primary text-white fw-bold">
                            <i class="fa-solid fa-id-card me-2"></i> Biodata Mahasiswa
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr><th width="35%">NPM</th><td>: <?= e($mhs['npm']); ?></td></tr>
                                        <tr><th>Nama Mahasiswa</th><td>: <?= e($mhs['nama_mahasiswa']); ?></td></tr>
                                        <tr><th>Jenis Kelamin</th><td>: <?= $mhs['jenis_kelamin'] === 'L' ? 'Laki-Laki' : 'Perempuan'; ?></td></tr>
                                        <tr><th>Tempat, Tgl Lahir</th><td>: <?= e($mhs['tempat_lahir']) . ', ' . date('d-m-Y', strtotime($mhs['tanggal_lahir'])); ?></td></tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr><th width="35%">Program Studi</th><td>: <?= e($mhs['nama_prodi']); ?></td></tr>
                                        <tr><th>Fakultas</th><td>: <?= e($mhs['nama_fakultas']); ?></td></tr>
                                        <tr><th>Tanggal Masuk</th><td>: <?= date('d-m-Y', strtotime($mhs['tanggal_masuk'])); ?></td></tr>
                                        <tr><th>Alamat</th><td>: <?= e($mhs['alamat']); ?></td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            <!-- TAMPILAN DASHBOARD OPERATOR & ADMIN -->
            <?php else: ?>
                <?php
                $c1 = ambil_satu("SELECT COUNT(*) AS t FROM mahasiswa");
                $c2 = ambil_satu("SELECT COUNT(*) AS t FROM prodi");
                $c3 = ambil_satu("SELECT COUNT(*) AS t FROM users");
                ?>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card bg-primary text-white p-3 shadow-sm">
                            <h5>Total Mahasiswa</h5>
                            <h2 class="fw-bold"><?= (int)$c1['t']; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white p-3 shadow-sm">
                            <h5>Total Program Studi</h5>
                            <h2 class="fw-bold"><?= (int)$c2['t']; ?></h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-dark text-white p-3 shadow-sm">
                            <h5>User Staf / Admin</h5>
                            <h2 class="fw-bold"><?= (int)$c3['t']; ?></h2>
                        </div>
                    </div>
                </div>

                <!-- Tabel Laporan Mahasiswa Per Program Studi -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-list me-2"></i> Daftar Mahasiswa Per Program Studi</span>
                        <button class="btn btn-sm btn-outline-primary no-print" onclick="window.print()"><i class="fa-solid fa-print me-1"></i> Cetak Laporan</button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>No.</th>
                                        <th>NPM</th>
                                        <th>Nama Mahasiswa</th>
                                        <th>JK</th>
                                        <th>Tempat/Tgl Lahir</th>
                                        <th>Program Studi</th>
                                        <th>Fakultas</th>
                                        <th>Alamat</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    $semua = ambil_semua(
                                        "SELECT m.*, p.nama_prodi, f.nama_fakultas
                                         FROM mahasiswa m
                                         JOIN prodi p ON m.id_prodi = p.id_prodi
                                         JOIN fakultas f ON p.id_fakultas = f.id_fakultas
                                         ORDER BY p.nama_prodi, m.npm ASC"
                                    );
                                    foreach ($semua as $r):
                                    ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= e($r['npm']); ?></td>
                                            <td><?= e($r['nama_mahasiswa']); ?></td>
                                            <td><?= e($r['jenis_kelamin']); ?></td>
                                            <td><?= e($r['tempat_lahir']) . ', ' . date('d/m/Y', strtotime($r['tanggal_lahir'])); ?></td>
                                            <td><?= e($r['nama_prodi']); ?></td>
                                            <td><?= e($r['nama_fakultas']); ?></td>
                                            <td><?= e($r['alamat']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (!$semua): ?>
                                        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data mahasiswa.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

<?php require 'footer.php'; ?>
