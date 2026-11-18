<?php
require_once __DIR__ . '/../includes/config.php';
/** @var mysqli $conn */
check_admin_login();

// Get statistics
$res = mysqli_query($conn, "SELECT COUNT(*) as count FROM booking");
$total_booking = $res ? mysqli_fetch_assoc($res)['count'] : 0;

$res = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM booking WHERE status_pembayaran = 'paid'");
$total_revenue = $res ? (mysqli_fetch_assoc($res)['total'] ?? 0) : 0;

$res = mysqli_query($conn, "SELECT COUNT(*) as count FROM booking WHERE status_pembayaran = 'pending'");
$pending_payment = $res ? mysqli_fetch_assoc($res)['count'] : 0;

$res = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'user'");
$total_users = $res ? mysqli_fetch_assoc($res)['count'] : 0;

// Booking by destination
$destination_query = "
    SELECT s.nama_stasiun as tujuan, COUNT(b.id) as jumlah_booking
    FROM booking b
    JOIN rute_kereta r ON b.rute_kereta_id = r.id
    JOIN stasiun s ON r.stasiun_akhir_id = s.id
    GROUP BY r.stasiun_akhir_id ORDER BY jumlah_booking DESC";
$destination_result = mysqli_query($conn, $destination_query);

// Revenue by month
$revenue_query = "
    SELECT DATE_FORMAT(tanggal_booking, '%m/%Y') as bulan, SUM(total_harga) as revenue
    FROM booking WHERE status_pembayaran = 'paid'
    GROUP BY DATE_FORMAT(tanggal_booking, '%Y-%m')
    ORDER BY tanggal_booking DESC LIMIT 6";
$revenue_result = mysqli_query($conn, $revenue_query);

// Status distribution
$status_query = "SELECT status_pembayaran, COUNT(*) as count FROM booking GROUP BY status_pembayaran";
$status_result = mysqli_query($conn, $status_query);

// Recent bookings
$recent_query = "
    SELECT b.*, u.full_name, s.nama_stasiun
    FROM booking b
    JOIN users u ON b.user_id = u.id
    JOIN rute_kereta r ON b.rute_kereta_id = r.id
    JOIN stasiun s ON r.stasiun_akhir_id = s.id
    ORDER BY b.tanggal_booking DESC LIMIT 5";
$recent_result = mysqli_query($conn, $recent_query);

// Prepare chart data
$destination_labels = []; $destination_data = [];
while ($row = mysqli_fetch_assoc($destination_result)) {
    $destination_labels[] = $row['tujuan'];
    $destination_data[]   = $row['jumlah_booking'];
}

$revenue_labels = []; $revenue_data = [];
while ($row = mysqli_fetch_assoc($revenue_result)) {
    $revenue_labels[] = $row['bulan'];
    $revenue_data[]   = $row['revenue'];
}

