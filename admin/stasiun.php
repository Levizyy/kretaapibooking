<?php
require_once __DIR__ . '/../includes/config.php';
/** @var mysqli $conn */
check_admin_login();

$message = '';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'delete' && $id) {
    $delete = "DELETE FROM stasiun WHERE id = $id";
    if (mysqli_query($conn, $delete)) $message = "Stasiun berhasil dihapus!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_stasiun = sanitize($_POST['nama_stasiun']);
    $kota         = sanitize($_POST['kota']);
    $alamat       = sanitize($_POST['alamat']);
    $telepon      = sanitize($_POST['telepon']);

    if ($action === 'edit' && $id) {
        $update = "UPDATE stasiun SET nama_stasiun='$nama_stasiun', kota='$kota', alamat='$alamat', telepon='$telepon' WHERE id=$id";
        if (mysqli_query($conn, $update)) { header("Location: stasiun.php?message=updated"); exit(); }
    } else {
        $insert = "INSERT INTO stasiun (nama_stasiun, kota, alamat, telepon) VALUES ('$nama_stasiun', '$kota', '$alamat', '$telepon')";
        if (mysqli_query($conn, $insert)) { header("Location: stasiun.php?message=added"); exit(); }
    }
}

$edit_data = null;
if ($action === 'edit' && $id) {
    $result = mysqli_query($conn, "SELECT * FROM stasiun WHERE id = $id");
    $edit_data = mysqli_fetch_assoc($result);
}

$stasiun_result = mysqli_query($conn, "SELECT * FROM stasiun ORDER BY kota, nama_stasiun");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Stasiun — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Figtree:ital,wght@0,300;0,400;0,500;0,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="top-bar">
            <h2>Kelola Stasiun</h2>
        </header>

        <div class="content">
            <?php if ($_GET['message'] ?? ''): ?>
            <div class="alert alert-success">
                ✓ <?php
                if ($_GET['message'] === 'added') echo "Stasiun berhasil ditambahkan!";
                elseif ($_GET['message'] === 'updated') echo "Stasiun berhasil diperbarui!";
                ?>
            </div>
            <?php endif; ?>
            <?php if ($message): ?><div class="alert alert-success">✓ <?= $message ?></div><?php endif; ?>

            <div class="page-header">
                <h3><?= ($action === 'edit' && $edit_data) ? 'Edit Stasiun' : 'Tambah Stasiun Baru' ?></h3>
                <?php if ($action === 'edit'): ?>
                <a href="stasiun.php" class="btn btn-secondary">← Kembali</a>
                <?php endif; ?>
            </div>

            <div class="form-section">
                <?php if ($action === 'edit' && $edit_data): ?>
                <h3>Edit Data Stasiun</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Stasiun</label>
                            <input type="text" name="nama_stasiun" value="<?= $edit_data['nama_stasiun'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Kota</label>
                            <input type="text" name="kota" value="<?= $edit_data['kota'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="tel" name="telepon" value="<?= $edit_data['telepon'] ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" rows="3" required><?= $edit_data['alamat'] ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="stasiun.php" class="btn btn-ghost">Batal</a>
                    </div>
                </form>
                <?php else: ?>
                <h3>Tambah Stasiun Baru</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Stasiun</label>
                            <input type="text" name="nama_stasiun" placeholder="Contoh: Stasiun Gambir" required>
                        </div>
                        <div class="form-group">
                            <label>Kota</label>
                            <input type="text" name="kota" placeholder="Contoh: Jakarta" required>
                        </div>
                        <div class="form-group">
                            <label>Telepon</label>
                            <input type="tel" name="telepon" placeholder="021-xxxxxxxx">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <textarea name="alamat" rows="3" placeholder="Alamat lengkap stasiun" required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">+ Tambah Stasiun</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>

            <div class="form-section">
                <h3>Daftar Stasiun</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Stasiun</th>
                            <th>Kota</th>
                            <th>Alamat</th>
                            <th>Telepon</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($stasiun_result)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= $row['nama_stasiun'] ?></strong></td>
                            <td><?= $row['kota'] ?></td>
                            <td><?= $row['alamat'] ?></td>
                            <td><?= $row['telepon'] ?></td>
                            <td style="display:flex;gap:6px;">
                                <a href="stasiun.php?action=edit&id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="stasiun.php?action=delete&id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus stasiun ini?')">Hapus</a>
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
