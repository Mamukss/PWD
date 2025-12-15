<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotorKu - Dealer Motor Terpercaya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="bi bi-motorcycle"></i> MotorKu
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="katalog.php">Katalog Motor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="tentang.html">Tentang Kami</a>
                    </li>
                    <?php if (!isset($_SESSION["user_id"])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-warning text-dark ms-2" href="register.php">Daftar</a>
                        </li>

                    <?php else: ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION["username"]) ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
                                <li><a class="dropdown-item" href="pesanan-saya.php"><i class="bi bi-bag"></i> Pesanan Saya</a></li>
                                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="logout.php">
                                    <i class="bi bi-box-arrow-right"></i> Logout</a>
                                </li>
                            </ul>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-100">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">Temukan Motor Impian Anda</h1>
                    <p class="lead text-white mb-4">Dealer motor terpercaya dengan berbagai pilihan motor berkualitas dan harga terjangkau.</p>
                    <div class="d-flex gap-3">
                        <a href="katalog.php" class="btn btn-warning btn-lg">Lihat Katalog</a>
                        <?php if (!isset($_SESSION["user_id"])): ?>
                            <a href="register.php" class="btn btn-outline-light btn-lg">Daftar Sekarang</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-outline-light btn-lg">Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <img src="https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=600" alt="Motor" class="img-fluid rounded shadow-lg">
                </div>
            </div>
        </div>
    </section>
    <section class="py-5">
        <div class="container">
            <h2 class="text-center mb-5">Mengapa Memilih MotorKu?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow">
                        <div class="card-body">
                            <i class="bi bi-shield-check text-primary" style="font-size: 3rem;"></i>
                            <h5 class="card-title mt-3">Motor Berkualitas</h5>
                            <p class="card-text">Semua motor telah melalui inspeksi ketat untuk memastikan kualitas terbaik.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow">
                        <div class="card-body">
                            <i class="bi bi-credit-card text-primary" style="font-size: 3rem;"></i>
                            <h5 class="card-title mt-3">Harga Terjangkau</h5>
                            <p class="card-text">Berbagai pilihan pembayaran dengan harga kompetitif dan cicilan ringan.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 text-center border-0 shadow">
                        <div class="card-body">
                            <i class="bi bi-headset text-primary" style="font-size: 3rem;"></i>
                            <h5 class="card-title mt-3">Layanan Terbaik</h5>
                            <p class="card-text">Tim profesional siap membantu Anda menemukan motor yang tepat.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="py-5 bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Motor Populer</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow">
                        <img src="https://images.unsplash.com/photo-1609630875171-b1321377ee65?w=400" class="card-img-top" alt="Motor Sport">
                        <div class="card-body">
                            <span class="badge bg-success mb-2">Sport</span>
                            <h5 class="card-title">Honda CBR150R</h5>
                            <p class="card-text text-muted">Motor sport dengan performa tinggi dan desain aerodinamis.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold fs-5">Rp 35.000.000</span>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow">
                        <img src="https://images.unsplash.com/photo-1568772585407-9361f9bf3a87?w=400" class="card-img-top" alt="Motor Matic">
                        <div class="card-body">
                            <span class="badge bg-info mb-2">Matic</span>
                            <h5 class="card-title">Yamaha NMAX</h5>
                            <p class="card-text text-muted">Motor matic premium dengan fitur lengkap dan nyaman.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold fs-5">Rp 28.000.000</span>
                                
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow">
                        <img src="https://imgcdnblog.carbay.com/wp-content/uploads/2021/07/13171109/Honda-Revo-Fit.jpg" class="card-img-top" alt="Motor Bebek">
                        <div class="card-body">
                            <span class="badge bg-warning mb-2">Bebek</span>
                            <h5 class="card-title">Honda Supra X 125</h5>
                            <p class="card-text text-muted">Motor bebek irit dan tangguh untuk penggunaan harian.</p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-primary fw-bold fs-5">Rp 18.500.000</span>
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <a href="katalog.php" class="btn btn-primary btn-lg">Lihat Semua Motor</a>
            </div>
        </div>
    </section>

    <section class="py-5 bg-primary text-white">
        <div class="container text-center">
            <h2 class="mb-4">Siap Mendapatkan Motor Impian Anda?</h2>
            <p class="lead mb-4">Daftar sekarang dan dapatkan penawaran terbaik dari kami!</p>
            <a href="register.php" class="btn btn-warning btn-lg">Daftar Sekarang</a>
        </div>
    </section>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <div class="bg-dark text-white"> 
                <h5 class="mb-0"><i class="bi bi-question-circle"></i> Bantuan</h5>
                <p class="mb-2">Butuh bantuan terkait pesanan atau pembayaran?</p> 
                    <ul class="list-unstyled mb-3"> <li class="mb-1"><i class="bi bi-chat-dots text-primary"></i> Live chat dengan admin</li> 
                        <li class="mb-1"><i class="bi bi-envelope text-primary"></i> Email: support@motorku.id</li> 
                        <li class="mb-1"><i class="bi bi-telephone text-primary"></i> WA: 0812-xxxx-xxxx</li> 
                    </ul> 
                <i class="bi bi-headset"></i> Hubungi Support </a>
        </div>
    </footer>
</body>
</html>