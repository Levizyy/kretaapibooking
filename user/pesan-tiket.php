<?php
require '../includes/config.php';
/** @var mysqli $conn */
check_user_login();
$user_id = $_SESSION['user_id'];
$rute_id = (int)($_GET['rute_id'] ?? 0);
$error = '';
if (!$rute_id) { header("Location: cari-tiket.php"); exit(); }
$rq = "SELECT r.*,k.nama_kereta,k.jenis_kereta,s1.nama_stasiun as awal,s2.nama_stasiun as akhir FROM rute_kereta r JOIN kereta_api k ON r.kereta_id=k.id JOIN stasiun s1 ON r.stasiun_awal_id=s1.id JOIN stasiun s2 ON r.stasiun_akhir_id=s2.id WHERE r.id=$rute_id";
$rute = mysqli_fetch_assoc(mysqli_query($conn,$rq));
if (!$rute) { header("Location: cari-tiket.php"); exit(); }
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $jd=(int)$_POST['jumlah_dewasa']; $ja=(int)$_POST['jumlah_anak']; $jb=(int)$_POST['jumlah_bayi'];
    $jp=$jd+$ja+$jb;
    if ($jp===0) { $error="Jumlah penumpang minimal 1!"; }
    elseif ($jp>$rute['kapasitas_tersedia']) { $error="Jumlah penumpang melebihi kapasitas tersedia ({$rute['kapasitas_tersedia']} kursi)!"; }
    else {
        $total=($jd*$rute['harga_dewasa'])+($ja*$rute['harga_anak'])+($jb*$rute['harga_bayi']);
        $code='BK-'.date('Y').'-'.strtoupper(substr(uniqid(),-6)).rand(10,99);
        $ins="INSERT INTO booking(user_id,rute_kereta_id,booking_code,jumlah_penumpang,jumlah_dewasa,jumlah_anak,jumlah_bayi,total_harga,tanggal_perjalanan,status_pembayaran,status_booking) VALUES($user_id,$rute_id,'$code',$jp,$jd,$ja,$jb,$total,'{$rute['tgl_operasi']}','pending','confirmed')";
        if (mysqli_query($conn,$ins)) {
            $bid=mysqli_insert_id($conn);
            if (isset($_POST['penumpang'])&&is_array($_POST['penumpang'])) {
                foreach($_POST['penumpang'] as $p) {
                    if(!empty($p['nama'])) {
                        $nm=sanitize($p['nama']); $jk=sanitize($p['jenis_kelamin']); $umr=(int)$p['umur'];
                        $ni=sanitize($p['nomor_identitas']); $ji=sanitize($p['jenis_identitas']);
                        mysqli_query($conn,"INSERT INTO penumpang(booking_id,nama_penumpang,jenis_kelamin,umur,nomor_identitas,jenis_identitas) VALUES($bid,'$nm','$jk',$umr,'$ni','$ji')");
                    }
                }
            }
            mysqli_query($conn,"UPDATE rute_kereta SET kapasitas_tersedia=kapasitas_tersedia-$jp WHERE id=$rute_id");
            header("Location: tiket-saya.php?booking_id=$bid&success=1"); exit();
        } else { $error="Gagal membuat pemesanan. Silakan coba lagi."; }
    }
}
$dur_h=floor($rute['durasi_perjalanan']/60); $dur_m=$rute['durasi_perjalanan']%60;
?><!DOCTYPE html>
<html lang="id"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Pesan Tiket — Kereta Api</title>
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
  <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px">
    <a href="cari-tiket.php" class="btn btn-sm btn-secondary">← Kembali</a>
    <div class="page-title" style="margin:0">Pesan Tiket Kereta Api</div>
  </div>
  <div class="page-subtitle">Lengkapi detail pemesanan Anda</div>
  <?php if ($error): ?><div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div><?php endif; ?>

  <form method="POST">
  <div class="booking-layout">
    <div>
      <!-- Block 1: Info Rute -->
      <div class="booking-block">
        <div class="block-header"><div class="block-num">1</div><h3>Informasi Kereta</h3></div>
        <div class="block-body">
          <div class="route-info-box">
            <div class="rib-icon">🚄</div>
            <div>
              <div class="rib-title"><?= htmlspecialchars($rute['nama_kereta']) ?> <span style="font-size:12px;color:var(--txt3);font-weight:400">(<?= htmlspecialchars($rute['jenis_kereta']) ?>)</span></div>
              <div class="rib-meta">📍 <?= htmlspecialchars($rute['awal']) ?> → <?= htmlspecialchars($rute['akhir']) ?> &nbsp;·&nbsp; 🕐 <?= $rute['waktu_berangkat'] ?> – <?= $rute['waktu_tiba'] ?> (<?= $dur_h ?>j <?= $dur_m ?>m) &nbsp;·&nbsp; 📅 <?= format_date($rute['tgl_operasi']) ?></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Block 2: Jumlah Penumpang -->
      <div class="booking-block">
        <div class="block-header"><div class="block-num">2</div><h3>Jumlah Penumpang</h3></div>
        <div class="block-body">
          <div class="pax-grid">
            <div class="pax-card">
              <div class="pax-label">Dewasa</div>
              <div class="pax-price"><?= format_currency($rute['harga_dewasa']) ?></div>
              <div class="pax-ctrl">
                <button type="button" class="pax-btn" onclick="changePax('dewasa',-1)">−</button>
                <input type="number" id="jumlah_dewasa" name="jumlah_dewasa" value="1" min="0" max="<?= $rute['kapasitas_tersedia'] ?>" style="display:none">
                <div class="pax-num" id="num-dewasa">1</div>
                <button type="button" class="pax-btn" onclick="changePax('dewasa',1)">+</button>
              </div>
            </div>
            <div class="pax-card">
              <div class="pax-label">Anak (3–12th)</div>
              <div class="pax-price"><?= format_currency($rute['harga_anak']) ?></div>
              <div class="pax-ctrl">
                <button type="button" class="pax-btn" onclick="changePax('anak',-1)">−</button>
                <input type="number" id="jumlah_anak" name="jumlah_anak" value="0" min="0" max="<?= $rute['kapasitas_tersedia'] ?>" style="display:none">
                <div class="pax-num" id="num-anak">0</div>
                <button type="button" class="pax-btn" onclick="changePax('anak',1)">+</button>
              </div>
            </div>
            <div class="pax-card">
              <div class="pax-label">Bayi (0–2th)</div>
              <div class="pax-price"><?= format_currency($rute['harga_bayi']) ?></div>
              <div class="pax-ctrl">
                <button type="button" class="pax-btn" onclick="changePax('bayi',-1)">−</button>
                <input type="number" id="jumlah_bayi" name="jumlah_bayi" value="0" min="0" max="<?= $rute['kapasitas_tersedia'] ?>" style="display:none">
                <div class="pax-num" id="num-bayi">0</div>
                <button type="button" class="pax-btn" onclick="changePax('bayi',1)">+</button>
              </div>
            </div>
          </div>
          <div style="font-size:12px;color:var(--txt3);padding:8px 12px;background:var(--surface2);border-radius:8px;border:1px solid var(--border)">
            🪑 Kursi tersedia: <strong style="color:var(--blue)"><?= $rute['kapasitas_tersedia'] ?> kursi</strong>
          </div>
        </div>
      </div>

      <!-- Block 3: Data Penumpang -->
      <div class="booking-block">
        <div class="block-header"><div class="block-num">3</div><h3>Data Penumpang</h3></div>
        <div class="block-body" id="passenger-forms">
          <div style="color:var(--txt3);font-size:13px;text-align:center;padding:20px">Tambahkan penumpang di atas untuk mengisi data...</div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg">✅ Selesaikan Pemesanan</button>
    </div>

    <!-- Sidebar Summary -->
    <div class="booking-sidebar">
      <div class="summary-card">
        <div class="summary-header"><h4>💰 Ringkasan Harga</h4></div>
        <div class="summary-body">
          <div class="summary-row"><span class="s-label" id="lbl-dewasa">Dewasa × 1</span><span class="s-val" id="val-dewasa"><?= format_currency($rute['harga_dewasa']) ?></span></div>
          <div class="summary-row"><span class="s-label" id="lbl-anak">Anak × 0</span><span class="s-val" id="val-anak">Rp 0</span></div>
          <div class="summary-row"><span class="s-label" id="lbl-bayi">Bayi × 0</span><span class="s-val" id="val-bayi">Rp 0</span></div>
          <div class="summary-row"><span class="s-label">Pajak & Biaya</span><span class="s-val">Termasuk</span></div>
        </div>
        <div style="padding:0 18px 18px">
          <div class="summary-total">
            <div class="st-label">Total Pembayaran</div>
            <div class="st-value" id="total-val"><?= format_currency($rute['harga_dewasa']) ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>
  </form>
