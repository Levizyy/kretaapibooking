<?php
require_once __DIR__ . '/../includes/config.php';
/** @var mysqli $conn */
check_admin_login();

// Revenue by month
$revenue_labels = []; $revenue_data = [];
$result = mysqli_query($conn, "
    SELECT DATE_FORMAT(tanggal_booking, '%m/%Y') as bulan, SUM(total_harga) as revenue
    FROM booking WHERE status_pembayaran = 'paid'
    GROUP BY YEAR(tanggal_booking), MONTH(tanggal_booking)
    ORDER BY tanggal_booking DESC LIMIT 12");
$temp = [];
while ($row = mysqli_fetch_assoc($result)) $temp[] = $row;
foreach (array_reverse($temp) as $row) {
    $revenue_labels[] = $row['bulan'];
    $revenue_data[]   = $row['revenue'];
}

// Top destinations
$dest_labels = []; $dest_data = [];
$result = mysqli_query($conn, "
    SELECT s.nama_stasiun, COUNT(b.id) as jumlah
    FROM booking b JOIN rute_kereta r ON b.rute_kereta_id = r.id
    JOIN stasiun s ON r.stasiun_akhir_id = s.id
    GROUP BY r.stasiun_akhir_id ORDER BY jumlah DESC LIMIT 10");
while ($row = mysqli_fetch_assoc($result)) { $dest_labels[] = $row['nama_stasiun']; $dest_data[] = $row['jumlah']; }

// Status distribution
$status_labels = []; $status_data = []; $status_colors = [];
$result = mysqli_query($conn, "SELECT status_pembayaran, COUNT(*) as count FROM booking GROUP BY status_pembayaran");
while ($row = mysqli_fetch_assoc($result)) {
    $status_labels[] = ucfirst($row['status_pembayaran']);
    $status_data[]   = $row['count'];
    if ($row['status_pembayaran'] === 'paid')      $status_colors[] = '#22c55e';
    elseif ($row['status_pembayaran'] === 'pending') $status_colors[] = '#a78bfa';
    else                                              $status_colors[] = '#fb7185';
}

// Train type
$train_labels = []; $train_data = [];
$result = mysqli_query($conn, "
    SELECT k.jenis_kereta, COUNT(b.id) as jumlah
    FROM booking b JOIN rute_kereta r ON b.rute_kereta_id = r.id
    JOIN kereta_api k ON r.kereta_id = k.id GROUP BY k.jenis_kereta");
while ($row = mysqli_fetch_assoc($result)) { $train_labels[] = $row['jenis_kereta']; $train_data[] = $row['jumlah']; }

// Monthly passenger breakdown
$monthly_labels = []; $monthly_dewasa = []; $monthly_anak = []; $monthly_bayi = [];
$mp_result = mysqli_query($conn, "
    SELECT DATE_FORMAT(tanggal_booking, '%b %Y') as bulan, MONTH(tanggal_booking) as m,
           SUM(jumlah_dewasa) as dewasa, SUM(jumlah_anak) as anak, SUM(jumlah_bayi) as bayi
    FROM booking GROUP BY YEAR(tanggal_booking), MONTH(tanggal_booking)
    ORDER BY YEAR(tanggal_booking), MONTH(tanggal_booking) LIMIT 6");
while ($row = mysqli_fetch_assoc($mp_result)) {
    $monthly_labels[] = $row['bulan'];
    $monthly_dewasa[] = (int)$row['dewasa'];
    $monthly_anak[]   = (int)$row['anak'];
    $monthly_bayi[]   = (int)$row['bayi'];
}

// Daily bookings
$daily_labels = []; $daily_data = [];
$result = mysqli_query($conn, "
    SELECT DATE_FORMAT(tanggal_booking, '%d-%m') as tanggal, COUNT(*) as jumlah
    FROM booking WHERE tanggal_booking >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(tanggal_booking) ORDER BY tanggal_booking ASC");
while ($row = mysqli_fetch_assoc($result)) { $daily_labels[] = $row['tanggal']; $daily_data[] = $row['jumlah']; }

// Destination labels (top 5)
$destination_labels = []; $destination_data = [];
$dest_result = mysqli_query($conn, "
    SELECT s2.nama_stasiun as tujuan, COUNT(b.id) as jumlah_booking
    FROM booking b JOIN rute_kereta r ON b.rute_kereta_id = r.id
    JOIN stasiun s2 ON r.stasiun_akhir_id = s2.id
    GROUP BY r.stasiun_akhir_id ORDER BY jumlah_booking DESC LIMIT 5");
while ($row = mysqli_fetch_assoc($dest_result)) { $destination_labels[] = $row['tujuan']; $destination_data[] = $row['jumlah_booking']; }

// Summary stats
$res = mysqli_query($conn, "SELECT COUNT(*) as count FROM booking");
$total_booking = mysqli_fetch_assoc($res)['count'];
$res = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM booking WHERE status_pembayaran = 'paid'");
$total_revenue = mysqli_fetch_assoc($res)['total'] ?? 0;
$res_pass = mysqli_query($conn, "SELECT SUM(jumlah_penumpang) as total FROM booking");
$total_passengers = mysqli_fetch_assoc($res_pass)['total'] ?? 0;
$avg_booking_value = $total_booking > 0 ? round($total_revenue / $total_booking) : 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan & Analisis — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Figtree:ital,wght@0,300;0,400;0,500;0,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script src="../assets/js/charts.js"></script>
</head>
<body>
<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-content">
        <header class="top-bar">
            <h2>Laporan &amp; Analisis</h2>
        </header>

        <div class="content">
            <div class="report-header">
                <h2>Dashboard Laporan Penjualan</h2>
                <p>Analisis komprehensif sistem pemesanan tiket kereta api</p>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h4>Total Booking</h4>
                    <div class="value"><?= number_format($total_booking) ?></div>
                </div>
                <div class="summary-card">
                    <h4>Total Revenue</h4>
                    <div class="value"><?= format_currency($total_revenue) ?></div>
                </div>
                <div class="summary-card">
                    <h4>Total Penumpang</h4>
                    <div class="value"><?= number_format($total_passengers) ?></div>
                </div>
                <div class="summary-card">
                    <h4>Rata-rata Nilai Booking</h4>
                    <div class="value"><?= format_currency($avg_booking_value) ?></div>
                </div>
            </div>

            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-wrapper full-width">
                    <h3>📈 Trend Revenue (12 Bulan)</h3>
                    <div class="chart-container" style="position:relative;height:360px">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <div class="chart-wrapper">
                    <h3>🚄 Top 10 Tujuan Paling Ramai</h3>
                    <div class="chart-container" style="position:relative;height:300px">
                        <canvas id="destinationChart"></canvas>
                    </div>
                </div>

                <div class="chart-wrapper">
                    <h3>🎫 Status Pembayaran</h3>
                    <div class="chart-container" style="position:relative;height:300px">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>

                <div class="chart-wrapper">
                    <h3>🚆 Distribusi Jenis Kereta</h3>
                    <div class="chart-container" style="position:relative;height:300px">
                        <canvas id="trainChart"></canvas>
                    </div>
                </div>

                <div class="chart-wrapper">
                    <h3>👥 Distribusi Jenis Penumpang</h3>
                    <div class="chart-container" style="position:relative;height:300px">
                        <canvas id="passengerChart"></canvas>
                    </div>
                </div>

                <div class="chart-wrapper full-width">
                    <h3>📅 Booking Harian (30 Hari Terakhir)</h3>
                    <div class="chart-container" style="position:relative;height:320px">
                        <canvas id="dailyChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
initRevenueChart('revenueChart', <?= json_encode($revenue_labels) ?>, <?= json_encode($revenue_data) ?>);
initDestinationChart('destinationChart', <?= json_encode($destination_labels) ?>, <?= json_encode($destination_data) ?>);
initStatusChart('statusChart', <?= json_encode($status_labels) ?>, <?= json_encode($status_data) ?>);
initTrainTypeChart('trainChart', <?= json_encode($train_labels ?? []) ?>, <?= json_encode($train_data ?? []) ?>);
initPassengerChart('passengerChart',
    <?= json_encode($monthly_labels ?? []) ?>,
    <?= json_encode($monthly_dewasa ?? []) ?>,
    <?= json_encode($monthly_anak ?? []) ?>,
    <?= json_encode($monthly_bayi ?? []) ?>
);
initDailyChart('dailyChart', <?= json_encode($daily_labels) ?>, <?= json_encode($daily_data) ?>);
</script>
</body>
</html>
