<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$user     = $_SESSION['user'];
$namaUser = htmlspecialchars($user['nama_lengkap'] ?? $user['name'] ?? 'User');

$errorMsg = "";

$motors = $pdo->query("SELECT id, nama_motor, harga FROM motor ORDER BY nama_motor")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id  = (int)$user['id'];
    $motor_id = $_POST['motor_id'] ?? null;
    $warna    = $_POST['warna'] ?? null;
    $tahun    = $_POST['tahun'] ?? null;          
    $metode   = $_POST['metode_pembayaran'] ?? null;

    $alamat   = $_POST['alamat_pengiriman'] ?? null;
    $kota     = $_POST['kota'] ?? null;
    $kode_pos = $_POST['kode_pos'] ?? null;
    $no_telp  = $_POST['no_telp'] ?? null;       
    $catatan  = $_POST['catatan'] ?? null;

    $alamat_full = trim($alamat);
    if ($kota) {
        $alamat_full .= ", " . $kota;
    }
    if ($kode_pos) {
        $alamat_full .= " " . $kode_pos;
    }

    if (!$motor_id || !$warna || !$tahun || !$metode || !$alamat || !$kota || !$kode_pos || !$no_telp) {
        $errorMsg = "Data pesanan belum lengkap. Mohon isi semua field yang wajib.";
    } else {

        $stmt = $pdo->prepare("SELECT harga FROM motor WHERE id = ?");
        $stmt->execute([$motor_id]);
        $motor = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$motor) {
            $errorMsg = "Motor yang dipilih tidak ditemukan.";
        } else {
            $harga_motor = (int)$motor['harga'];
            $total_harga = $harga_motor; 

            try {
                $pdo->beginTransaction();

                $stmtPesanan = $pdo->prepare("
                    INSERT INTO pesanan 
                    (user_id, motor_id, warna, tahun_unit, metode_pembayaran, 
                     harga_motor, alamat_pengiriman, catatan, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
                ");

                $stmtPesanan->execute([
                    $user_id,
                    $motor_id,
                    $warna,
                    $tahun,
                    $metode,
                    $harga_motor,
                    $alamat_full,
                    $catatan
                ]);

                $order_id = (int)$pdo->lastInsertId();

                $stmtPay = $pdo->prepare("
                    INSERT INTO pembayaran (order_id, metode, jumlah, status)
                    VALUES (?, ?, ?, 'pending')
                ");

                $stmtPay->execute([
                    $order_id,
                    $metode,
                    $total_harga
                ]);

                $stmtStok = $pdo->prepare("
                    UPDATE motor 
                    SET stok = stok - 1 
                    WHERE id = ? AND stok > 0
                ");
                $stmtStok->execute([$motor_id]);

                if ($stmtStok->rowCount() === 0) {
                    throw new PDOException("Stok motor habis saat proses pemesanan.");
                }

                $pdo->commit();

                header("Location: pesanan-saya.php");
                exit;
            } catch (PDOException $e) {
                $pdo->rollBack();
                $errorMsg = "Terjadi kesalahan saat menyimpan pesanan: " . $e->getMessage();
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
    <title>Buat Pesanan - MotorKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet"
      href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
      integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
      crossorigin=""/>

</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.html">
                <i class="bi bi-motorcycle"></i> MotorKu
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.html">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="katalog.php">
                            <i class="bi bi-grid"></i> Katalog
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pesanan-saya.php">
                            <i class="bi bi-bag"></i> Pesanan Saya
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $namaUser; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="process/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">

                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger">
                        <?= htmlspecialchars($errorMsg) ?>
                    </div>
                <?php endif; ?>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <div class="text-center flex-fill">
                                <div class="rounded-circle bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                                    style="width: 40px; height: 40px;">1</div>
                                <p class="small mt-2 mb-0 fw-bold">Pilih Motor</p>
                            </div>
                            <div class="text-center flex-fill">
                                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" 
                                    style="width: 40px; height: 40px;">2</div>
                                <p class="small mt-2 mb-0">Detail Pesanan</p>
                            </div>
                            <div class="text-center flex-fill">
                                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" 
                                    style="width: 40px; height: 40px;">3</div>
                                <p class="small mt-2 mb-0">Konfirmasi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="bi bi-cart-plus"></i> Buat Pesanan Baru</h5>
                    </div>
                    <div class="card-body">

                        <form method="POST" id="formPesanan">

                            <h6 class="mb-3">1. Pilih Motor</h6>
                            <div class="mb-4">
                                <label for="motor_id" class="form-label">Motor <span class="text-danger">*</span></label>
                                <select class="form-select" id="motor_id" name="motor_id" required>
                                    <option value="">-- Pilih Motor --</option>
                                    <?php foreach ($motors as $m): ?>
                                        <option 
                                            value="<?= $m['id'] ?>" 
                                            data-price="<?= $m['harga'] ?>">
                                            <?= htmlspecialchars($m['nama_motor']) ?> - 
                                            Rp <?= number_format($m['harga'], 0, ',', '.') ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="warna" class="form-label">Warna <span class="text-danger">*</span></label>
                                    <select class="form-select" id="warna" name="warna" required>
                                        <option value="">-- Pilih Warna --</option>
                                        <option value="merah">Merah</option>
                                        <option value="hitam">Hitam</option>
                                        <option value="putih">Putih</option>
                                        <option value="biru">Biru</option>
                                        <option value="silver">Silver</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="tahun" class="form-label">Tahun <span class="text-danger">*</span></label>
                                    <select class="form-select" id="tahun" name="tahun" required>
                                        <option value="">-- Pilih Tahun --</option>
                                        <option value="2024">2024</option>
                                        <option value="2023">2023</option>
                                    </select>
                                </div>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">2. Detail Pembayaran</h6>
                            <div class="mb-3">
                                <label for="metode_pembayaran" class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" required>
                                    <option value="">-- Pilih Metode --</option>
                                    <option value="cash">Cash/Tunai</option>
                                    <option value="kredit">Kredit</option>
                                </select>
                            </div>
                            <hr class="my-4">

                            <h6 class="mb-3">3. Lokasi & Pengiriman</h6>
                            <div class="mb-3">
                                <label for="alamat_pengiriman" class="form-label">Alamat Pengiriman <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="alamat_pengiriman" name="alamat_pengiriman" rows="3" required>Jl. Contoh No. 123, Yogyakarta</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kota" class="form-label">Kota <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="kota" name="kota" value="Yogyakarta" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="kode_pos" class="form-label">Kode Pos <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="kode_pos" name="kode_pos" placeholder="55281" required>
                                </div>
                            </div>


                            <div class="mb-3">
                                <label for="no_telp" class="form-label">No. Telepon <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="no_telp" name="no_telp" value="081234567890" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Pin Lokasi di Peta (opsional)</label>
                                <div id="mapLokasi" class="rounded border" style="height: 300px;"></div>
                                <small id="mapInfo" class="text-muted d-block mt-2">
                                    Geser pin untuk menyesuaikan lokasi pengiriman.
                                </small>
                            </div>

                            <hr class="my-4">

                            <h6 class="mb-3">4. Catatan Tambahan (Opsional)</h6>
                            <div class="mb-4">
                                <label for="catatan" class="form-label">Catatan</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3" 
                                        placeholder="Contoh: Mohon dikirim pada hari Sabtu"></textarea>
                            </div>

                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">Ringkasan Pesanan</h6>
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td>Motor:</td>
                                            <td class="text-end" id="summaryMotor">-</td>
                                        </tr>
                                        <tr>
                                            <td>Warna:</td>
                                            <td class="text-end" id="summaryWarna">-</td>
                                        </tr>
                                        <tr>
                                            <td>Metode Pembayaran:</td>
                                            <td class="text-end" id="summaryMetode">-</td>
                                        </tr>
                                        <tr class="border-top">
                                            <td><strong>Total Harga:</strong></td>
                                            <td class="text-end"><strong class="text-primary fs-5" id="summaryTotal">Rp 0</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="form-check my-4">
                                <input class="form-check-input" type="checkbox" id="agree" required>
                                <label class="form-check-label" for="agree">
                                    Saya menyetujui <a href="#" class="text-primary">syarat dan ketentuan</a> pemesanan
                                </label>
                            </div>

                            <div class="d-flex gap-2 justify-content-end">
                                <a href="katalog.php" class="btn btn-secondary">
                                    <i class="bi bi-x-circle"></i> Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-circle"></i> Buat Pesanan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""></script>
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0">&copy; 2024 MotorKu. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