</main>
<footer class="footer"><strong>Kereta Api</strong> · Sistem Pemesanan Tiket Online &copy; <?= date('Y') ?></footer>

<script>
const HD=<?= $rute['harga_dewasa'] ?>,HA=<?= $rute['harga_anak'] ?>,HB=<?= $rute['harga_bayi'] ?>;
const MAX=<?= $rute['kapasitas_tersedia'] ?>;
const pax={dewasa:1,anak:0,bayi:0};

function fmt(n){return'Rp '+n.toLocaleString('id-ID')}

function changePax(type,delta){
  const total=pax.dewasa+pax.anak+pax.bayi;
  const newVal=pax[type]+delta;
  if(newVal<0) return;
  if(delta>0 && total>=MAX){alert('Kapasitas penuh!');return;}
  pax[type]=newVal;
  document.getElementById('jumlah_'+type).value=newVal;
  document.getElementById('num-'+type).textContent=newVal;
  updateSummary();
  generateForms();
}

function updateSummary(){
  const td=pax.dewasa*HD,ta=pax.anak*HA,tb=pax.bayi*HB,total=td+ta+tb;
  document.getElementById('lbl-dewasa').textContent='Dewasa × '+pax.dewasa;
  document.getElementById('val-dewasa').textContent=fmt(td);
  document.getElementById('lbl-anak').textContent='Anak × '+pax.anak;
  document.getElementById('val-anak').textContent=fmt(ta);
  document.getElementById('lbl-bayi').textContent='Bayi × '+pax.bayi;
  document.getElementById('val-bayi').textContent=fmt(tb);
  document.getElementById('total-val').textContent=fmt(total);
}

