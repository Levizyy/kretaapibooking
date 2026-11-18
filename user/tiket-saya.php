<?php
require '../includes/config.php';
/** @var mysqli $conn */
check_user_login();

$user_id = $_SESSION['user_id'];
$filter = $_GET['filter'] ?? '';
$action = $_GET['action'] ?? '';
$act_id = (int)($_GET['act_id'] ?? 0);

// Handle payment (simulate payment)
if ($action === 'bayar' && $act_id) {
    $upd = "UPDATE booking SET status_pembayaran='paid' WHERE id=$act_id AND user_id=$user_id AND status_pembayaran='pending'";
    mysqli_query($conn, $upd);
    header("Location: tiket-saya.php?booking_id=$act_id");
    exit();
}
// Handle cancel
if ($action === 'cancel' && $act_id) {
    // Get booking info to restore capacity
    $bk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM booking WHERE id=$act_id AND user_id=$user_id AND status_pembayaran='pending'"));
    if ($bk) {
        mysqli_query($conn, "UPDATE booking SET status_pembayaran='cancelled', status_booking='cancelled' WHERE id=$act_id");
        mysqli_query($conn, "UPDATE rute_kereta SET kapasitas_tersedia = kapasitas_tersedia + {$bk['jumlah_penumpang']} WHERE id={$bk['rute_kereta_id']}");
    }
    header("Location: tiket-saya.php");
    exit();
}

$query = "SELECT b.*, s.nama_stasiun, k.nama_kereta, r.waktu_berangkat, r.waktu_tiba, r.harga_dewasa
         FROM booking b
         JOIN rute_kereta r ON b.rute_kereta_id = r.id
         JOIN kereta_api k ON r.kereta_id = k.id
         JOIN stasiun s ON r.stasiun_akhir_id = s.id
         WHERE b.user_id = $user_id";

if ($filter === 'paid') {
    $query .= " AND b.status_pembayaran = 'paid'";
} elseif ($filter === 'pending') {
    $query .= " AND b.status_pembayaran = 'pending'";
} elseif ($filter === 'upcoming') {
    $query .= " AND b.tanggal_perjalanan >= CURDATE() AND b.status_pembayaran = 'paid'";
} elseif ($filter === 'completed') {
    $query .= " AND b.tanggal_perjalanan < CURDATE()";
}

$query .= " ORDER BY b.tanggal_booking DESC";
$result = mysqli_query($conn, $query);

// Get booking detail if viewing specific booking
$booking_id = $_GET['booking_id'] ?? '';
$booking_detail = null;
$penumpang_list = [];

