<?php
session_start();
require_once "../config/db.php";


if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin = $_SESSION['user'];

$totalUsers      = 0;
$totalMotor      = 0;
$totalPesanan    = 0;
$totalPendapatan = 0;
$recentOrders    = [];

try {
    $totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

    $totalMotor = (int)$pdo->query("SELECT COUNT(*) FROM motor")->fetchColumn();

    $totalPesanan = (int)$pdo->query("SELECT COUNT(*) FROM pesanan")->fetchColumn();

    $stmt = $pdo->query("SELECT COALESCE(SUM(jumlah),0) FROM pembayaran WHERE status = 'berhasil'");
    $totalPendapatan = (int)$stmt->fetchColumn();

    $sqlRecent = "
        SELECT 
            p.id,
            p.harga_motor,
            p.status,
            u.nama_lengkap,
            m.nama_motor
        FROM pesanan p
        JOIN users u  ON p.user_id = u.id
        JOIN motor m  ON p.motor_id = m.id
        ORDER BY p.id DESC
        LIMIT 5
    ";
    $stmt = $pdo->query($sqlRecent);
    $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $recentOrders = [];
}

function badgeStatus($status) {
    switch ($status) {
        case 'pending':     return 'bg-warning';
        case 'proses':      return 'bg-info';
        case 'dikirim':     return 'bg-primary';
        case 'selesai':     return 'bg-success';
        case 'dibatalkan':
        case 'batal':       return 'bg-danger';
        default:            return 'bg-secondary';
    }
}

function formatRupiah($n) {
    return 'Rp ' . number_format($n, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MotorKu</title>
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
                    <a class="nav-link active bg-primary text-white rounded" href="admin-dashboard.php">
                        <i class="bi bi-speedometer2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-dark" href="admin-users.php">
                        <i class="bi bi-people"></i> Kelola Users
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-dark" href="admin-motor.php">
                        <i class="bi bi-motorcycle"></i> Kelola Motor
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a class="nav-link text-dark" href="admin-pesanan.php">
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

            <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">
                        <i class="bi bi-motorcycle"></i> MotorKu Admin
                    </a>
                    <div class="ms-auto">
                        <span class="text-white me-3">
                            <i class="bi bi-person-circle"></i>
                            <?= htmlspecialchars($admin['nama_lengkap'] ?? $admin['username'] ?? 'Admin') ?>
                        </span>
                    </div>
                </div>
            </nav>


            <div class="dashboard-content">
                <h2 class="mb-4">Dashboard Admin</h2>


                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1">Total Users</p>
                                    <h3 class="mb-0"><?= $totalUsers ?></h3>
                                </div>
                                <i class="bi bi-people" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1">Total Motor</p>
                                    <h3 class="mb-0"><?= $totalMotor ?></h3>
                                </div>
                                <i class="bi bi-motorcycle" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1">Total Pesanan</p>
                                    <h3 class="mb-0"><?= $totalPesanan ?></h3>
                                </div>
                                <i class="bi bi-bag-check" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <p class="mb-1">Pendapatan</p>
                                    <h3 class="mb-0"><?= formatRupiah($totalPendapatan) ?></h3>
                                </div>
                                <i class="bi bi-graph-up" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="row g-4">
                    <div class="container text-center">
                        <div class="card dashboard-card">
                            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0"><i class="bi bi-clock-history"></i> Pesanan Terbaru</h5>
                                <a href="admin-pesanan.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Customer</th>
                                                <th>Motor</th>
                                                <th>Total</th>
                                                <th>Status</th>
                                                <th>Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php if (empty($recentOrders)): ?>
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">
                                                        Belum ada pesanan.
                                                    </td>
                                                </tr>
                                            <?php else: ?>
                                                <?php foreach ($recentOrders as $ord): ?>
                                                    <tr>
                                                        <td>
                                                            <strong>#ORD-<?= str_pad($ord['id'], 3, '0', STR_PAD_LEFT) ?></strong>
                                                        </td>
                                                        <td><?= htmlspecialchars($ord['nama_lengkap']) ?></td>
                                                        <td><?= htmlspecialchars($ord['nama_motor']) ?></td>
                                                        <td><?= formatRupiah($ord['harga_motor']) ?></td>
                                                        <td>
                                                            <span class="badge <?= badgeStatus($ord['status']) ?>">
                                                                <?= strtoupper($ord['status']) ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="admin-pesanan.php?view=<?= $ord['id'] ?>" 
                                                               class="btn btn-sm btn-info">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
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
                    <div class="col-12">
                        <div class="card dashboard-card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="bi bi-trophy"></i> Motor Terlaris</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="card border">
                                            <img src="https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=400" 
                                                class="card-img-top" alt="Motor">
                                            <div class="card-body">
                                                <h6 class="card-title">Honda CBR150R</h6>
                                                <p class="text-muted mb-2">45 Unit Terjual</p>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-primary fw-bold">Rp 35.000.000</span>
                                                    <span class="badge bg-success">Top 1</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border">
                                            <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=400" 
                                                class="card-img-top" alt="Motor">
                                            <div class="card-body">
                                                <h6 class="card-title">Yamaha NMAX</h6>
                                                <p class="text-muted mb-2">38 Unit Terjual</p>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-primary fw-bold">Rp 28.000.000</span>
                                                    <span class="badge bg-info">Top 2</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border">
                                            <img src="https://th.bing.com/th/id/OIP.szhJra2E4VgrqOc257LDZQHaEZ?w=298&h=180&c=7&r=0&o=7&cb=ucfimg2&pid=1.7&rm=3&ucfimg=1&w=400" 
                                                class="card-img-top" alt="Motor">
                                            <div class="card-body">
                                                <h6 class="card-title">Honda Supra X</h6>
                                                <p class="text-muted mb-2">32 Unit Terjual</p>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-primary fw-bold">Rp 18.500.000</span>
                                                    <span class="badge bg-warning">Top 3</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>