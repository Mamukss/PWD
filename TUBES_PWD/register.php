<?php
session_start();
require_once __DIR__ . '/config/db.php';

$errors  = [];
$success = "";
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama_lengkap'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $username  = trim($_POST['username'] ?? '');
    $no_telp   = trim($_POST['no_telp'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['confirm_password'] ?? '';
    if ($nama === '' || $email === '' || $username === '' || $no_telp === '' || $alamat === '' || $password === '' || $password2 === '') {
        $errors[] = 'Semua field wajib diisi.';
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid.';
    }

    if ($password !== '' && strlen($password) < 8) {
        $errors[] = 'Password minimal 8 karakter.';
    }

    if ($password !== '' && $password2 !== '' && $password !== $password2) {
        $errors[] = 'Konfirmasi password tidak sesuai.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $errors[] = 'Username atau email sudah digunakan.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (nama_lengkap, email, username, password_hash, alamat, no_telp, role)
                VALUES (?, ?, ?, ?, ?, ?, 'user')
            ");
            $stmt->execute([$nama, $email, $username, $hash, $alamat, $no_telp]);

            $success = 'Registrasi berhasil. Silakan login.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - MotorKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
    <script src="js/main.js"></script>
</head>
<body>
    <div class="auth-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="auth-card">
                        <div class="auth-header">
                            <h2><i class="bi bi-motorcycle"></i> MotorKu</h2>
                            <p class="mb-0">Daftar Akun Baru</p>
                        </div>
                        <div class="auth-body">

                            <?php if ($errors): ?>
                                <div class="alert alert-danger">
                                    <?php foreach ($errors as $e): ?>
                                        <p class="mb-0"><?= htmlspecialchars($e) ?></p>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    <?= htmlspecialchars($success) ?>
                                </div>
                            <?php endif; ?>

                            <form action="" method="POST" id="registerForm">
                                <div class="mb-3">
                                    <label for="nama_lengkap" class="form-label">
                                        <i class="bi bi-person"></i> Nama Lengkap
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="nama_lengkap" 
                                        name="nama_lengkap" 
                                        required
                                        value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">
                                        <i class="bi bi-envelope"></i> Email
                                    </label>
                                    <input 
                                        type="email" 
                                        class="form-control" 
                                        id="email" 
                                        name="email" 
                                        required
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    <small class="text-muted">Email digunakan untuk data kontak pelanggan.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">
                                        <i class="bi bi-person-badge"></i> Username
                                    </label>
                                    <input 
                                        type="text" 
                                        class="form-control" 
                                        id="username" 
                                        name="username" 
                                        required
                                        value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="no_telp" class="form-label">
                                        <i class="bi bi-telephone"></i> No. Telepon
                                    </label>
                                    <input 
                                        type="tel" 
                                        class="form-control" 
                                        id="no_telp" 
                                        name="no_telp" 
                                        required
                                        value="<?= htmlspecialchars($_POST['no_telp'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">
                                        <i class="bi bi-geo-alt"></i> Alamat
                                    </label>
                                    <textarea 
                                        class="form-control" 
                                        id="alamat" 
                                        name="alamat" 
                                        rows="3" 
                                        required><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">
                                        <i class="bi bi-lock"></i> Password
                                    </label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="password" 
                                        name="password" 
                                        required>
                                    <small class="text-muted">Minimal 8 karakter</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="confirm_password" class="form-label">
                                        <i class="bi bi-lock-fill"></i> Konfirmasi Password
                                    </label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="confirm_password" 
                                        name="confirm_password" 
                                        required>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="terms" required>
                                    <label class="form-check-label" for="terms">
                                        Saya menyetujui <a href="#" class="text-primary">syarat dan ketentuan</a>
                                    </label>
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="bi bi-person-plus"></i> Daftar
                                    </button>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <p class="mb-0">
                                    Sudah punya akun?
                                    <a href="login.php" class="text-primary fw-bold">Login di sini</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php" class="text-white">
                            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

 
</body>
</html>