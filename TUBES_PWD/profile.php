<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$userSession = $_SESSION['user'];
$userId = $userSession['id'];

$success = '';
$error = '';
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email        = trim($_POST['email']);
    $username     = trim($_POST['username']);
    $alamat       = trim($_POST['alamat']);
    $no_telp      = trim($_POST['no_telp']);

    if ($nama_lengkap === '' || $email === '' || $username === '') {
        $error = "Nama, email dan username wajib diisi.";
    } else {
        try {
            $check = $pdo->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $check->execute([$email, $username, $userId]);

            if ($check->rowCount() > 0) {
                $error = "Email atau username sudah digunakan.";
            } else {
                $avatarName = $user['avatar'];

                if (!empty($_FILES['avatar']['name'])) {

                    $fileTmp = $_FILES['avatar']['tmp_name'];
                    $fileName = time() . "_" . basename($_FILES['avatar']['name']);
                    $targetPath = "uploads/avatar/" . $fileName;
                    if (!file_exists("uploads/avatar")) {
                        mkdir("uploads/avatar", 0777, true);
                    }

                    move_uploaded_file($fileTmp, $targetPath);
                    if (!empty($user['avatar']) && file_exists("uploads/avatar/".$user['avatar'])) {
                        unlink("uploads/avatar/".$user['avatar']);
                    }

                    $avatarName = $fileName;
                }
                $update = $pdo->prepare("
                    UPDATE users
                    SET nama_lengkap = ?, email = ?, username = ?, alamat = ?, no_telp = ?, avatar = ?
                    WHERE id = ?
                ");
                $update->execute([$nama_lengkap, $email, $username, $alamat, $no_telp, $avatarName, $userId]);
                $_SESSION['user']['nama_lengkap'] = $nama_lengkap;
                $_SESSION['user']['email']        = $email;
                $_SESSION['user']['username']     = $username;
                $_SESSION['user']['avatar']       = $avatarName;

                $success = "Profil berhasil diperbarui!";
            }

        } catch (PDOException $e) {
            $error = "Terjadi kesalahan server.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - MotorKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
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
                    <a class="nav-link" href="pesanan-saya.php"><i class="bi bi-bag"></i> Pesanan Saya</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link active" href="profile.php"><i class="bi bi-person-circle"></i> Profil</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link text-danger" href="process/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>

            </ul>
        </div>
    </div>
</nav>
<div class="container py-5">

    <h3 class="mb-4"><i class="bi bi-person"></i> Profil Saya</h3>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">

        <div class="text-center mb-4">
            <?php if (!empty($user['avatar'])): ?>
                <img src="uploads/avatar/<?= htmlspecialchars($user['avatar']) ?>" 
                     class="rounded-circle mb-2" width="120" height="120" style="object-fit: cover;">
            <?php else: ?>
                <img src="https://via.placeholder.com/120?text=No+Avatar" 
                     class="rounded-circle mb-2" width="120" height="120">
            <?php endif; ?>
            <div>
                <input type="file" name="avatar" class="form-control mt-2" accept="image/*">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Alamat</label>
            <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($user['alamat']) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">No. Telepon</label>
            <input type="text" name="no_telp" class="form-control" value="<?= htmlspecialchars($user['no_telp']) ?>">
        </div>

        <button class="btn btn-primary w-100">Simpan Perubahan</button>

    </form>

</div>


<footer class="bg-dark text-white py-3 text-center mt-5">
    &copy; 2024 MotorKu. All rights reserved.
</footer>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>