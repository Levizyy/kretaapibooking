<?php
require_once __DIR__ . '/../includes/config.php';
/** @var mysqli $conn */
check_admin_login();

$message = '';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

if ($action === 'delete' && $id) {
    $delete = "DELETE FROM kereta_api WHERE id = $id";
    if (mysqli_query($conn, $delete)) $message = "Kereta berhasil dihapus!";
    else $message = "Gagal menghapus kereta!";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_kereta  = sanitize($_POST['nama_kereta']);
    $no_kereta    = sanitize($_POST['no_kereta']);
    $kapasitas    = sanitize($_POST['kapasitas']);
    $jenis_kereta = sanitize($_POST['jenis_kereta']);
    $status       = sanitize($_POST['status']);

    if ($action === 'edit' && $id) {
        $update = "UPDATE kereta_api SET nama_kereta='$nama_kereta', no_kereta='$no_kereta', kapasitas=$kapasitas, jenis_kereta='$jenis_kereta', status='$status' WHERE id=$id";
        if (mysqli_query($conn, $update)) { header("Location: kereta.php?message=updated"); exit(); }
        else $message = "Gagal mengupdate kereta!";
    } else {
        $insert = "INSERT INTO kereta_api (nama_kereta, no_kereta, kapasitas, jenis_kereta, status) VALUES ('$nama_kereta', '$no_kereta', $kapasitas, '$jenis_kereta', '$status')";
        if (mysqli_query($conn, $insert)) { header("Location: kereta.php?message=added"); exit(); }
        else $message = "Gagal menambah kereta!";
    }
}

$edit_data = null;
if ($action === 'edit' && $id) {
    $result = mysqli_query($conn, "SELECT * FROM kereta_api WHERE id = $id");
    $edit_data = mysqli_fetch_assoc($result);
}

$kereta_result = mysqli_query($conn, "SELECT * FROM kereta_api ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kereta — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Figtree:ital,wght@0,300;0,400;0,500;0,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
</head>
<body>
<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="top-bar">
            <h2>Kelola Kereta Api</h2>
        </header>

        <div class="content">
            <?php if ($_GET['message'] ?? ''): ?>
            <div class="alert alert-success">
                ✓ <?php
                if ($_GET['message'] === 'added') echo "Kereta berhasil ditambahkan!";
                elseif ($_GET['message'] === 'updated') echo "Kereta berhasil diperbarui!";
                ?>
            </div>
            <?php endif; ?>

            <?php if ($message): ?>
            <div class="alert alert-success">✓ <?php echo $message; ?></div>
            <?php endif; ?>

            <div class="page-header">
                <h3><?php echo ($action === 'edit' && $edit_data) ? 'Edit Kereta' : 'Tambah Kereta Baru'; ?></h3>
                <?php if ($action === 'edit'): ?>
                <a href="kereta.php" class="btn btn-secondary">← Kembali</a>
                <?php endif; ?>
            </div>

            <div class="form-section">
                <?php if ($action === 'edit' && $edit_data): ?>
                <h3>Edit Data Kereta</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Kereta</label>
                            <input type="text" name="nama_kereta" value="<?php echo $edit_data['nama_kereta']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>No. Kereta</label>
                            <input type="text" name="no_kereta" value="<?php echo $edit_data['no_kereta']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Kapasitas</label>
                            <input type="number" name="kapasitas" value="<?php echo $edit_data['kapasitas']; ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Jenis Kereta</label>
                            <select name="jenis_kereta" required>
                                <option value="Express" <?php echo $edit_data['jenis_kereta']==='Express'?'selected':''; ?>>Express</option>
                                <option value="Lokal" <?php echo $edit_data['jenis_kereta']==='Lokal'?'selected':''; ?>>Lokal</option>
                                <option value="Premium" <?php echo $edit_data['jenis_kereta']==='Premium'?'selected':''; ?>>Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" required>
                                <option value="aktif" <?php echo $edit_data['status']==='aktif'?'selected':''; ?>>Aktif</option>
                                <option value="nonaktif" <?php echo $edit_data['status']==='nonaktif'?'selected':''; ?>>Nonaktif</option>
                                <option value="maintenance" <?php echo $edit_data['status']==='maintenance'?'selected':''; ?>>Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="kereta.php" class="btn btn-ghost">Batal</a>
                    </div>
                </form>
                <?php else: ?>
                <h3>Tambah Kereta Baru</h3>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nama Kereta</label>
                            <input type="text" name="nama_kereta" placeholder="Contoh: Argo Bromo Anggrek" required>
                        </div>
                        <div class="form-group">
                            <label>No. Kereta</label>
                            <input type="text" name="no_kereta" placeholder="Contoh: KA-001" required>
                        </div>
                        <div class="form-group">
                            <label>Kapasitas</label>
                            <input type="number" name="kapasitas" placeholder="Contoh: 500" required>
                        </div>
                        <div class="form-group">
                            <label>Jenis Kereta</label>
                            <select name="jenis_kereta" required>
                                <option value="">— Pilih —</option>
                                <option value="Express">Express</option>
                                <option value="Lokal">Lokal</option>
                                <option value="Premium">Premium</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <select name="status" required>
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">+ Tambah Kereta</button>
                    </div>
                </form>
                <?php endif; ?>
            </div>

            <div class="form-section">
                <h3>Daftar Kereta</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kereta</th>
                            <th>No. Kereta</th>
                            <th>Kapasitas</th>
                            <th>Jenis</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while ($row = mysqli_fetch_assoc($kereta_result)): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><strong><?php echo $row['nama_kereta']; ?></strong></td>
                            <td><?php echo $row['no_kereta']; ?></td>
                            <td><?php echo number_format($row['kapasitas']); ?> orang</td>
                            <td><?php echo $row['jenis_kereta']; ?></td>
                            <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                            <td style="display:flex;gap:6px;">
                                <a href="kereta.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="kereta.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus kereta ini?')">Hapus</a>
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