if ($booking_id) {
    $detail_query = "SELECT b.*, s.nama_stasiun, k.nama_kereta, r.waktu_berangkat, r.waktu_tiba, r.harga_dewasa, r.harga_anak, r.harga_bayi, r.tgl_operasi, r.durasi_perjalanan, s1.nama_stasiun as awal
                    FROM booking b
                    JOIN rute_kereta r ON b.rute_kereta_id = r.id
                    JOIN kereta_api k ON r.kereta_id = k.id
                    JOIN stasiun s ON r.stasiun_akhir_id = s.id
                    JOIN stasiun s1 ON r.stasiun_awal_id = s1.id
                    WHERE b.id = $booking_id AND b.user_id = $user_id";
    $detail_result = mysqli_query($conn, $detail_query);
    $booking_detail = mysqli_fetch_assoc($detail_result);
    
    if ($booking_detail) {
        $penumpang_query = "SELECT * FROM penumpang WHERE booking_id = $booking_id";
        $penumpang_result = mysqli_query($conn, $penumpang_query);
        $penumpang_list = [];
        while ($penumpang = mysqli_fetch_assoc($penumpang_result)) {
            $penumpang_list[] = $penumpang;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tiket Saya - Kereta Api</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Figtree:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user-style.css">
    <style>
        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .ticket-details {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .detail-header h3 {
            margin: 0;
        }
        
        .detail-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .detail-item {
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .detail-item strong {
            display: block;
            color: #333;
            margin-bottom: 5px;
        }
        
        .detail-item p {
            margin: 0;
            color: #666;
        }
        
        .passenger-list {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .passenger-item {
            background: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .passenger-item:last-child {
            margin-bottom: 0;
        }
        
        .print-btn {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        
        .print-btn:hover {
            background: #5568d3;
        }
        
        @media print {
            .navbar, .footer, .filter-buttons {
                display: none;
            }
            
            .print-btn {
                display: none;
            }
        }
    </style>
    <link rel="stylesheet" href="../assets/css/user-account.css">
</head>
<body>
    <div class="user-container">
        <!-- Header/Navbar -->
        <header class="navbar">
            <div class="navbar-content">
                <h1>🚄 Kereta Api</h1>
                <nav class="nav-menu">
                    <a href="index.php" class="nav-item">Dashboard</a>
                    <a href="cari-tiket.php" class="nav-item">Cari & Pesan Tiket</a>
                    <a href="tiket-saya.php" class="nav-item active">Tiket Saya</a>
                    <a href="profile.php" class="nav-item">Profile</a>
                    <a href="../logout.php" class="nav-item logout">⏻ Logout</a>
                </nav>
            </div>
        </header>
        
        <main class="page-wrap">
            <h2 class="page-title">Tiket Saya</h2>
            <p class="page-subtitle">Lihat status pemesanan dan detail perjalanan Anda.</p>
            
            <?php if ($booking_detail): ?>
                <!-- Detail View -->
                <div class="ticket-details">
                    <div class="detail-header">
                        <div>
                            <h3><?php echo $booking_detail['booking_code']; ?></h3>
                            <p><?php echo format_datetime($booking_detail['tanggal_booking']); ?></p>
                        </div>
                        <div class="detail-actions">
                            <span class="badge badge-<?php echo $booking_detail['status_pembayaran']; ?>">
                                <?php echo ucfirst($booking_detail['status_pembayaran']); ?>
                            </span>
                            <button class="print-btn" type="button" onclick="window.print()">Cetak tiket</button>
                        </div>
                    </div>
                    
                    <h4 class="ticket-section-title">Informasi perjalanan</h4>
                    <div class="detail-grid">
                        <div class="detail-item">
                            <strong><?php echo $booking_detail['nama_kereta']; ?></strong>
                            <p><?php echo $booking_detail['awal']; ?> → <?php echo $booking_detail['nama_stasiun']; ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Waktu Perjalanan</strong>
                            <p><?php echo $booking_detail['waktu_berangkat']; ?> - <?php echo $booking_detail['waktu_tiba']; ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Tanggal Perjalanan</strong>
                            <p><?php echo format_date($booking_detail['tanggal_perjalanan']); ?></p>
                        </div>
                        <div class="detail-item">
                            <strong>Jumlah Penumpang</strong>
                            <p><?php echo $booking_detail['jumlah_penumpang']; ?> orang (Dewasa: <?php echo $booking_detail['jumlah_dewasa']; ?>, Anak: <?php echo $booking_detail['jumlah_anak']; ?>, Bayi: <?php echo $booking_detail['jumlah_bayi']; ?>)</p>
                        </div>
                    </div>
                    
                    <?php if (!empty($penumpang_list)): ?>
                        <h4 class="ticket-section-title">Data penumpang</h4>
                        <div class="passenger-list">
                            <?php foreach ($penumpang_list as $idx => $penumpang): ?>
                                <div class="passenger-item">
                                    <strong><?php echo ($idx + 1) . '. ' . $penumpang['nama_penumpang']; ?></strong>
                                    <p>Jenis Kelamin: <?php echo ucfirst($penumpang['jenis_kelamin']); ?> | Umur: <?php echo $penumpang['umur']; ?> tahun</p>
                                    <p>No. Identitas: <?php echo $penumpang['nomor_identitas']; ?> (<?php echo $penumpang['jenis_identitas']; ?>)</p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    
                    <h4 class="ticket-section-title">Rincian pembayaran</h4>
                    <div class="payment-summary">
                        <div class="payment-row">
                            <span>Dewasa (<?php echo $booking_detail['jumlah_dewasa']; ?> × <?php echo format_currency($booking_detail['harga_dewasa']); ?>)</span>
                            <strong><?php echo format_currency($booking_detail['jumlah_dewasa'] * $booking_detail['harga_dewasa']); ?></strong>
                        </div>
                        <div class="payment-row payment-meta">
                            <span>Pajak & Biaya</span>
                            <strong>Termasuk</strong>
                        </div>
                        <div class="payment-row payment-total">
                            <span>Total</span>
                            <span><?php echo format_currency($booking_detail['total_harga']); ?></span>
                        </div>
                    </div>
                    
                    <?php if ($booking_detail['status_pembayaran'] === 'pending'): ?>
                        <div class="payment-warning">
                            <strong>⚠️ Pembayaran Belum Lunas</strong>
                            <p>Silakan lakukan pembayaran sebelum tanggal perjalanan untuk mengkonfirmasi tiket Anda.</p>
                        </div>
                    <?php endif; ?>
                    
                    <a href="tiket-saya.php" class="btn btn-secondary ticket-back">← Kembali ke daftar</a>
                </div>
            <?php else: ?>
                <!-- List View -->
                <div class="filter-buttons">
                    <a href="tiket-saya.php" class="btn <?php echo !$filter ? 'btn-primary' : 'btn-secondary'; ?>" <?php echo !$filter ? 'aria-current="page"' : ''; ?>>Semua</a>
                    <a href="tiket-saya.php?filter=pending" class="btn <?php echo $filter === 'pending' ? 'btn-primary' : 'btn-secondary'; ?>" <?php echo $filter === 'pending' ? 'aria-current="page"' : ''; ?>>Pending</a>
                    <a href="tiket-saya.php?filter=paid" class="btn <?php echo $filter === 'paid' ? 'btn-primary' : 'btn-secondary'; ?>" <?php echo $filter === 'paid' ? 'aria-current="page"' : ''; ?>>Lunas</a>
                    <a href="tiket-saya.php?filter=upcoming" class="btn <?php echo $filter === 'upcoming' ? 'btn-primary' : 'btn-secondary'; ?>" <?php echo $filter === 'upcoming' ? 'aria-current="page"' : ''; ?>>Perjalanan Mendatang</a>
                    <a href="tiket-saya.php?filter=completed" class="btn <?php echo $filter === 'completed' ? 'btn-primary' : 'btn-secondary'; ?>" <?php echo $filter === 'completed' ? 'aria-current="page"' : ''; ?>>Selesai</a>
                </div>
                
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <div class="bookings-grid">
                        <?php while ($booking = mysqli_fetch_assoc($result)): ?>
                            <div class="booking-card">
                                <div class="booking-header">
                                    <strong><?php echo $booking['booking_code']; ?></strong>
                                    <span class="badge badge-<?php echo $booking['status_pembayaran']; ?>">
                                        <?php echo ucfirst($booking['status_pembayaran']); ?>
                                    </span>
                                </div>
                                <div class="booking-body">
                                    <p><strong>Kereta:</strong> <?php echo $booking['nama_kereta']; ?></p>
                                    <p><strong>Tujuan:</strong> <?php echo $booking['nama_stasiun']; ?></p>
                                    <p><strong>Berangkat:</strong> <?php echo $booking['waktu_berangkat']; ?></p>
                                    <p><strong>Tanggal:</strong> <?php echo format_date($booking['tanggal_perjalanan']); ?></p>
                                    <p><strong>Penumpang:</strong> <?php echo $booking['jumlah_penumpang']; ?> orang</p>
                                    <p><strong>Total:</strong> <?php echo format_currency($booking['total_harga']); ?></p>
                                </div>
                                <div class="booking-footer">
                                    <a href="tiket-saya.php?booking_id=<?php echo $booking['id']; ?>" class="btn btn-primary">Lihat detail</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="booking-empty">
                        <div class="es-icon" aria-hidden="true">🎟️</div>
                        <h3>Belum ada tiket</h3>
                        <p>Pemesanan Anda akan tampil di sini setelah dibuat.</p>
                        <a href="cari-tiket.php" class="btn btn-primary">Cari tiket</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </main>
        
        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2026 Sistem Pemesanan Tiket Kereta Api. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