function generateForms(){
  const c=document.getElementById('passenger-forms');
  const total=pax.dewasa+pax.anak+pax.bayi;
  if(total===0){c.innerHTML='<div style="color:var(--txt3);font-size:13px;text-align:center;padding:20px">Tambahkan penumpang di atas untuk mengisi data...</div>';return;}
  c.innerHTML='';
  for(let i=0;i<total;i++){
    const d=document.createElement('div');
    d.className='pax-form-item';
    d.innerHTML=`
      <div class="pfi-header"><div class="pfi-num">${i+1}</div><div class="pfi-title">Penumpang ${i+1}</div></div>
      <div class="form-group"><label>Nama Lengkap</label><input type="text" name="penumpang[${i}][nama]" placeholder="Nama sesuai identitas" required></div>
      <div class="form-row">
        <div class="form-group"><label>Jenis Kelamin</label><select name="penumpang[${i}][jenis_kelamin]" required><option value="">-- Pilih --</option><option value="laki-laki">Laki-laki</option><option value="perempuan">Perempuan</option></select></div>
        <div class="form-group"><label>Umur</label><input type="number" name="penumpang[${i}][umur]" placeholder="Tahun" min="0" max="120"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label>Nomor Identitas</label><input type="text" name="penumpang[${i}][nomor_identitas]" placeholder="NIK / Paspor"></div>
        <div class="form-group"><label>Jenis Identitas</label><select name="penumpang[${i}][jenis_identitas]"><option value="KTP">KTP</option><option value="Paspor">Paspor</option><option value="SIM">SIM</option></select></div>
      </div>`;
    c.appendChild(d);
  }
}
// Init
updateSummary();
generateForms();
</script>
</body></html>
