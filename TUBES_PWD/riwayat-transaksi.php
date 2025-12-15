<?php
session_start();
require_once "config/db.php";
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user   = $_SESSION['user'];
$userId = $user['id'];
$sql = "
    SELECT
        p.id,
        p.warna,
        p.tahun_unit,
        p.metode_pembayaran,
        p.harga_motor,
        p.status        AS status_pesanan,
        p.alamat_pengiriman,
        p.catatan,

        m.nama_motor,
        m.merek,
        m.foto,

        pay.metode      AS metode_bayar,
        pay.jumlah      AS jumlah_bayar,
        pay.status      AS status_bayar,
        pay.tanggal_bayar

    FROM pesanan p
    JOIN motor m       ON p.motor_id = m.id
    LEFT JOIN pembayaran pay ON pay.order_id = p.id
    WHERE p.user_id = ?
    ORDER BY p.id DESC, pay.tanggal_bayar DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

function badgePesanan($status) {
    switch ($status) {
        case 'selesai':     return 'bg-success';
        case 'dikirim':     return 'bg-info text-dark';
        case 'proses':      return 'bg-warning text-dark';
        case 'dibatalkan':
        case 'batal':       return 'bg-danger';
        case 'pending':
        default:            return 'bg-secondary';
    }
}

function badgeBayar($status) {
    switch ($status) {
        case 'berhasil':    return 'bg-success';
        case 'pending':     return 'bg-warning text-dark';
        case 'gagal':       return 'bg-danger';
        default:            return 'bg-secondary';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - MotorKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="dashboard.php">
            <i class="bi bi-motorcycle"></i> MotorKu
        </a>

        <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">

                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
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

                <li class="nav-item">
                    <a class="nav-link active" href="riwayat-pesanan.php">
                        <i class="bi bi-clock-history"></i> Riwayat Pesanan
                    </a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                        <?= htmlspecialchars($user['nama_lengkap'] ?? $user['username'] ?? 'User') ?>
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
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="bi bi-clock-history"></i> Riwayat Pesanan</h3>
            <p class="text-muted mb-0">Semua pesanan dan status pembayarannya yang pernah kamu buat.</p>
        </div>
        <a href="katalog.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Buat Pesanan Baru
        </a>
    </div>

    <?php if (empty($rows)): ?>

        <div class="alert alert-info text-center py-4">
            <i class="bi bi-info-circle"></i> Belum ada riwayat pesanan.
            <br>
            <span class="small text-muted">Sepertinya kamu belum pernah jajan motor di sini.</span>
        </div>

    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#Order</th>
                        <th>Motor</th>
                        <th>Metode</th>
                        <th>Harga</th>
                        <th>Status Pesanan</th>
                        <th>Status Pembayaran</th>
                        <th>Tanggal Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $r): ?>
                    <tr>
                        <td>
                            <span class="fw-semibold">#<?= htmlspecialchars($r['id']) ?></span><br>
                            <small class="text-muted">
                                <?= htmlspecialchars($r['tahun_unit']) ?> · warna <?= htmlspecialchars($r['warna']) ?>
                            </small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <?php if (!empty($r['foto'])): ?>
                                    <img src="uploads/motor/<?= htmlspecialchars($r['foto']) ?>"
                                         alt="motor"
                                         class="me-2"
                                         style="width: 48px; height: 32px; object-fit: cover; border-radius: 4px;">
                                <?php endif; ?>
                                <div>
                                    <div class="fw-semibold"><?= htmlspecialchars($r['nama_motor']) ?></div>
                                    <small class="text-muted"><?= htmlspecialchars($r['merek']) ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?= htmlspecialchars(ucfirst($r['metode_pembayaran'])) ?><br>
                            <?php if (!empty($r['metode_bayar'])): ?>
                                <small class="text-muted">Bayar: <?= htmlspecialchars($r['metode_bayar']) ?></small>
                            <?php else: ?>
                                <small class="text-muted">Belum ada pembayaran</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            Rp <?= number_format($r['harga_motor'], 0, ',', '.') ?><br>
                            <?php if (!empty($r['jumlah_bayar'])): ?>
                                <small class="text-muted">
                                    Dibayar: Rp <?= number_format($r['jumlah_bayar'], 0, ',', '.') ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?= badgePesanan($r['status_pesanan']) ?>">
                                <?= strtoupper($r['status_pesanan']) ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($r['status_bayar'])): ?>
                                <span class="badge <?= badgeBayar($r['status_bayar']) ?>">
                                    <?= strtoupper($r['status_bayar']) ?>
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">BELUM BAYAR</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($r['tanggal_bayar'])): ?>
                                <?= date('d-m-Y H:i', strtotime($r['tanggal_bayar'])) ?>
                            <?php else: ?>
                                <span class="text-muted small">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</div>

<footer class="bg-dark text-white py-3 mt-5 text-center">
    &copy; 2024 MotorKu. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>