$status_labels = []; $status_data = []; $status_colors = [];
while ($row = mysqli_fetch_assoc($status_result)) {
    $status_labels[] = ucfirst($row['status_pembayaran']);
    $status_data[]   = $row['count'];
    if ($row['status_pembayaran'] === 'paid')      $status_colors[] = '#22c55e';
    elseif ($row['status_pembayaran'] === 'pending') $status_colors[] = '#a78bfa';
    else                                              $status_colors[] = '#fb7185';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dasbor — Admin Kereta Api</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Figtree:ital,wght@0,300;0,400;0,500;0,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="../assets/js/charts.js"></script>
</head>
<body>
<div class="admin-container">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="brand">
                <div class="brand-icon">🚄</div>
                <div>
                    <h1>Kereta Api</h1>
                </div>
            </div>
            <div class="tagline">Panel Admin</div>
        </div>

        <nav class="sidebar-menu">
            <div class="menu-section-label">Utama</div>
            <a href="index.php" class="menu-item active"><span class="icon">📊</span> Dasbor</a>
            <a href="booking.php" class="menu-item"><span class="icon">🎫</span> Pemesanan</a>

            <div class="menu-section-label">Data Master</div>
            <a href="kereta.php" class="menu-item"><span class="icon">🚆</span> Kereta</a>
            <a href="rute.php" class="menu-item"><span class="icon">🧭</span> Rute Kereta</a>
            <a href="stasiun.php" class="menu-item"><span class="icon">🏤</span> Stasiun</a>
            <a href="pengguna.php" class="menu-item"><span class="icon">👥</span> Pengguna</a>

            <div class="menu-section-label">Laporan</div>
            <a href="laporan.php" class="menu-item"><span class="icon">📈</span> Laporan &amp; Analisis</a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-info">
                <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?></div>
                <div class="user-meta">
                    <div class="name"><?php echo $_SESSION['full_name']; ?></div>
                    <div class="role">Administrator</div>
                </div>
            </div>
            <a href="../logout.php" class="logout-btn">⏻ Keluar</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="top-bar">
            <h2>Dasbor</h2>
            <div class="top-bar-right">
                <span class="date-time" id="dateTime"></span>
            </div>
        </header>

        <div class="content">
            <!-- Stats -->
            <section class="stats-section">
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#16a085,#2dd4bf)">🎫</div>
                    <div class="stat-content">
                        <h3>Total Pemesanan</h3>
                        <p class="stat-value"><?php echo number_format($total_booking); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#f97316,#f29e4c)">💰</div>
                    <div class="stat-content">
                        <h3>Total Pendapatan</h3>
                        <p class="stat-value"><?php echo format_currency($total_revenue); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#38bdf8,#22d3ee)">⏳</div>
                    <div class="stat-content">
                        <h3>Pembayaran Tertunda</h3>
                        <p class="stat-value"><?php echo number_format($pending_payment); ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:linear-gradient(135deg,#2dd4bf,#16a085)">👥</div>
                    <div class="stat-content">
                        <h3>Total Pengguna</h3>
                        <p class="stat-value"><?php echo number_format($total_users); ?></p>
                    </div>
                </div>
            </section>

            <!-- Charts -->
            <section class="charts-section">
                <div class="chart-card">
                    <h3>Tujuan Paling Ramai</h3>
                    <div class="chart-container" style="position:relative;height:280px">
                        <canvas id="destinationChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <h3>Status Pembayaran</h3>
                    <div class="chart-container" style="position:relative;height:280px">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </section>

            <section class="charts-section">
                <div class="chart-card full-width">
                    <h3>Trend Pendapatan — 6 Bulan Terakhir</h3>
                    <div class="chart-container" style="position:relative;height:320px">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </section>

            <!-- Recent Bookings -->
            <section class="recent-section">
                <h3>Booking Terbaru</h3>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Kode Booking</th>
                            <th>Pengguna</th>
                            <th>Tujuan</th>
                            <th>Penumpang</th>
                            <th>Total Harga</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($recent_result)): ?>
                        <tr>
                            <td><strong><?php echo $row['booking_code']; ?></strong></td>
                            <td><?php echo $row['full_name']; ?></td>
                            <td><?php echo $row['nama_stasiun']; ?></td>
                            <td><?php echo $row['jumlah_penumpang']; ?> orang</td>
                            <td><?php echo format_currency($row['total_harga']); ?></td>
                            <td><span class="badge badge-<?php echo $row['status_pembayaran']; ?>"><?php echo ucfirst($row['status_pembayaran']); ?></span></td>
                            <td><?php echo format_datetime($row['tanggal_booking']); ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
</div>

<script>
function updateDateTime() {
    document.getElementById('dateTime').textContent = new Date().toLocaleString('id-ID',{
        weekday:'long', year:'numeric', month:'long', day:'numeric', hour:'2-digit', minute:'2-digit'
    });
}
updateDateTime();
setInterval(updateDateTime, 1000);

initDashboardCharts(
    <?= json_encode($destination_labels) ?>,
    <?= json_encode($destination_data) ?>,
    <?= json_encode($status_labels) ?>,
    <?= json_encode($status_data) ?>,
    <?= json_encode($revenue_labels) ?>,
    <?= json_encode($revenue_data) ?>
);
</script>
</body>
</html>

