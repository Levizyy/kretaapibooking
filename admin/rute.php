<?php
require_once __DIR__ . '/../includes/config.php';
/** @var mysqli $conn */
check_admin_login();

$message = '';
$action = $_GET['action'] ?? '';
$id = $_GET['id'] ?? '';

// Handle Delete
if ($action === 'delete' && $id) {
    $delete = "DELETE FROM rute_kereta WHERE id = $id";
    if (mysqli_query($conn, $delete)) {
        $message = "Rute berhasil dihapus!";
    } else {
        $message = "Gagal menghapus rute!";
    }
}

// Handle Add/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kereta_id = sanitize($_POST['kereta_id']);
    $stasiun_awal_id = sanitize($_POST['stasiun_awal_id']);
    $stasiun_akhir_id = sanitize($_POST['stasiun_akhir_id']);
    $waktu_berangkat = sanitize($_POST['waktu_berangkat']);
    $waktu_tiba = sanitize($_POST['waktu_tiba']);
    $durasi_perjalanan = sanitize($_POST['durasi_perjalanan']);
    $harga_dewasa = sanitize($_POST['harga_dewasa']);
    $harga_anak = sanitize($_POST['harga_anak']);
    $harga_bayi = sanitize($_POST['harga_bayi']);
    $tgl_operasi = sanitize($_POST['tgl_operasi']);
    $kapasitas_tersedia = sanitize($_POST['kapasitas_tersedia']);
    $status = sanitize($_POST['status']);
    
    if ($action === 'edit' && $id) {
        $update = "UPDATE rute_kereta SET 
                   kereta_id = $kereta_id,
                   stasiun_awal_id = $stasiun_awal_id,
                   stasiun_akhir_id = $stasiun_akhir_id,
                   waktu_berangkat = '$waktu_berangkat',
                   waktu_tiba = '$waktu_tiba',
                   durasi_perjalanan = $durasi_perjalanan,
                   harga_dewasa = $harga_dewasa,
                   harga_anak = $harga_anak,
                   harga_bayi = $harga_bayi,
                   tgl_operasi = '$tgl_operasi',
                   kapasitas_tersedia = $kapasitas_tersedia,
                   status = '$status'
                   WHERE id = $id";
        if (mysqli_query($conn, $update)) {
            header("Location: rute.php?message=updated");
            exit();
        }
    } else {
        $insert = "INSERT INTO rute_kereta 
                  (kereta_id, stasiun_awal_id, stasiun_akhir_id, waktu_berangkat, waktu_tiba, durasi_perjalanan, harga_dewasa, harga_anak, harga_bayi, tgl_operasi, kapasitas_tersedia, status)
                  VALUES ($kereta_id, $stasiun_awal_id, $stasiun_akhir_id, '$waktu_berangkat', '$waktu_tiba', $durasi_perjalanan, $harga_dewasa, $harga_anak, $harga_bayi, '$tgl_operasi', $kapasitas_tersedia, '$status')";
        if (mysqli_query($conn, $insert)) {
            header("Location: rute.php?message=added");
            exit();
        }
    }
}

// Get rute for edit
$edit_data = null;
if ($action === 'edit' && $id) {
    $query = "SELECT r.*, k.nama_kereta, s1.nama_stasiun as awal, s2.nama_stasiun as akhir
              FROM rute_kereta r
              JOIN kereta_api k ON r.kereta_id = k.id
              JOIN stasiun s1 ON r.stasiun_awal_id = s1.id
              JOIN stasiun s2 ON r.stasiun_akhir_id = s2.id
              WHERE r.id = $id";
    $result = mysqli_query($conn, $query);
    $edit_data = mysqli_fetch_assoc($result);
}

// Get all rute
$rute_query = "SELECT r.*, k.nama_kereta, s1.nama_stasiun as awal, s2.nama_stasiun as akhir
               FROM rute_kereta r
               JOIN kereta_api k ON r.kereta_id = k.id
               JOIN stasiun s1 ON r.stasiun_awal_id = s1.id
               JOIN stasiun s2 ON r.stasiun_akhir_id = s2.id
               ORDER BY r.tgl_operasi DESC, r.waktu_berangkat ASC";
$rute_result = mysqli_query($conn, $rute_query);

