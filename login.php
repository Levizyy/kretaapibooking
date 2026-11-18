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
:root {
  color-scheme: light;
  --ink: #142b2c;
  --muted: #647875;
  --accent: #12836f;
  --accent-dark: #096b5b;
  --line: #dce8e4;
  --soft: #f3f8f6;
}

*, *::before, *::after { box-sizing: border-box; }
html { min-height: 100%; }
body {
  min-height: 100vh;
  margin: 0;
  display: grid;
  grid-template-columns: minmax(0, 1.08fr) minmax(460px, .92fr);
  color: var(--ink);
  background: #f5f8f7;
  font-family: 'Figtree', sans-serif;
  -webkit-font-smoothing: antialiased;
}

.left-panel {
  position: relative;
  display: flex;
  align-items: center;
  min-height: 100vh;
  overflow: hidden;
  padding: clamp(48px, 8vw, 124px);
  isolation: isolate;
  color: #effbf7;
  background:
    radial-gradient(ellipse at 12% 18%, rgba(32, 180, 145, .22), transparent 35%),
    linear-gradient(145deg, #0c2628, #102e35 55%, #123e3c);
}

.left-panel::after {
  position: absolute;
  z-index: -1;
  right: -180px;
  bottom: -260px;
  width: 580px;
  height: 580px;
  border: 1px solid rgba(220, 255, 242, .1);
  border-radius: 50%;
  box-shadow: 0 0 0 48px rgba(220, 255, 242, .025), 0 0 0 96px rgba(220, 255, 242, .02);
  content: '';
}

.orb {
  position: absolute;
  z-index: -1;
  top: 12%;
  right: 12%;
  width: 170px;
  height: 170px;
  border-radius: 50%;
  background: rgba(36, 197, 154, .16);
  filter: blur(2px);
}

.train-art {
  position: absolute;
  top: 10%;
  right: 12%;
  font-size: clamp(58px, 7vw, 100px);
  opacity: .8;
  transform: rotate(-8deg);
}

.panel-content {
  width: min(100%, 590px);
  margin: 0 auto;
}

.pre-title {
  margin-bottom: 20px;
  color: #80dfc0;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 2.4px;
  text-transform: uppercase;
}

.panel-content h2 {
  max-width: 560px;
  font-family: 'Syne', sans-serif;
  font-size: clamp(40px, 5vw, 68px);
  font-weight: 700;
  letter-spacing: -2.6px;
  line-height: 1.08;
}

.panel-content h2 em {
  color: #65d8b2;
  font-style: normal;
}

.panel-content > p:not(.pre-title) {
  max-width: 450px;
  margin-top: 22px;
  color: #bad1cb;
  font-size: 16px;
  line-height: 1.75;
}

.features {
  display: grid;
  gap: 12px;
  margin-top: 36px;
}

.feature-item {
  display: flex;
  align-items: center;
  gap: 13px;
  color: #e2f1ec;
  font-size: 14px;
}

.feat-icon {
  display: grid;
  width: 38px;
  height: 38px;
  flex: 0 0 38px;
  place-items: center;
  border: 1px solid rgba(222, 255, 242, .12);
  border-radius: 12px;
  background: rgba(255, 255, 255, .07);
  font-size: 16px;
}

.right-panel {
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 100vh;
  padding: 48px clamp(28px, 5vw, 76px);
}

.login-box {
  width: min(100%, 440px);
  padding: clamp(28px, 4vw, 46px);
  border: 1px solid #e6eeeb;
  border-radius: 24px;
  background: #fff;
  box-shadow: 0 24px 70px rgba(20, 54, 45, .09);
}

.login-logo {
  display: flex;
  align-items: center;
  gap: 13px;
  margin-bottom: 36px;
}

.logo-mark {
  display: grid;
  width: 48px;
  height: 48px;
  place-items: center;
  border-radius: 15px;
  background: linear-gradient(145deg, #daf6ea, #bcebd7);
  font-size: 23px;
}

.logo-text h1 {
  font-family: 'Syne', sans-serif;
  font-size: 19px;
  font-weight: 700;
  letter-spacing: -.4px;
}

.logo-text span {
  display: block;
  margin-top: 2px;
  color: var(--muted);
  font-size: 12px;
}

.login-heading {
  font-family: 'Syne', sans-serif;
  font-size: 29px;
  font-weight: 700;
  letter-spacing: -.8px;
}

.login-sub {
  margin-top: 8px;
  margin-bottom: 28px;
  color: var(--muted);
  font-size: 14px;
  line-height: 1.5;
}

.form-group { margin-bottom: 18px; }
.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #36514b;
  font-size: 12px;
  font-weight: 700;
}

.form-group input {
  width: 100%;
  min-height: 49px;
  padding: 0 14px;
  border: 1px solid #d5e2dd;
  border-radius: 11px;
  outline: none;
  background: #fff;
  color: var(--ink);
  font: inherit;
  font-size: 14px;
  transition: border-color .18s ease, box-shadow .18s ease;
}

.form-group input::placeholder { color: #97a8a2; }
.form-group input:focus {
  border-color: var(--accent);
  box-shadow: 0 0 0 4px rgba(18, 131, 111, .12);
}

.password-wrap { position: relative; }
.password-wrap input { padding-right: 76px; }
.reveal-button {
  position: absolute;
  top: 50%;
  right: 12px;
  padding: 5px;
  border: 0;
  background: transparent;
  color: var(--accent-dark);
  cursor: pointer;
  font: inherit;
  font-size: 12px;
  font-weight: 700;
  transform: translateY(-50%);
}

.btn-submit {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;
  width: 100%;
  min-height: 50px;
  margin-top: 8px;
  border: 0;
  border-radius: 11px;
  background: linear-gradient(110deg, #11836f, #19a582);
  box-shadow: 0 8px 18px rgba(18, 131, 111, .18);
  color: #fff;
  cursor: pointer;
  font: inherit;
  font-size: 14px;
  font-weight: 700;
  transition: transform .18s ease, box-shadow .18s ease, background .18s ease;
}

.btn-submit:hover {
  background: linear-gradient(110deg, #0d725f, #128e70);
  box-shadow: 0 11px 22px rgba(18, 131, 111, .24);
  transform: translateY(-1px);
}

.btn-submit:focus-visible,
.reveal-button:focus-visible,
.demo-box:focus-visible,
.auth-link a:focus-visible {
  outline: 3px solid rgba(18, 131, 111, .35);
  outline-offset: 3px;
}

.alert {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 18px;
  padding: 12px 14px;
  border: 1px solid transparent;
  border-radius: 10px;
  font-size: 13px;
  line-height: 1.45;
}

.alert-error {
  border-color: #f5c7c4;
  background: #fff4f2;
  color: #a63c35;
}

.alert-success {
  border-color: #b7e7d0;
  background: #effaf4;
  color: #176d4e;
}

.auth-link {
  margin-top: 20px;
  color: var(--muted);
  font-size: 13px;
  text-align: center;
}

.auth-link a {
  color: var(--accent-dark);
  font-weight: 700;
  text-decoration: none;
}

.auth-link a:hover { text-decoration: underline; }

.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 25px 0 14px;
  color: #81928c;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
}

.divider::before,
.divider::after {
  height: 1px;
  flex: 1;
  background: var(--line);
  content: '';
}

.demo-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.demo-box {
  min-width: 0;
  padding: 12px;
  border: 1px solid var(--line);
  border-radius: 11px;
  background: var(--soft);
  cursor: pointer;
  text-align: left;
  transition: border-color .18s ease, background .18s ease, transform .18s ease;
}

.demo-box:hover {
  transform: translateY(-1px);
  border-color: #abd8c7;
  background: #edf8f3;
}

.db-role {
  margin-bottom: 5px;
  color: var(--accent-dark);
  font-size: 11px;
  font-weight: 700;
}

.db-cred {
  overflow-wrap: anywhere;
  color: #536861;
  font-family: 'DM Mono', monospace;
  font-size: 11px;
}

.demo-notice {
  min-height: 18px;
  margin-top: 8px;
  color: var(--muted);
  font-size: 11px;
}

@media (max-width: 900px) {
  body { grid-template-columns: minmax(0, 1fr) minmax(390px, .95fr); }
  .left-panel { padding: 48px; }
  .train-art { top: 7%; right: 8%; font-size: 58px; }
  .panel-content h2 { font-size: clamp(38px, 6vw, 54px); }
  .features { margin-top: 26px; }
}

@media (max-width: 700px) {
  body { display: block; min-height: 100svh; background: #f5f8f7; }
  .left-panel { display: none; }
  .right-panel { min-height: 100svh; padding: 24px 16px; }
  .login-box { padding: 28px 22px; border-radius: 20px; }
  .login-logo { margin-bottom: 28px; }
  .login-heading { font-size: 26px; }
}

@media (max-width: 360px) {
  .demo-grid { grid-template-columns: 1fr; }
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { scroll-behavior: auto !important; transition-duration: .01ms !important; }
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
      <div class="feature-item"><div class="feat-icon">📊</div> Manajemen booking real-time</div>
      <div class="feature-item"><div class="feat-icon">📈</div> Laporan &amp; analisis lengkap</div>
      <div class="feature-item"><div class="feat-icon">🧭</div> Kelola rute &amp; jadwal kereta</div>
    </div>
  </div>
</div>

<div class="right-panel">
  <div class="login-box">
    <div class="login-logo">
      <div class="logo-mark">🚄</div>
      <div class="logo-text"><h1>Kereta Api</h1><span>Sistem Tiket</span></div>
    </div>
    <h2 class="login-heading">Selamat Datang</h2>
    <p class="login-sub">Masuk ke akun Anda untuk melanjutkan</p>

    <?php if ($error): ?>
      <div class="alert alert-error" role="alert">⚠️ <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success" role="status">✅ Registrasi berhasil! Silakan login.</div>
    <?php endif; ?>

    <form method="POST" id="loginForm">
      <div class="form-group">
        <label for="username">Username atau email</label>
        <input type="text" name="username" placeholder="Masukkan username atau email"
               value="<?= htmlspecialchars($_POST['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autocomplete="username" id="username">
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-wrap">
          <input type="password" name="password" placeholder="Masukkan password" required autocomplete="current-password" id="password">
          <button class="reveal-button" type="button" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false">Lihat</button>
        </div>
      </div>
      <button type="submit" class="btn-submit">Masuk ke akun <span aria-hidden="true">→</span></button>
    </form>

    <p class="auth-link">Belum punya akun? <a href="register.php">Daftar sekarang</a></p>

    <div class="divider"><span>Akun baru?</span></div>
    <p class="auth-link">Buat akun pengguna melalui <a href="register.php">formulir pendaftaran</a>.</p>
  </div>
</div>
<script>
const passwordInput = document.getElementById('password');
const togglePassword = document.getElementById('togglePassword');
togglePassword.addEventListener('click', () => {
  const isVisible = passwordInput.type === 'text';
  passwordInput.type = isVisible ? 'password' : 'text';
  togglePassword.textContent = isVisible ? 'Lihat' : 'Sembunyikan';
  togglePassword.setAttribute('aria-pressed', String(!isVisible));
  togglePassword.setAttribute('aria-label', isVisible ? 'Tampilkan password' : 'Sembunyikan password');
});
</script>
</body>
</html>

