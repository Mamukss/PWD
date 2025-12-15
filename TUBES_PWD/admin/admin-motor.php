<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION['user']) || ($_SESSION['user']['role'] ?? '') !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$admin = $_SESSION['user'];

$success = $_GET['success'] ?? '';
$error   = $_GET['error'] ?? '';


function redirectWithMessage($type, $msg) {
    $location = "admin-motor.php?$type=" . urlencode($msg);
    header("Location: $location");
    exit;
}


if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];
    if ($id > 0) {

        $stmt = $pdo->prepare("SELECT foto FROM motor WHERE id = ?");
        $stmt->execute([$id]);
        $m = $stmt->fetch(PDO::FETCH_ASSOC);

        $del = $pdo->prepare("DELETE FROM motor WHERE id = ?");
        $del->execute([$id]);

        if ($m && !empty($m['foto'])) {
            $path = "../uploads/motor/" . $m['foto'];
            if (file_exists($path)) {
                unlink($path);
            }
        }

        redirectWithMessage('success', 'Motor berhasil dihapus.');
    } else {
        redirectWithMessage('error', 'ID motor tidak valid.');
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action       = $_POST['action'] ?? 'create';
    $nama_motor   = trim($_POST['nama_motor'] ?? '');
    $merek        = trim($_POST['merek'] ?? '');
    $tahun        = (int)($_POST['tahun'] ?? 0);
    $kapasitas_mesin = (int)($_POST['kapasitas_mesin'] ?? 0);
    $harga        = (int)($_POST['harga'] ?? 0);
    $stok         = (int)($_POST['stok'] ?? 0);
    $deskripsi    = trim($_POST['deskripsi'] ?? '');
    $id_motor     = (int)($_POST['id'] ?? 0);

    if ($nama_motor === '' || $merek === '' || !$tahun || !$kapasitas_mesin || !$harga) {
        redirectWithMessage('error', 'Nama, merek, tahun, kapasitas, dan harga wajib diisi.');
    }

    $fotoName = null;
    if (!empty($_FILES['foto']['name'])) {
        $fileTmp  = $_FILES['foto']['tmp_name'];
        $origName = basename($_FILES['foto']['name']);
        $ext      = pathinfo($origName, PATHINFO_EXTENSION);
        $fotoName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($origName, PATHINFO_FILENAME)) . '.' . $ext;
        $targetDir  = "../uploads/motor/";

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (!move_uploaded_file($fileTmp, $targetDir . $fotoName)) {
            redirectWithMessage('error', 'Gagal mengupload foto.');
        }
    }

    try {
        if ($action === 'update' && $id_motor > 0) {
  
            $stmt = $pdo->prepare("SELECT foto FROM motor WHERE id = ?");
            $stmt->execute([$id_motor]);
            $old = $stmt->fetch(PDO::FETCH_ASSOC);


            if ($fotoName === null) {
                $fotoName = $old['foto'] ?? null;
            } else {

                if ($old && !empty($old['foto'])) {
                    $oldPath = "../uploads/motor/" . $old['foto'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
            }

            $sql = "UPDATE motor
                    SET nama_motor = ?, merek = ?, tahun = ?, kapasitas_mesin = ?, harga = ?, stok = ?, deskripsi = ?, foto = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nama_motor,
                $merek,
                $tahun,
                $kapasitas_mesin,
                $harga,
                $stok,
                $deskripsi,
                $fotoName,
                $id_motor
            ]);

            redirectWithMessage('success', 'Data motor berhasil diperbarui.');

        } else {

            $sql = "INSERT INTO motor (nama_motor, merek, tahun, kapasitas_mesin, harga, stok, deskripsi, foto)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $nama_motor,
                $merek,
                $tahun,
                $kapasitas_mesin,
                $harga,
                $stok,
                $deskripsi,
                $fotoName
            ]);

            redirectWithMessage('success', 'Motor baru berhasil ditambahkan.');
        }

    } catch (PDOException $e) {
        redirectWithMessage('error', 'DB error: ' . $e->getMessage());
    }
}


$stmt = $pdo->query("SELECT * FROM motor ORDER BY id DESC");
$motors = $stmt->fetchAll(PDO::FETCH_ASSOC);


