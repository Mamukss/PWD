<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user   = $_SESSION['user'];
$userId = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $orderId = (int)($_POST['order_id'] ?? 0);

    if ($orderId > 0) {
        if ($action === 'batal') {
            $stmt = $pdo->prepare("UPDATE pesanan SET status = 'dibatalkan' WHERE id = ? AND user_id = ?");
            $stmt->execute([$orderId, $userId]);
            $stmtPay = $pdo->prepare("UPDATE pembayaran SET status = 'gagal' WHERE order_id = ?");
            $stmtPay->execute([$orderId]);

        } elseif ($action === 'lunas') {
            $stmt = $pdo->prepare("UPDATE pesanan SET status = 'selesai' WHERE id = ? AND user_id = ?");
            $stmt->execute([$orderId, $userId]);
            $stmtPay = $pdo->prepare("UPDATE pembayaran SET status = 'berhasil' WHERE order_id = ?");
            $stmtPay->execute([$orderId]);
        }
    }
    header("Location: pesanan-saya.php");
    exit;
}

$sql = "
    SELECT 
        p.id,
        p.warna,
        p.tahun_unit,
        p.metode_pembayaran,
        p.harga_motor,
        p.alamat_pengiriman,
        p.catatan,
        p.status,
        m.nama_motor,
        m.merek,
        m.foto,
        m.harga AS harga_asli
    FROM pesanan p
    JOIN motor m ON p.motor_id = m.id
    WHERE p.user_id = ?
    ORDER BY p.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$pesanan = $stmt->fetchAll(PDO::FETCH_ASSOC);

function badgeClass($status) {
    switch ($status) {
        case 'selesai': return 'bg-success';
        case 'proses': return 'bg-warning text-dark';
        case 'dikirim': return 'bg-info text-dark';
        case 'batal':
        case 'dibatalkan': return 'bg-danger';
        default: return 'bg-secondary';
    }
}

function statusLabel($status) {
    return strtoupper($status);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - MotorKu</title>
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
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="katalog.php"><i class="bi bi-grid"></i> Katalog</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="pesanan-saya.php"><i class="bi bi-bag"></i> Pesanan Saya</a>
                </li>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['nama_lengkap']) ?>
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
        <h3 class="mb-0"><i class="bi bi-bag"></i> Pesanan Saya</h3>
        <a href="katalog.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Buat Pesanan Baru
        </a>
    </div>

    <?php if (empty($pesanan)) : ?>

        <div class="alert alert-info text-center py-4">
            <i class="bi bi-info-circle"></i> Kamu belum punya pesanan.
            <br><br>
            <a href="katalog.php" class="btn btn-primary">Mulai Pesan Motor</a>
        </div>

    <?php else: ?>

        <div class="row g-4">

            <?php foreach ($pesanan as $p): ?>
            <div class="col-md-6">
                <div class="card shadow-sm h-100">

                    <?php if (!empty($p['foto'])): ?>
                        <img src="uploads/motor/<?= htmlspecialchars($p['foto']) ?>" 
                             class="card-img-top" 
                             style="height: 180px; object-fit: cover;">
                    <?php endif; ?>

                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="card-title mb-1"><?= htmlspecialchars($p['nama_motor']) ?></h5>
                                <p class="text-muted small mb-1">
                                    <?= htmlspecialchars($p['merek']) ?> · 
                                    <?= htmlspecialchars($p['tahun_unit']) ?> · 
                                    Warna: <?= htmlspecialchars(ucfirst($p['warna'])) ?>
                                </p>
                            </div>

                            <span class="badge <?= badgeClass($p['status']) ?>">
                                <?= statusLabel($p['status']) ?>
                            </span>
                        </div>

                        <p class="mb-1"><strong>Metode:</strong> <?= ucfirst($p['metode_pembayaran']) ?></p>
                        <p class="mb-2"><strong>Harga:</strong> Rp <?= number_format($p['harga_motor'], 0, ',', '.') ?></p>

                        <p class="small mb-1"><strong>Alamat:</strong><br>
                            <?= nl2br(htmlspecialchars(mb_strimwidth($p['alamat_pengiriman'], 0, 120, '...'))) ?>
                        </p>

                        <?php if (!empty($p['catatan'])): ?>
                            <p class="small"><strong>Catatan:</strong><br>
                                <?= nl2br(htmlspecialchars(mb_strimwidth($p['catatan'], 0, 120, '...'))) ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!in_array($p['status'], ['dibatalkan', 'batal', 'selesai'])): ?>
                            <form method="post" class="mt-3 d-flex gap-2">
                                <input type="hidden" name="order_id" value="<?= (int)$p['id'] ?>">

                                <button type="submit"
                                        name="action"
                                        value="batal"
                                        class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Yakin ingin membatalkan pesanan ini?');">
                                    <i class="bi bi-x-circle"></i> Batalkan
                                </button>

                                <button type="submit"
                                        name="action"
                                        value="lunas"
                                        class="btn btn-success btn-sm">
                                    <i class="bi bi-check-circle"></i> Tandai Lunas
                                </button>
                            </form>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer class="bg-dark text-white py-3 mt-5 text-center">
    &copy; 2024 MotorKu. All rights reserved.
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
