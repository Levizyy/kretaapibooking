<?php
require 'includes/config.php';
/** @var mysqli $conn */
if (isset($_SESSION['user_id'])) { $base=get_base_url(); header("Location: {$base}/user/"); exit(); }

$error = ''; $redirect_after_success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $username  = sanitize($_POST['username'] ?? '');
    $email     = sanitize($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $phone     = sanitize($_POST['phone'] ?? '');

    if (!$full_name || !$username || !$email || !$password)
        $error = 'Semua field wajib diisi.';
    elseif (!validate_email($email))
        $error = 'Format email tidak valid.';
    elseif (strlen($password) < 6)
        $error = 'Password minimal 6 karakter.';
    else {
        $chk = mysqli_query($conn,"SELECT id FROM users WHERE username='$username' OR email='$email'");
        if (mysqli_num_rows($chk) > 0)
            $error = 'Username atau email sudah digunakan.';
        else {
            $pass = md5($password);
            $ins  = "INSERT INTO users (full_name,username,email,password,phone,role,status) VALUES ('$full_name','$username','$email','$pass','$phone','user','active')";
            if (mysqli_query($conn,$ins)) $redirect_after_success = true;
            else $error = 'Terjadi kesalahan. Coba lagi.';
        }
    }
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Daftar — Kereta Api</title>
<?php if ($redirect_after_success): ?>
<meta http-equiv="refresh" content="2;url=login.php?registered=1">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Figtree:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
:root{--bg:#08121f;--surface:#121f2f;--surface-soft:#162733;--border:rgba(212,229,255,.12);--accent:#16a085;--accent-soft:rgba(22,160,133,.14);--text-1:#e5f4ff;--text-2:#b0c7db;--text-3:#8ca6bf;}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
html{height:100%}
body{min-height:100vh;background:linear-gradient(180deg,#08121f 0%,#0f2238 100%);font-family:'Inter',sans-serif;color:var(--text-1);display:flex;align-items:center;justify-content:center;padding:24px;position:relative;overflow:hidden;-webkit-font-smoothing:antialiased;}
body::before{content:'';position:fixed;inset:0;background:radial-gradient(circle at 20% 15%,rgba(22,160,133,.18),transparent 18%),radial-gradient(circle at 82% 18%,rgba(242,158,76,.1),transparent 20%);pointer-events:none;}
.card{position:relative;z-index:1;background:rgba(18,31,48,.96);border:1px solid rgba(212,229,255,.08);border-radius:28px;padding:40px 36px;max-width:520px;width:100%;box-shadow:0 30px 90px rgba(0,0,0,.32)}
.logo-row{display:flex;align-items:center;gap:14px;margin-bottom:26px;justify-content:center}
.logo-mark{width:52px;height:52px;border-radius:18px;background:linear-gradient(135deg,var(--accent),#2dd4bf);display:flex;align-items:center;justify-content:center;font-size:24px;box-shadow:0 18px 40px rgba(22,160,133,.18)}
.logo-text h1{font-size:20px;font-weight:800;color:var(--text-1);letter-spacing:-.4px}
.logo-text span{font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:2px;font-weight:700}
.auth-title{font-size:26px;font-weight:800;color:var(--text-1);margin-bottom:8px;text-align:center}
.auth-sub{font-size:14.5px;color:var(--text-2);margin-bottom:24px;text-align:center;line-height:1.6}
.alert{padding:14px 16px;border-radius:18px;font-size:14px;display:flex;align-items:center;gap:10px;margin-bottom:18px;border-left:4px solid}
.alert-error{background:rgba(244,114,182,.12);color:#f472b6;border-left-color:#f472b6}
.alert-success{background:rgba(34,211,170,.12);color:#2dd4bf;border-left-color:#2dd4bf}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.form-group{margin-bottom:18px}
.form-group label{display:block;font-size:12px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:1px;margin-bottom:8px}
.form-group input{width:100%;background:rgba(255,255,255,.05);border:1.5px solid rgba(212,229,255,.08);border-radius:16px;padding:14px 16px;color:var(--text-1);font-size:14px;transition:all .22s ease;outline:none}
.form-group input::placeholder{color:var(--text-3)}
.form-group input:focus{border-color:var(--accent);box-shadow:0 0 0 4px var(--accent-soft);background:rgba(255,255,255,.08)}
.btn-register{width:100%;padding:15px 0;border-radius:18px;border:none;background:linear-gradient(135deg,var(--accent),#2dd4bf);color:#08121f;font-size:15px;font-weight:800;cursor:pointer;transition:all .22s ease;box-shadow:0 18px 40px rgba(22,160,133,.18)}
.btn-register:hover{transform:translateY(-1px);box-shadow:0 22px 48px rgba(22,160,133,.22)}
.auth-link{text-align:center;font-size:13.5px;color:var(--text-2);margin-top:18px}
.auth-link a{color:var(--accent);font-weight:700;text-decoration:none}
.success-screen{text-align:center;padding:46px 0}
.success-screen .s-icon{font-size:52px;margin-bottom:16px}
.success-screen h3{font-size:22px;font-weight:800;color:var(--text-1);margin-bottom:8px}
.success-screen p{font-size:14px;color:var(--text-2)}
@media(max-width:700px){.form-grid{grid-template-columns:1fr}.card{padding:30px 24px}}
</style>
</head>
<body>
<div class="card">
  <div class="logo-row">
    <div class="logo-mark">🚄</div>
    <div class="logo-text"><h1>Kereta Api</h1><span>Sistem Tiket</span></div>
  </div>

  <?php if ($redirect_after_success): ?>
  <div class="success-screen">
    <span class="s-icon">✅</span>
    <h3>Akun Berhasil Dibuat!</h3>
    <p>Selamat datang! Anda akan diarahkan ke halaman login dalam 2 detik...</p>
  </div>
  <?php else: ?>
  <h2 class="auth-title">Buat Akun Baru</h2>
  <p class="auth-sub">Daftar dan mulai pesan tiket kereta</p>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="form-grid">
      <div class="form-group">
        <label>Nama Lengkap</label>
        <input type="text" name="full_name" placeholder="Nama Anda" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
      </div>
      <div class="form-group">
        <label>No. Telepon</label>
        <input type="text" name="phone" placeholder="08xxxxxxxxxx" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
      </div>
    </div>
    <div class="form-group">
      <label>Username</label>
      <input type="text" name="username" placeholder="Pilih username unik" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Email</label>
      <input type="email" name="email" placeholder="email@contoh.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
    </div>
    <div class="form-group">
      <label>Password</label>
      <input type="password" name="password" placeholder="Minimal 6 karakter" required>
    </div>
    <button type="submit" class="btn-register">Buat Akun →</button>
  </form>
  <p class="auth-link">Sudah punya akun? <a href="login.php">Masuk sekarang</a></p>
  <?php endif; ?>
</div>
</body>
</html>

