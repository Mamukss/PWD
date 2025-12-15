<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$admin = $_SESSION['user'];
$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status  = $_POST['status'] ?? '';

    $allowedStatus = ['pending', 'proses', 'dikirim', 'selesai', 'dibatalkan', 'batal'];

    if ($orderId <= 0 || !in_array($status, $allowedStatus, true)) {
        $error = "Data tidak valid.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE pesanan SET status = ? WHERE id = ?");
            $stmt->execute([$status, $orderId]);
            $success = "Status pesanan #{$orderId} berhasil diperbarui.";
        } catch (PDOException $e) {
            $error = "Gagal memperbarui status pesanan.";
        }
    }
}

$filterStatus = $_GET['status'] ?? '';
$where = '';
$params = [];

if ($filterStatus !== '' && $filterStatus !== 'all') {
    $where = "WHERE p.status = ?";
    $params[] = $filterStatus;
}

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

        u.nama_lengkap,
        u.email,

        m.nama_motor,
        m.merek,

        pay.metode      AS metode_bayar,
        pay.jumlah      AS jumlah_bayar,
        pay.status      AS status_bayar,
        pay.tanggal_bayar

    FROM pesanan p
    JOIN users u  ON p.user_id = u.id
    JOIN motor m  ON p.motor_id = m.id
    LEFT JOIN pembayaran pay ON pay.order_id = p.id
    $where
    ORDER BY p.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

function badgePesanan($s) {
    return [
        'pending'     => 'bg-secondary',
        'proses'      => 'bg-info text-dark',
        'dikirim'     => 'bg-primary',
        'selesai'     => 'bg-success',
        'dibatalkan'  => 'bg-danger',
        'batal'       => 'bg-danger',
    ][$s] ?? 'bg-dark';
}

function badgeBayar($s) {
    return [
        'berhasil' => 'bg-success',
        'pending'  => 'bg-warning text-dark',
        'gagal'    => 'bg-danger',
    ][$s] ?? 'bg-secondary';
}

function rupiah($n) {
    return "Rp " . number_format((int)$n, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - MotorKu Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<div class="d-flex">

    <div class="dashboard-sidebar">
        <div class="text-center mb-4">
            <h4><i class="bi bi-shield-check"></i> Admin Panel</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a class="nav-link" href="admin-dashboard.php">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link" href="admin-users.php">
                    <i class="bi bi-people"></i> Kelola Users
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link" href="admin-motor.php">
                    <i class="bi bi-motorcycle"></i> Kelola Motor
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link active bg-primary text-white rounded" href="admin-pesanan.php">
                    <i class="bi bi-bag"></i> Kelola Pesanan
                </a>
            </li>
            <li class="nav-item mt-4">
                <a class="nav-link text-danger" href="../process/logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="flex-grow-1">

        <nav class="navbar navbar-dark bg-primary">
            <div class="container-fluid">
                <span class="navbar-brand">
                    <i class="bi bi-bag"></i> Kelola Pesanan
                </span>
                <span class="text-white">
                    <i class="bi bi-person-circle"></i>
                    <?= htmlspecialchars($admin['nama_lengkap'] ?? $admin['username'] ?? 'Admin') ?>
                </span>
            </div>
        </nav>

        <div class="dashboard-content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Data Pesanan</h3>

                <form method="GET" class="d-flex gap-2">
                    <select name="status" class="form-select form-select-sm">
                        <option value="all" <?= $filterStatus === 'all' || $filterStatus === '' ? 'selected' : '' ?>>Semua Status</option>
                        <option value="pending"    <?= $filterStatus === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="proses"     <?= $filterStatus === 'proses' ? 'selected' : '' ?>>Proses</option>
                        <option value="dikirim"    <?= $filterStatus === 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                        <option value="selesai"    <?= $filterStatus === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        <option value="dibatalkan" <?= $filterStatus === 'dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                    </select>
                    <button class="btn btn-sm btn-outline-light">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </form>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Daftar Pesanan</h5>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#Order</th>
                                    <th>Customer</th>
                                    <th>Motor</th>
                                    <th>Metode & Harga</th>
                                    <th>Status Pesanan</th>
                                    <th>Status Bayar</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Tidak ada pesanan.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($orders as $o): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= $o['id'] ?></strong><br>
                                            <small class="text-muted">
                                                <?= htmlspecialchars($o['tahun_unit']) ?> · warna <?= htmlspecialchars($o['warna']) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($o['nama_lengkap']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($o['email']) ?></small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($o['nama_motor']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($o['merek']) ?></small>
                                        </td>
                                        <td>
                                            <div>Metode: <?= htmlspecialchars(ucfirst($o['metode_pembayaran'])) ?></div>
                                            <div>Harga: <?= rupiah($o['harga_motor']) ?></div>
                                            <?php if (!empty($o['jumlah_bayar'])): ?>
                                                <small class="text-muted">
                                                    Dibayar: <?= rupiah($o['jumlah_bayar']) ?> (<?= htmlspecialchars($o['metode_bayar']) ?>)
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-flex flex-column gap-1">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">

                                                <select name="status" class="form-select form-select-sm mb-1">
                                                    <?php
                                                    $statuses = ['pending','proses','dikirim','selesai','dibatalkan'];
                                                    foreach ($statuses as $st):
                                                    ?>
                                                        <option value="<?= $st ?>" <?= $o['status_pesanan'] === $st ? 'selected' : '' ?>>
                                                            <?= ucfirst($st) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>

                                                <button class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-save"></i> Simpan
                                                </button>

                                                <span class="badge mt-1 <?= badgePesanan($o['status_pesanan']) ?>">
                                                    <?= strtoupper($o['status_pesanan']) ?>
                                                </span>
                                            </form>
                                        </td>
                                        <td>
                                            <?php if (!empty($o['status_bayar'])): ?>
                                                <span class="badge <?= badgeBayar($o['status_bayar']) ?>">
                                                    <?= strtoupper($o['status_bayar']) ?>
                                                </span><br>
                                                <?php if (!empty($o['tanggal_bayar'])): ?>
                                                    <small class="text-muted">
                                                        <?= date("d M Y", strtotime($o['tanggal_bayar'])) ?>
                                                    </small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">BELUM BAYAR</span>
                                            <?php endif; ?>
                                        </td>
                                        
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