$editMotor = null;
if (isset($_GET['edit'])) {
    $editId = (int) $_GET['edit'];
    foreach ($motors as $m) {
        if ((int)$m['id'] === $editId) {
            $editMotor = $m;
            break;
        }
    }
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
    <title>Kelola Motor - MotorKu Admin</title>
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
                <a class="nav-link active bg-primary text-white rounded" href="admin-motor.php">
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

    <div class="flex-grow-1">

        <nav class="navbar navbar-dark bg-primary">
            <div class="container-fluid">
                <span class="navbar-brand"><i class="bi bi-motorcycle"></i> Kelola Motor</span>
                <span class="text-white">
                    <i class="bi bi-person-circle"></i>
                    <?= htmlspecialchars($admin['nama_lengkap'] ?? $admin['username'] ?? 'Admin') ?>
                </span>
            </div>
        </nav>

        <div class="dashboard-content">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h3 class="mb-0">Data Motor</h3>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="bi bi-plus-circle"></i>
                        <?= $editMotor ? 'Edit Motor' : 'Tambah Motor Baru' ?>
                    </h5>
                </div>
                <div class="card-body">
                    <form method="POST" enctype="multipart/form-data">
                        <?php if ($editMotor): ?>
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="id" value="<?= (int)$editMotor['id'] ?>">
                        <?php else: ?>
                            <input type="hidden" name="action" value="create">
                        <?php endif; ?>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Motor</label>
                                <input type="text" name="nama_motor" class="form-control" required
                                       value="<?= htmlspecialchars($editMotor['nama_motor'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Merek</label>
                                <input type="text" name="merek" class="form-control" required
                                       value="<?= htmlspecialchars($editMotor['merek'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tahun</label>
                                <input type="number" name="tahun" class="form-control" required
                                       value="<?= htmlspecialchars($editMotor['tahun'] ?? date('Y')) ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Kapasitas (cc)</label>
                                <input type="number" name="kapasitas_mesin" class="form-control" required
                                       value="<?= htmlspecialchars($editMotor['kapasitas_mesin'] ?? '') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Stok</label>
                                <input type="number" name="stok" class="form-control"
                                       value="<?= htmlspecialchars($editMotor['stok'] ?? 0) ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Harga</label>
                                <input type="number" name="harga" class="form-control" required
                                       value="<?= htmlspecialchars($editMotor['harga'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Foto</label>
                                <input type="file" name="foto" class="form-control" accept="image/*">
                                <?php if ($editMotor && !empty($editMotor['foto'])): ?>
                                    <small class="text-muted d-block mt-1">
                                        Foto saat ini:
                                        <img src="../uploads/motor/<?= htmlspecialchars($editMotor['foto']) ?>"
                                             style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" rows="3" class="form-control"><?= htmlspecialchars($editMotor['deskripsi'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="mt-3 d-flex justify-content-between">
                            <?php if ($editMotor): ?>
                                <a href="admin-motor.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Batal Edit
                                </a>
                            <?php else: ?>
                                <span></span>
                            <?php endif; ?>
                            <button class="btn btn-primary">
                                <i class="bi bi-save"></i>
                                <?= $editMotor ? 'Simpan Perubahan' : 'Tambah Motor' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-list"></i> Daftar Motor</h5>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Motor</th>
                                    <th>Spesifikasi</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Foto</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (empty($motors)): ?>
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">
                                        Belum ada data motor.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($motors as $m): ?>
                                    <tr>
                                        <td><?= (int)$m['id'] ?></td>
                                        <td>
                                            <strong><?= htmlspecialchars($m['nama_motor']) ?></strong><br>
                                            <small class="text-muted"><?= htmlspecialchars($m['merek']) ?></small>
                                        </td>
                                        <td>
                                            <small>
                                                Tahun: <?= htmlspecialchars($m['tahun']) ?><br>
                                                <?= htmlspecialchars($m['kapasitas_mesin']) ?> cc
                                            </small>
                                        </td>
                                        <td><?= rupiah($m['harga']) ?></td>
                                        <td><?= (int)$m['stok'] ?></td>
                                        <td>
                                            <?php if (!empty($m['foto'])): ?>
                                                <img src="../uploads/motor/<?= htmlspecialchars($m['foto']) ?>"
                                                     style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                                            <?php else: ?>
                                                <span class="text-muted small">Tidak ada</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="admin-motor.php?edit=<?= (int)$m['id'] ?>" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="admin-motor.php?delete=<?= (int)$m['id'] ?>"
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Yakin ingin menghapus motor ini?');">
                                                <i class="bi bi-trash"></i>
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
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
