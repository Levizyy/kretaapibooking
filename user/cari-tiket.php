<?php
require '../includes/config.php';
/** @var mysqli $conn */
check_user_login();
$user_id = $_SESSION['user_id'];
$search_from = $_GET['from'] ?? '';
$search_to   = $_GET['to']   ?? '';
$search_date = $_GET['date'] ?? '';
$query = "SELECT r.*,k.nama_kereta,k.jenis_kereta,s1.nama_stasiun as awal,s2.nama_stasiun as akhir FROM rute_kereta r JOIN kereta_api k ON r.kereta_id=k.id JOIN stasiun s1 ON r.stasiun_awal_id=s1.id JOIN stasiun s2 ON r.stasiun_akhir_id=s2.id WHERE r.status='aktif' AND k.status='aktif' AND r.kapasitas_tersedia>0";
if ($search_from) $query .= " AND r.stasiun_awal_id=".(int)$search_from;
if ($search_to)   $query .= " AND r.stasiun_akhir_id=".(int)$search_to;
if ($search_date) $query .= " AND r.tgl_operasi='".sanitize($search_date)."'";
else              $query .= " AND r.tgl_operasi>=CURDATE()";
$query .= " ORDER BY r.tgl_operasi ASC, r.waktu_berangkat ASC";
$results = mysqli_query($conn, $query);
$stasiun_result = mysqli_query($conn, "SELECT id,nama_stasiun FROM stasiun ORDER BY kota,nama_stasiun");
?><!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Cari Tiket — Kereta Api</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,300;400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/user-style.css">
</head><body>
<nav class="navbar">
  <div class="navbar-content">
    <div class="nav-brand"><div class="nav-logo-box">🚄</div><div class="nav-brand-text"><h2>Kereta Api</h2><span>Ticket System</span></div></div>
    <div class="nav-menu">
      <a href="index.php" class="nav-item">🏠 Dashboard</a>
      <a href="cari-tiket.php" class="nav-item active">🔍 Cari Tiket</a>
      <a href="tiket-saya.php" class="nav-item">🎫 Tiket Saya</a>
      <a href="profile.php" class="nav-item">👤 Profil</a>
      <a href="../logout.php" class="nav-item logout">⏻ Logout</a>
    </div>
  </div>
</nav>
<main class="page-wrap">
  <div class="page-title">🔍 Cari & Pesan Tiket</div>
  <div class="page-subtitle">Temukan jadwal kereta yang sesuai dengan perjalanan Anda</div>

  <div class="search-card">
    <div class="search-card-title">📅 Filter Pencarian</div>
    <form method="GET" class="search-form">
      <div class="form-group">
        <label>Stasiun Awal</label>
        <select name="from">
          <option value="">-- Semua Stasiun --</option>
          <?php mysqli_data_seek($stasiun_result,0); while ($s=mysqli_fetch_assoc($stasiun_result)): ?>
          <option value="<?= $s['id'] ?>" <?= $search_from==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['nama_stasiun']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Stasiun Tujuan</label>
        <select name="to">
          <option value="">-- Semua Stasiun --</option>
          <?php mysqli_data_seek($stasiun_result,0); while ($s=mysqli_fetch_assoc($stasiun_result)): ?>
          <option value="<?= $s['id'] ?>" <?= $search_to==$s['id']?'selected':'' ?>><?= htmlspecialchars($s['nama_stasiun']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Tanggal Perjalanan</label>
        <input type="date" name="date" value="<?= htmlspecialchars($search_date) ?>" min="<?= date('Y-m-d') ?>">
      </div>
      <div style="display:flex;gap:8px;align-items:flex-end">
        <button type="submit" class="btn btn-primary btn-lg">Cari</button>
        <a href="cari-tiket.php" class="btn btn-secondary btn-lg">Reset</a>
      </div>
    </form>
  </div>

  <?php $count = mysqli_num_rows($results); ?>
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
    <div class="page-title" style="font-size:18px;margin:0">
      <?= $count > 0 ? "✅ $count Kereta Tersedia" : "Hasil Pencarian" ?>
    </div>
    <?php if ($count > 0): ?><div style="font-size:13px;color:var(--txt3)">Diurutkan berdasarkan tanggal & jam berangkat</div><?php endif; ?>
  </div>

  <?php if ($count > 0): ?>
  <div class="trains-grid">
  <?php while ($train = mysqli_fetch_assoc($results)):
    $dur_h = floor($train['durasi_perjalanan']/60);
    $dur_m = $train['durasi_perjalanan']%60;
  ?>
    <div class="train-card">
      <div class="train-card-inner">
        <div class="train-top">
          <div>
            <div class="train-name"><?= htmlspecialchars($train['nama_kereta']) ?></div>
            <div class="train-type-badge"><?= htmlspecialchars($train['jenis_kereta']) ?></div>
          </div>
          <div>
            <div class="train-price-label">Mulai dari</div>
            <div class="train-price"><?= format_currency($train['harga_dewasa']) ?></div>
            <div style="font-size:11px;color:var(--txt4);text-align:right">per dewasa</div>
          </div>
        </div>

        <div class="train-route-row">
          <div class="trr-station">
            <div class="trr-time"><?= $train['waktu_berangkat'] ?></div>
            <div class="trr-name"><?= htmlspecialchars($train['awal']) ?></div>
          </div>
          <div class="trr-mid">
            <div class="trr-line"></div>
            <div class="trr-dur"><?= $dur_h ?>j <?= $dur_m ?>m</div>
          </div>
          <div class="trr-station" style="text-align:right">
            <div class="trr-time"><?= $train['waktu_tiba'] ?></div>
            <div class="trr-name"><?= htmlspecialchars($train['akhir']) ?></div>
          </div>
        </div>

        <div class="train-bottom">
          <div>
            <div class="train-prices">
              <div class="tp-item"><div class="tp-lbl">Dewasa</div><div class="tp-val"><?= format_currency($train['harga_dewasa']) ?></div></div>
              <div class="tp-item"><div class="tp-lbl">Anak</div><div class="tp-val"><?= format_currency($train['harga_anak']) ?></div></div>
              <div class="tp-item"><div class="tp-lbl">Bayi</div><div class="tp-val"><?= format_currency($train['harga_bayi']) ?></div></div>
            </div>
            <div style="font-size:11px;color:var(--txt3);margin-top:6px">📅 <?= format_date($train['tgl_operasi']) ?> &nbsp;·&nbsp; <span class="seats-ok"><?= $train['kapasitas_tersedia'] ?> kursi</span></div>
          </div>
          <a href="pesan-tiket.php?rute_id=<?= $train['id'] ?>" class="btn btn-primary">Pesan →</a>
        </div>
      </div>
    </div>
  <?php endwhile; ?>
  </div>
  <?php else: ?>
  <div class="empty-state">
    <div class="empty-icon">🔍</div>
    <div class="empty-title">Tidak ada kereta tersedia</div>
    <div class="empty-desc">Coba ubah filter pencarian atau pilih tanggal lain</div>
    <a href="cari-tiket.php" class="btn btn-primary" style="margin-top:16px">Reset Pencarian</a>
  </div>
  <?php endif; ?>
</main>
<footer class="footer"><strong>Kereta Api</strong> · Sistem Pemesanan Tiket Online &copy; <?= date('Y') ?></footer>
</body></html>
