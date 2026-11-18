<?php
require_once __DIR__ . '/../includes/config.php';
/** @var mysqli $conn */
check_admin_login();

$message = '';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'delete' && $id) {
    $delete = "DELETE FROM users WHERE id = $id AND role = 'user'";
    if (mysqli_query($conn, $delete)) $message = "User berhasil dihapus!";
}

if ($action === 'toggle_status' && $id) {
    $result = mysqli_query($conn, "SELECT status FROM users WHERE id = $id AND role = 'user'");
    $row = mysqli_fetch_assoc($result);
    $new_status = ($row['status'] === 'active') ? 'inactive' : 'active';
    $update = "UPDATE users SET status = '$new_status' WHERE id = $id";
    if (mysqli_query($conn, $update)) { header("Location: pengguna.php"); exit(); }
}

$user_result = mysqli_query($conn, "SELECT * FROM users WHERE role = 'user' ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Figtree:ital,wght@0,300;0,400;0,500;0,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="top-bar">
            <h2>Kelola Pengguna</h2>
        </header>

        <div class="content">
            <?php if ($message): ?><div class="alert alert-success">✓ <?= $message ?></div><?php endif; ?>

            <div class="form-section">
                <h3>Daftar Pengguna Terdaftar</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Email</th>
                            <th>Telepon</th>
                            <th>Status</th>
                            <th>Bergabung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($user_result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= $row['username'] ?></strong></td>
                            <td><?= $row['full_name'] ?></td>
                            <td><?= $row['email'] ?></td>
                            <td><?= $row['phone'] ?? '-' ?></td>
                            <td>
                                <span class="badge badge-<?= $row['status'] ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td><?= format_date($row['created_at']) ?></td>
                            <td style="display:flex;gap:6px;">
                                <a href="pengguna.php?action=toggle_status&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">
                                    <?= $row['status'] === 'active' ? 'Nonaktifkan' : 'Aktifkan' ?>
                                </a>
                                <a href="pengguna.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin ingin menghapus user ini?')">Hapus</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>
