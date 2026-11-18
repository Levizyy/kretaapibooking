<?php
require 'includes/config.php';
/** @var mysqli $conn */

if (isset($_SESSION['user_id'])) {
    $base = get_base_url();
    if ($_SESSION['role'] === 'admin') header("Location: {$base}/admin/");
    else header("Location: {$base}/user/");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = md5($_POST['password'] ?? '');
    $q = "SELECT * FROM users WHERE (username='$username' OR email='$username') AND status='active'";
    $res = mysqli_query($conn, $q);
    if ($res && $row = mysqli_fetch_assoc($res)) {
        if ($row['password'] === $password) {
            $_SESSION['user_id']   = $row['id'];
            $_SESSION['username']  = $row['username'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['role']      = $row['role'];
            $base = get_base_url();
            if ($row['role'] === 'admin') header("Location: {$base}/admin/");
            else header("Location: {$base}/user/");
            exit();
        }
    }
    $error = 'Username atau password salah.';
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Masuk — Kereta Api</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Figtree:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#eef6ff;--surface:#ffffff;--border:rgba(59,130,246,.18);
  --amber:#2563eb;--amber-l:#3b82f6;
  --text-1:#0f172a;--text-2:#475569;--text-3:#64748b;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{height:100%}
body{
  font-family:'Figtree',sans-serif;min-height:100vh;background:var(--bg);
  display:grid;grid-template-columns:1fr 1fr;
  -webkit-font-smoothing:antialiased;overflow:hidden;
}
.left-panel{
  position:relative;
  background:linear-gradient(160deg,#eef6ff 0%,#dbeafe 50%,#e0ecff 100%);
  display:flex;flex-direction:column;justify-content:flex-end;
  padding:48px;overflow:hidden;
}
.left-panel::before{
  content:'';position:absolute;inset:0;
  background:repeating-linear-gradient(-15deg,transparent,transparent 40px,rgba(14,165,233,.06) 40px,rgba(14,165,233,.06) 41px);
}
.orb{
  position:absolute;width:500px;height:500px;border-radius:50%;
  background:radial-gradient(circle,rgba(14,165,233,.2) 0%,transparent 70%);
  top:-100px;right:-100px;pointer-events:none;
}
.train-art{
  position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
  font-size:160px;opacity:.08;filter:blur(2px);user-select:none;pointer-events:none;
}
.panel-content{position:relative;z-index:1;}
.panel-content .pre-title{
  font-size:10px;color:var(--amber);text-transform:uppercase;
  letter-spacing:3px;font-weight:600;margin-bottom:14px;
  display:flex;align-items:center;gap:8px;
}
.panel-content .pre-title::before{content:'';width:28px;height:1px;background:var(--amber);}
.panel-content h2{
  font-family:'Syne',sans-serif;font-size:38px;font-weight:800;
  color:var(--text-1);line-height:1.12;letter-spacing:-1.5px;margin-bottom:16px;
}
.panel-content h2 em{color:var(--amber);font-style:normal;}
.panel-content p{font-size:14px;color:var(--text-2);max-width:300px;line-height:1.7;margin-bottom:32px;}
.features{display:flex;flex-direction:column;gap:10px;}
.feature-item{display:flex;align-items:center;gap:12px;font-size:13px;color:var(--text-2);}
.feat-icon{
  width:32px;height:32px;border-radius:8px;
  background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2);
  display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;
}
.right-panel{
  background:var(--bg);display:flex;align-items:center;justify-content:center;
  padding:40px 48px;position:relative;overflow-y:auto;
}
.right-panel::before{
  content:'';position:absolute;top:0;left:0;bottom:0;width:1px;
  background:linear-gradient(to bottom,transparent,var(--border),transparent);
}
.login-box{width:100%;max-width:380px;animation:fadeUp .4s cubic-bezier(.4,0,.2,1);}
@keyframes fadeUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.login-logo{display:flex;align-items:center;gap:12px;margin-bottom:36px;}
.logo-mark{
  width:44px;height:44px;border-radius:12px;
  background:linear-gradient(135deg,var(--amber),#d97706);
  display:flex;align-items:center;justify-content:center;font-size:22px;
  box-shadow:0 0 24px rgba(245,158,11,.4);flex-shrink:0;
}
.logo-text h1{font-family:'Syne',sans-serif;font-size:16px;font-weight:800;color:var(--text-1);letter-spacing:-.3px;line-height:1;}
.logo-text span{font-size:9.5px;color:var(--amber);text-transform:uppercase;letter-spacing:2.5px;font-weight:600;}
.login-heading{font-family:'Syne',sans-serif;font-size:24px;font-weight:800;color:var(--text-1);letter-spacing:-.5px;margin-bottom:4px;}
.login-sub{font-size:13.5px;color:var(--text-2);margin-bottom:28px;}
.alert{padding:12px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px;border-left:3px solid;}
.alert-error{background:rgba(239,68,68,.1);color:#f87171;border-left-color:#ef4444;}
.alert-success{background:rgba(16,185,129,.1);color:#34d399;border-left-color:#10b981;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;}
.form-group input{
  width:100%;background:var(--surface);border:1.5px solid var(--border);
  border-radius:9px;padding:12px 14px;font-size:14px;color:var(--text-1);
  font-family:'Figtree',sans-serif;transition:all .2s;outline:none;
}
.form-group input::placeholder{color:var(--text-3);}
.form-group input:focus{border-color:var(--amber);box-shadow:0 0 0 3px rgba(37,99,235,.12);background:#ffffff;}
.btn-submit{
  width:100%;padding:13px;border-radius:9px;
  background:linear-gradient(135deg,var(--amber),var(--amber-l));
  color:#ffffff;font-size:15px;font-weight:700;border:none;cursor:pointer;
  font-family:'Syne',sans-serif;letter-spacing:-.2px;margin-top:6px;
  transition:all .2s;box-shadow:0 4px 20px rgba(37,99,235,.24);
}
.btn-submit:hover{transform:translateY(-1px);box-shadow:0 6px 28px rgba(37,99,235,.32);}
.btn-submit:active{transform:translateY(0);}
.auth-link{text-align:center;font-size:13.5px;color:var(--text-2);margin-top:18px;}
.auth-link a{color:var(--amber);font-weight:600;text-decoration:none;}
.auth-link a:hover{color:var(--amber-l);}
.divider{display:flex;align-items:center;gap:12px;margin:22px 0 16px;}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border);}
.divider span{font-size:10px;color:var(--text-3);text-transform:uppercase;letter-spacing:1px;white-space:nowrap;}
.demo-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.demo-box{
  background:#13161e;border:1px solid var(--border);
  border-radius:9px;padding:12px;cursor:pointer;transition:all .2s;
}
.demo-box:hover{border-color:rgba(245,158,11,.3);background:rgba(245,158,11,.05);}
.demo-box .db-role{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;}
.demo-box .db-role.user{color:var(--amber);}
.demo-box .db-role.admin{color:#60a5fa;}
.demo-box .db-cred{color:var(--text-2);font-family:'DM Mono',monospace;font-size:11.5px;}
@media(max-width:768px){
  body{grid-template-columns:1fr;overflow:auto;}
  .left-panel{display:none;}
  .right-panel{padding:32px 24px;}
}
</style>
</head>
<body>
<div class="left-panel">
  <div class="orb"></div>
  <div class="train-art">🚄</div>
  <div class="panel-content">
    <p class="pre-title">Sistem Tiket Online</p>
    <h2>Perjalanan<br><em>Lebih Mudah</em><br>&amp; Nyaman</h2>
    <p>Platform pemesanan tiket kereta api terpercaya. Kelola rute, penumpang, dan laporan dalam satu dashboard.</p>
    <div class="features">
      <div class="feature-item"><div class="feat-icon">🎫</div> Manajemen booking real-time</div>
      <div class="feature-item"><div class="feat-icon">📊</div> Laporan &amp; analisis lengkap</div>
      <div class="feature-item"><div class="feat-icon">🛤️</div> Kelola rute &amp; jadwal kereta</div>
    </div>
  </div>
</div>

<div class="right-panel">
  <div class="login-box">
    <div class="login-logo">
      <div class="logo-mark">🚄</div>
      <div class="logo-text"><h1>Kereta Api</h1><span>Ticket System</span></div>
    </div>
    <h2 class="login-heading">Selamat Datang</h2>
    <p class="login-sub">Masuk ke akun Anda untuk melanjutkan</p>

    <?php if ($error): ?>
      <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success">✓ Registrasi berhasil! Silakan login.</div>
    <?php endif; ?>

    <form method="POST">
      <div class="form-group">
        <label>Username / Email</label>
        <input type="text" name="username" placeholder="Masukkan username atau email"
               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required autocomplete="username">
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
      </div>
      <button type="submit" class="btn-submit">Masuk →</button>
    </form>

    <p class="auth-link">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>

    <div class="divider"><span>Akun baru?</span></div>
    <p class="auth-link">Buat akun melalui <a href="register.php">formulir pendaftaran</a>.</p>
  </div>
</div>
</body>
</html>
