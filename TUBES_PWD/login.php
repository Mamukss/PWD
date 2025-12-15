<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/config/db.php';
if (isset($_SESSION['user'])) {
    if (isset($_SESSION['user'])) {
        $role = $_SESSION['user']['role'] ?? 'user';

        if ($role === 'admin') {
            header("Location: admin/admin-dashboard.php");
        } else {
            header("Location: dashboard.php");
        }
        exit;
    }
}

$showError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $showError = true;
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id'           => $user['id'],
                    'nama_lengkap' => $user['nama_lengkap'],
                    'username'     => $user['username'],    
                    'email'        => $user['email'],
                    'role'         => $user['role'],
                    'avatar'       => $user['avatar'] ?? null,
                ];
                if ($user['role'] === 'admin') {
                    header("Location: admin/admin-dashboard.php"); 
                    exit;
                } else {
                    header("Location: dashboard.php");
                    exit;
                }
            } else {
                $showError = true;
            }

        } catch (PDOException $e) {
            $showError = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MotorKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    
<div class="auth-container">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="auth-card">

                    <div class="auth-header">
                        <h2><i class="bi bi-motorcycle"></i> MotorKu</h2>
                        <p>Login ke Akun Anda</p>
                    </div>

                    <div class="auth-body">
                        <div class="alert alert-danger <?= $showError ? '' : 'd-none' ?>" id="alertError">
                            <i class="bi bi-exclamation-triangle"></i> Username atau password salah!
                        </div>

                        <form action="" method="POST" id="loginForm">
                            
                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-person"></i> Username</label>
                                <input type="text" class="form-control"
                                       name="username"
                                       required
                                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label"><i class="bi bi-lock"></i> Password</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-box-arrow-in-right"></i> Login
                                </button>
                            </div>

                        </form>

                        <hr>

                        <div class="text-center">
                            Belum punya akun?
                            <a href="register.php" class="text-primary fw-bold">Daftar di sini</a>
                        </div>

                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="index.php" class="text-white"><i class="bi bi-arrow-left"></i> Kembali ke Beranda</a>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="js/main.js"></script>
</body>
</html>