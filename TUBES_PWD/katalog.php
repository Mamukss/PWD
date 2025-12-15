<?php
session_start();
require_once __DIR__ . '/config/db.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
try {
    $stmt = $pdo->query("SELECT * FROM motor ORDER BY nama_motor");
    $motor = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Gagal mengambil data motor: " . $e->getMessage());
}
$user = $_SESSION['user'];
$namaUser = htmlspecialchars($user['nama_lengkap'] ?? $user['nama'] ?? $user['name'] ?? $user['username'] ?? 'User');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Motor - MotorKu</title>
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
    <section class="bg-primary text-white py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h1 class="display-4 fw-bold">Katalog Motor</h1>
                    <p class="lead">Temukan motor impian Anda dari koleksi kami yang lengkap</p>
                </div>
            </div>
        </div>
    </section>

    


    <section class="py-5">
        <div class="container">
            <div class="row g-4" id="katalogList">
                <?php if (!$motor): ?>
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            Belum ada data motor di sistem.
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($motor as $m): ?>
                        <?php
                            $nama  = $m['nama_motor'] ?? 'Motor';
                            $merek = $m['merek'] ?? 'Merek';
                            $tahun = $m['tahun'] ?? '';
                            $harga = $m['harga'] ?? 0;
                            $stok  = (int)($m['stok'] ?? 0);
                            $fotoFile = !empty($m['foto'] ?? '') 
                                ? 'uploads/motor/' . $m['foto']
                                : redirectWithMessage('error', 'Foto motor wajib diupload.');
                        ?>
                        <div class="col-md-6 col-lg-4 katalog-item" 
                             data-name="<?= htmlspecialchars($nama) ?>" 
                             data-brand="<?= htmlspecialchars($merek) ?>" 
                             data-price="<?= (int)$harga ?>">
                            <div class="card h-100 shadow-sm">
                                <img src="<?= htmlspecialchars($fotoFile) ?>" 
                                     class="card-img-top" 
                                     alt="<?= htmlspecialchars($nama) ?>"
                                     style="height: 200px; object-fit: cover;">
                                <div class="card-body d-flex flex-column">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h5 class="card-title mb-1"><?= htmlspecialchars($nama) ?></h5>
                                            <p class="text-muted small mb-1">
                                                <i class="bi bi-building"></i> <?= htmlspecialchars($merek) ?>
                                                <?php if ($tahun): ?>
                                                    &middot; <i class="bi bi-calendar"></i> <?= htmlspecialchars($tahun) ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <span class="badge <?= $stok > 0 ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $stok > 0 ? 'Stok ' . $stok : 'Habis' ?>
                                        </span>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Harga</small>
                                        <strong class="text-primary fs-5">Rp <?= number_format($harga, 0, ',', '.') ?></strong>
                                    </div>

                                    <div class="mt-auto d-flex gap-2">

                                        <?php if (isset($_SESSION['user']) && $stok > 0): ?>
                                            <a href="buat-pesanan.php?motor_id=<?= $m['id'] ?>" class="btn btn-primary btn-sm flex-grow-1">
                                                <i class="bi bi-cart"></i> Pesan
                                            </a>
                                        <?php elseif (!isset($_SESSION['user'])): ?>
                                            <a href="login.php" class="btn btn-primary btn-sm flex-grow-1">
                                                <i class="bi bi-box-arrow-in-right"></i> Login untuk pesan
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary btn-sm flex-grow-1" disabled>
                                                Stok Habis
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            
        </div>
    </section>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>

        