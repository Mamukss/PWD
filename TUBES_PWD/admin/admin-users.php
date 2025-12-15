<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] !== 'admin')) {
    header("Location: ../login.php");
    exit;
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "create") {
        $nama = $_POST["nama"];
        $email = $_POST["email"];
        $username = $_POST["username"];
        $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
        $role = $_POST["role"];

        $stmt = $pdo->prepare("INSERT INTO users (nama_lengkap, email, username, password_hash, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $username, $password, $role]);

        header("Location: admin-users.php");
        exit;
    }

    if ($action === "update") {
        $id = $_POST["id"];
        $nama = $_POST["nama"];
        $email = $_POST["email"];
        $username = $_POST["username"];
        $role = $_POST["role"];

        $stmt = $pdo->prepare("UPDATE users SET nama_lengkap=?, email=?, username=?, role=? WHERE id=?");
        $stmt->execute([$nama, $email, $username, $role, $id]);

        header("Location: admin-users.php");
        exit;
    }

    if ($action === "delete") {
    $id = (int)($_POST["id"] ?? 0);

    if ($id <= 0) {
        header("Location: admin-users.php?error=" . urlencode("ID user tidak valid."));
        exit;
    }

    try {

        $stmt = $pdo->prepare("DELETE FROM pesanan WHERE user_id = ?");
        $stmt->execute([$id]);

        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        header("Location: admin-users.php?success=" . urlencode("User dan seluruh pesanannya berhasil dihapus."));
        exit;
    } catch (PDOException $e) {
        header("Location: admin-users.php?error=" . urlencode("Gagal menghapus user: " . $e->getMessage()));
        exit;
    }
}
}


$users = $pdo->query("SELECT * FROM users ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Kelola Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="p-4">

<h2>Kelola Users</h2>
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
                <a class="nav-link active bg-primary text-white rounded" href="admin-users.php">
                    <i class="bi bi-people"></i> Kelola Users
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link " href="admin-motor.php">
                    <i class="bi bi-motorcycle"></i> Kelola Motor
                </a>
            </li>
            <li class="nav-item mb-2">
                <a class="nav-link" href="admin-pesanan.php">
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

<button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">
    Tambah User
</button>


<table class="table table-bordered">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Username</th>
            <th>Role</th>
            <th width="160">Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
            <td><?= $u["id"] ?></td>
            <td><?= htmlspecialchars($u["nama_lengkap"]) ?></td>
            <td><?= htmlspecialchars($u["email"]) ?></td>
            <td><?= htmlspecialchars($u["username"]) ?></td>
            <td><?= htmlspecialchars($u["role"]) ?></td>
            <td>

                <button class="btn btn-warning btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#editModal"
                    data-id="<?= $u['id'] ?>"
                    data-nama="<?= htmlspecialchars($u['nama_lengkap']) ?>"
                    data-email="<?= htmlspecialchars($u['email']) ?>"
                    data-username="<?= htmlspecialchars($u['username']) ?>"
                    data-role="<?= htmlspecialchars($u['role']) ?>">
                    Edit
                </button>


                <button class="btn btn-danger btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#deleteModal"
                    data-id="<?= $u['id'] ?>">
                    Hapus
                </button>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>


<div class="modal fade" id="addModal">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">
<input type="hidden" name="action" value="create">

<div class="modal-header">
    <h5 class="modal-title">Tambah User</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <div class="mb-2">
        <label class="form-label">Nama</label>
        <input type="text" class="form-control" name="nama" required>
    </div>

    <div class="mb-2">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" required>
    </div>

    <div class="mb-2">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" name="username" required>
    </div>

    <div class="mb-2">
        <label class="form-label">Password</label>
        <input type="password" class="form-control" name="password" required>
    </div>

    <div class="mb-2">
        <label class="form-label">Role</label>
        <select class="form-select" name="role">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
    </div>

</div>

<div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    <button class="btn btn-primary">Simpan</button>
</div>

</form>
</div>
</div>
</div>

<div class="modal fade" id="editModal">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">
<input type="hidden" name="action" value="update">
<input type="hidden" name="id" id="edit_id">

<div class="modal-header">
    <h5 class="modal-title">Edit User</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

    <div class="mb-2">
        <label class="form-label">Nama</label>
        <input type="text" class="form-control" name="nama" id="edit_nama" required>
    </div>

    <div class="mb-2">
        <label class="form-label">Email</label>
        <input type="email" class="form-control" name="email" id="edit_email" required>
    </div>

    <div class="mb-2">
        <label class="form-label">Username</label>
        <input type="text" class="form-control" name="username" id="edit_username" required>
    </div>

    <div class="mb-2">
        <label class="form-label">Role</label>
        <select class="form-select" name="role" id="edit_role">
            <option value="user">User</option>
            <option value="admin">Admin</option>
        </select>
    </div>

</div>

<div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    <button class="btn btn-warning">Update</button>
</div>

</form>
</div>
</div>
</div>


<div class="modal fade" id="deleteModal">
<div class="modal-dialog">
<div class="modal-content">

<form method="POST">
<input type="hidden" name="action" value="delete">
<input type="hidden" name="id" id="delete_id">

<div class="modal-header bg-danger text-white">
    <h5 class="modal-title">Hapus User</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">
    Yakin ingin menghapus user ini?
</div>

<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
    <button type="submit" class="btn btn-danger">Hapus</button>
</div>

</form>
</div>
</div>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>

var editModal = document.getElementById("editModal");
editModal.addEventListener("show.bs.modal", function (e) {
    var btn = e.relatedTarget;
    document.getElementById("edit_id").value = btn.dataset.id;
    document.getElementById("edit_nama").value = btn.dataset.nama;
    document.getElementById("edit_email").value = btn.dataset.email;
    document.getElementById("edit_username").value = btn.dataset.username;
    document.getElementById("edit_role").value = btn.dataset.role;
});

var delModal = document.getElementById("deleteModal");
delModal.addEventListener("show.bs.modal", function (e) {
    var btn = e.relatedTarget;
    document.getElementById("delete_id").value = btn.dataset.id;
});
</script>

</body>
</html>