// Get kereta and stasiun for dropdowns
$kereta_options = mysqli_query($conn, "SELECT id, nama_kereta FROM kereta_api WHERE status = 'aktif'");
$stasiun_options = mysqli_query($conn, "SELECT id, nama_stasiun FROM stasiun");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Rute - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Figtree:ital,wght@0,300;0,400;0,500;0,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <style>
        /* durasi_perjalanan field - only in add form, not edit */
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <header class="top-bar">
                <h2>Kelola Rute Kereta</h2>
            </header>
            
            <div class="content">
                <?php if ($_GET['message'] ?? ''): ?>
                    <div class="alert alert-success">
                        <?php
                        if ($_GET['message'] === 'added') echo "Rute berhasil ditambahkan!";
                        else if ($_GET['message'] === 'updated') echo "Rute berhasil diperbarui!";
                        ?>
                    </div>
                <?php endif; ?>
                
                <div class="page-header">
                    <h3>Daftar Rute</h3>
                    <a href="rute.php" class="btn btn-primary">+ Tambah Rute</a>
                </div>
                
                <?php if ($action === 'edit' && $edit_data): ?>
                    <div class="form-section">
                        <h3>Edit Rute</h3>
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="kereta_id">Kereta</label>
                                    <select id="kereta_id" name="kereta_id" required>
                                        <option value="">-- Pilih Kereta --</option>
                                        <?php
                                        mysqli_data_seek($kereta_options, 0);
                                        while ($row = mysqli_fetch_assoc($kereta_options)):
                                        ?>
                                            <option value="<?php echo $row['id']; ?>" <?php echo $edit_data['kereta_id'] == $row['id'] ? 'selected' : ''; ?>><?php echo $row['nama_kereta']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="stasiun_awal_id">Stasiun Awal</label>
                                    <select id="stasiun_awal_id" name="stasiun_awal_id" required>
                                        <option value="">-- Pilih Stasiun --</option>
                                        <?php
                                        mysqli_data_seek($stasiun_options, 0);
                                        while ($row = mysqli_fetch_assoc($stasiun_options)):
                                        ?>
                                            <option value="<?php echo $row['id']; ?>" <?php echo $edit_data['stasiun_awal_id'] == $row['id'] ? 'selected' : ''; ?>><?php echo $row['nama_stasiun']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="stasiun_akhir_id">Stasiun Akhir</label>
                                    <select id="stasiun_akhir_id" name="stasiun_akhir_id" required>
                                        <option value="">-- Pilih Stasiun --</option>
                                        <?php
                                        mysqli_data_seek($stasiun_options, 0);
                                        while ($row = mysqli_fetch_assoc($stasiun_options)):
                                        ?>
                                            <option value="<?php echo $row['id']; ?>" <?php echo $edit_data['stasiun_akhir_id'] == $row['id'] ? 'selected' : ''; ?>><?php echo $row['nama_stasiun']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="waktu_berangkat">Waktu Berangkat</label>
                                    <input type="time" id="waktu_berangkat" name="waktu_berangkat" value="<?php echo $edit_data['waktu_berangkat']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="waktu_tiba">Waktu Tiba</label>
                                    <input type="time" id="waktu_tiba" name="waktu_tiba" value="<?php echo $edit_data['waktu_tiba']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="durasi_perjalanan">Durasi (menit)</label>
                                    <input type="number" id="durasi_perjalanan" name="durasi_perjalanan" value="<?php echo $edit_data['durasi_perjalanan']; ?>" required>
                                </div>
                                    <input type="number" id="harga_dewasa" name="harga_dewasa" value="<?php echo $edit_data['harga_dewasa']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="harga_anak">Harga Anak</label>
                                    <input type="number" id="harga_anak" name="harga_anak" value="<?php echo $edit_data['harga_anak']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="harga_bayi">Harga Bayi</label>
                                    <input type="number" id="harga_bayi" name="harga_bayi" value="<?php echo $edit_data['harga_bayi']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="tgl_operasi">Tanggal Operasi</label>
                                    <input type="date" id="tgl_operasi" name="tgl_operasi" value="<?php echo $edit_data['tgl_operasi']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="kapasitas_tersedia">Kapasitas Tersedia</label>
                                    <input type="number" id="kapasitas_tersedia" name="kapasitas_tersedia" value="<?php echo $edit_data['kapasitas_tersedia']; ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" required>
                                        <option value="aktif" <?php echo $edit_data['status'] === 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                                        <option value="nonaktif" <?php echo $edit_data['status'] === 'nonaktif' ? 'selected' : ''; ?>>Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-success">Update</button>
                                <a href="rute.php" class="btn btn-ghost">Batal</a>
                            </div>
                        </form>
                    </div>
                <?php elseif ($action !== 'edit'): ?>
                    <div class="form-section">
                        <h3>Tambah Rute Baru</h3>
                        <form method="POST">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="kereta_id">Kereta</label>
                                    <select id="kereta_id" name="kereta_id" required>
                                        <option value="">-- Pilih Kereta --</option>
                                        <?php
                                        mysqli_data_seek($kereta_options, 0);
                                        while ($row = mysqli_fetch_assoc($kereta_options)):
                                        ?>
                                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_kereta']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="stasiun_awal_id">Stasiun Awal</label>
                                    <select id="stasiun_awal_id" name="stasiun_awal_id" required>
                                        <option value="">-- Pilih Stasiun --</option>
                                        <?php
                                        mysqli_data_seek($stasiun_options, 0);
                                        while ($row = mysqli_fetch_assoc($stasiun_options)):
                                        ?>
                                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_stasiun']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="stasiun_akhir_id">Stasiun Akhir</label>
                                    <select id="stasiun_akhir_id" name="stasiun_akhir_id" required>
                                        <option value="">-- Pilih Stasiun --</option>
                                        <?php
                                        mysqli_data_seek($stasiun_options, 0);
                                        while ($row = mysqli_fetch_assoc($stasiun_options)):
                                        ?>
                                            <option value="<?php echo $row['id']; ?>"><?php echo $row['nama_stasiun']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="waktu_berangkat">Waktu Berangkat</label>
                                    <input type="time" id="waktu_berangkat" name="waktu_berangkat" required>
                                </div>
                                <div class="form-group">
                                    <label for="waktu_tiba">Waktu Tiba</label>
                                    <input type="time" id="waktu_tiba" name="waktu_tiba" required>
                                </div>
                                <div class="form-group">
                                    <label for="durasi_perjalanan">Durasi (menit)</label>
                                    <input type="number" id="durasi_perjalanan" name="durasi_perjalanan" required>
                                </div>
                                <div class="form-group">
                                    <label for="harga_dewasa">Harga Dewasa</label>
                                    <input type="number" id="harga_dewasa" name="harga_dewasa" required>
                                </div>
                                <div class="form-group">
                                    <label for="harga_anak">Harga Anak</label>
                                    <input type="number" id="harga_anak" name="harga_anak" required>
                                </div>
                                <div class="form-group">
                                    <label for="harga_bayi">Harga Bayi</label>
                                    <input type="number" id="harga_bayi" name="harga_bayi" required>
                                </div>
                                <div class="form-group">
                                    <label for="tgl_operasi">Tanggal Operasi</label>
                                    <input type="date" id="tgl_operasi" name="tgl_operasi" required>
                                </div>
                                <div class="form-group">
                                    <label for="kapasitas_tersedia">Kapasitas Tersedia</label>
                                    <input type="number" id="kapasitas_tersedia" name="kapasitas_tersedia" required>
                                </div>
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select id="status" name="status" required>
                                        <option value="aktif">Aktif</option>
                                        <option value="nonaktif">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Tambah</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
                
                <div class="form-section">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Kereta</th>
                                <th>Asal - Tujuan</th>
                                <th>Berangkat - Tiba</th>
                                <th>Harga Dewasa</th>
                                <th>Kapasitas</th>
                                <th>Operasi</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($rute_result)): ?>
                                <tr>
                                    <td><?php echo $row['nama_kereta']; ?></td>
                                    <td><?php echo $row['awal']; ?> - <?php echo $row['akhir']; ?></td>
                                    <td><?php echo $row['waktu_berangkat']; ?> - <?php echo $row['waktu_tiba']; ?></td>
                                    <td><?php echo format_currency($row['harga_dewasa']); ?></td>
                                    <td><?php echo $row['kapasitas_tersedia']; ?></td>
                                    <td><?php echo format_date($row['tgl_operasi']); ?></td>
                                    <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                    <td>
                                        <a href="rute.php?action=edit&id=<?php echo $row['id']; ?>" class="btn btn-warning" style="font-size: 12px; padding: 5px 10px;">Edit</a>
                                        <a href="rute.php?action=delete&id=<?php echo $row['id']; ?>" class="btn btn-danger" style="font-size: 12px; padding: 5px 10px;" onclick="return confirm('Yakin ingin menghapus?');">Hapus</a>
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
