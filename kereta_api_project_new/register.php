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
:root{
  --bg:#eef6ff;--surface:#ffffff;--border:rgba(59,130,246,.18);
  --amber:#2563eb;--amber-l:#3b82f6;
  --text-1:#0f172a;--text-2:#475569;--text-3:#64748b;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
body{
  font-family:'Figtree',sans-serif;min-height:100vh;background:var(--bg);
  display:flex;align-items:center;justify-content:center;
  padding:32px 24px;position:relative;overflow-x:hidden;
  -webkit-font-smoothing:antialiased;
}
body::before{
  content:'';position:fixed;top:0;left:0;right:0;height:50vh;
  background:linear-gradient(160deg,#eef6ff 0%,#dbeafe 100%);
  clip-path:ellipse(120% 100% at 50% 0%);z-index:0;
}
body::after{
  content:'';position:fixed;bottom:-200px;right:-200px;
  width:500px;height:500px;border-radius:50%;
  background:radial-gradient(circle,rgba(14,165,233,.12) 0%,transparent 70%);
  pointer-events:none;
}
.card{
  background:var(--bg);border:1px solid var(--border);
  border-radius:20px;padding:40px 40px 36px;
  width:100%;max-width:520px;
  box-shadow:0 32px 80px rgba(0,0,0,.6);
  position:relative;z-index:1;
  animation:fadeUp .4s cubic-bezier(.4,0,.2,1);
}
/* top amber glow line */
.card::before{
  content:'';position:absolute;top:0;left:10%;right:10%;height:1px;
  background:linear-gradient(90deg,transparent,var(--amber),transparent);
  border-radius:50%;
}
@keyframes fadeUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
.logo-row{display:flex;align-items:center;gap:12px;margin-bottom:28px;}
.logo-mark{
  width:44px;height:44px;border-radius:12px;
  background:linear-gradient(135deg,var(--amber),var(--amber-l));
  display:flex;align-items:center;justify-content:center;font-size:22px;
  box-shadow:0 0 24px rgba(37,99,235,.18);flex-shrink:0;
}
.logo-text h1{font-family:'Syne',sans-serif;font-size:16px;font-weight:800;color:var(--text-1);letter-spacing:-.3px;line-height:1;}
.logo-text span{font-size:9.5px;color:var(--amber);text-transform:uppercase;letter-spacing:2.5px;font-weight:600;}
.auth-title{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:var(--text-1);letter-spacing:-.4px;margin-bottom:4px;}
.auth-sub{font-size:13.5px;color:var(--text-2);margin-bottom:26px;}
.alert{padding:12px 14px;border-radius:8px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px;border-left:3px solid;}
.alert-error{background:rgba(239,68,68,.1);color:#f87171;border-left-color:#ef4444;}
.alert-success{background:rgba(16,185,129,.1);color:#34d399;border-left-color:#10b981;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.form-group{margin-bottom:16px;}
.form-group label{display:block;font-size:11px;font-weight:700;color:var(--text-3);text-transform:uppercase;letter-spacing:1px;margin-bottom:7px;}
.form-group input{
  width:100%;background:var(--surface);border:1.5px solid var(--border);
  border-radius:9px;padding:11px 14px;font-size:14px;color:var(--text-1);
  font-family:'Figtree',sans-serif;transition:all .2s;outline:none;
}
.form-group input::placeholder{color:var(--text-3);}
.form-group input:focus{border-color:var(--amber);box-shadow:0 0 0 3px rgba(37,99,235,.12);background:#ffffff;}
.btn-register{
  width:100%;padding:13px;border-radius:9px;
  background:linear-gradient(135deg,var(--amber),var(--amber-l));
  color:#ffffff;font-size:15px;font-weight:700;border:none;cursor:pointer;
  font-family:'Syne',sans-serif;margin-top:6px;
  transition:all .2s;box-shadow:0 4px 20px rgba(37,99,235,.24);
}
.btn-register:hover{transform:translateY(-1px);box-shadow:0 6px 28px rgba(37,99,235,.32);}
.auth-link{text-align:center;font-size:13.5px;color:var(--text-2);margin-top:18px;}
.auth-link a{color:var(--amber);font-weight:600;text-decoration:none;}
.success-screen{text-align:center;padding:12px 0;}
.success-screen .s-icon{font-size:56px;margin-bottom:16px;display:block;}
.success-screen h3{font-family:'Syne',sans-serif;font-size:22px;font-weight:800;color:var(--text-1);margin-bottom:8px;}
.success-screen p{font-size:13.5px;color:var(--text-2);}
@media(max-width:560px){.form-grid{grid-template-columns:1fr;}.card{padding:28px 24px;}}
</style>
</head>
<body>
<div class="card">
  <div class="logo-row">
    <div class="logo-mark">🚄</div>
    <div class="logo-text"><h1>Kereta Api</h1><span>Ticket System</span></div>
  </div>

  <?php if ($redirect_after_success): ?>
  <div class="success-screen">
    <span class="s-icon">🎉</span>
    <h3>Akun Berhasil Dibuat!</h3>
    <p>Selamat datang! Anda akan diarahkan ke halaman login dalam 2 detik…</p>
  </div>
  <?php else: ?>
  <h2 class="auth-title">Buat Akun Baru</h2>
  <p class="auth-sub">Daftar dan mulai pesan tiket kereta</p>

  <?php if ($error): ?>
    <div class="alert alert-error">⚠ <?= htmlspecialchars($error) ?></div>
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
