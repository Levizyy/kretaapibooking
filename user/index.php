<?php
require '../includes/config.php';
/** @var mysqli $conn */
check_user_login();
$user_id = $_SESSION['user_id'];
$res = mysqli_query($conn, "SELECT COUNT(*) as c FROM booking WHERE user_id=$user_id"); $total_bookings = mysqli_fetch_assoc($res)['c'];
$res = mysqli_query($conn, "SELECT SUM(total_harga) as t FROM booking WHERE user_id=$user_id AND status_pembayaran='paid'"); $total_spent = mysqli_fetch_assoc($res)['t'] ?? 0;
$res = mysqli_query($conn, "SELECT COUNT(*) as c FROM booking WHERE user_id=$user_id AND tanggal_perjalanan>=CURDATE() AND status_pembayaran='paid'"); $upcoming = mysqli_fetch_assoc($res)['c'];
$recent = mysqli_query($conn, "SELECT b.*,s.nama_stasiun,k.nama_kereta,r.waktu_berangkat,s1.nama_stasiun as awal FROM booking b JOIN rute_kereta r ON b.rute_kereta_id=r.id JOIN kereta_api k ON r.kereta_id=k.id JOIN stasiun s ON r.stasiun_akhir_id=s.id JOIN stasiun s1 ON r.stasiun_awal_id=s1.id WHERE b.user_id=$user_id ORDER BY b.tanggal_booking DESC LIMIT 5");
?><!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Dashboard — Kereta Api</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/user-style.css">
</head><body>
<nav class="navbar">
  <div class="navbar-content">
    <div class="nav-brand"><div class="nav-logo-box">🚄</div><div class="nav-brand-text"><h2>Kereta Api</h2><span>Ticket System</span></div></div>
    <div class="nav-menu">
      <a href="index.php" class="nav-item active">◈ Dashboard</a>
      <a href="cari-tiket.php" class="nav-item">→ Cari & Pesan Tiket</a>
      <a href="tiket-saya.php" class="nav-item">🎫 Tiket Saya</a>
      <a href="profile.php" class="nav-item">◎ Profil</a>
      <a href="../logout.php" class="nav-item logout">⏻ Logout</a>
    </div>
  </div>
</nav>
<main class="page-wrap">
  <div class="hero-banner">
    <h2>Halo, <?= htmlspecialchars($_SESSION['full_name']) ?>! 👋</h2>
    <p>Rencanakan perjalanan Anda berikutnya dengan mudah dan cepat.</p>
    <div class="hero-cta">
      <a href="cari-tiket.php" class="btn-white">🔍 Cari Tiket Sekarang</a>
      <a href="tiket-saya.php" class="btn-ghost">🎫 Lihat Tiket Saya</a>
    </div>
    <div class="hero-emoji">🚄</div>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="stat-icon blue">🎫</div><div><div class="stat-label">Total Booking</div><div class="stat-value"><?= $total_bookings ?></div><div class="stat-note">Semua pesanan</div></div></div>
    <div class="stat-card"><div class="stat-icon green">💰</div><div><div class="stat-label">Total Pengeluaran</div><div class="stat-value" style="font-size:18px"><?= format_currency($total_spent) ?></div><div class="stat-note">Tiket terbayar</div></div></div>
    <div class="stat-card"><div class="stat-icon amber">✈️</div><div><div class="stat-label">Trip Mendatang</div><div class="stat-value"><?= $upcoming ?></div><div class="stat-note">Perjalanan aktif</div></div></div>
  </div>

  <div class="quick-actions">
    <a href="cari-tiket.php" class="quick-action"><div class="qa-icon">🔍</div><div class="qa-label">Cari Tiket</div></a>
    <a href="tiket-saya.php" class="quick-action"><div class="qa-icon">🎫</div><div class="qa-label">Tiket Saya</div></a>
    <a href="tiket-saya.php?filter=upcoming" class="quick-action"><div class="qa-icon">🗓️</div><div class="qa-label">Trip Mendatang</div></a>
    <a href="profile.php" class="quick-action"><div class="qa-icon">⚙️</div><div class="qa-label">Kelola Profil</div></a>
  </div>

  <div class="section-card">
    <div class="card-header">
      <h3>🕐 Riwayat Pemesanan Terbaru</h3>
      <a href="tiket-saya.php" class="btn btn-sm btn-secondary">Lihat Semua →</a>
    </div>
    <div style="overflow-x:auto">
      <table class="data-table">
        <thead><tr><th>Kode</th><th>Rute</th><th>Kereta</th><th>Berangkat</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
        <?php if (mysqli_num_rows($recent) > 0): ?>
          <?php while ($b = mysqli_fetch_assoc($recent)): ?>
          <tr onclick="window.location='tiket-saya.php?booking_id=<?= $b['id'] ?>'" style="cursor:pointer">
            <td class="fw"><?= htmlspecialchars($b['booking_code']) ?></td>
            <td><?= htmlspecialchars($b['awal']) ?> → <?= htmlspecialchars($b['nama_stasiun']) ?></td>
            <td><?= htmlspecialchars($b['nama_kereta']) ?></td>
            <td><?= $b['waktu_berangkat'] ?> · <?= format_date($b['tanggal_perjalanan']) ?></td>
            <td class="fw"><?= format_currency($b['total_harga']) ?></td>
            <td><span class="badge badge-<?= $b['status_pembayaran'] ?>"><?= ucfirst($b['status_pembayaran']) ?></span></td>
          </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="6" style="text-align:center;color:var(--txt3);padding:32px">Belum ada pemesanan. <a href="cari-tiket.php" style="color:var(--blue);font-weight:600">Cari tiket sekarang →</a></td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<footer class="footer"><strong>Kereta Api</strong> · Sistem Pemesanan Tiket Online &copy; <?= date('Y') ?></footer>
</body></html>
