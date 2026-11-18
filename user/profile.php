<?php
require '../includes/config.php';
/** @var mysqli $conn */
check_user_login();

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';
$active_section = 'profile';

// Get user data
$user_query = "SELECT * FROM users WHERE id = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $active_section = 'profile';
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        
        if (empty($full_name) || empty($email)) {
            $error = "Nama dan email harus diisi!";
        } elseif (!validate_email($email)) {
            $error = "Format email tidak valid!";
        } else {
            // Check if email is already used
            $check_email = "SELECT id FROM users WHERE email = '$email' AND id != $user_id";
            $result = mysqli_query($conn, $check_email);
            
            if (mysqli_num_rows($result) > 0) {
                $error = "Email sudah digunakan!";
            } else {
                $update = "UPDATE users SET full_name = '$full_name', email = '$email', phone = '$phone' WHERE id = $user_id";
                if (mysqli_query($conn, $update)) {
                    $_SESSION['full_name'] = $full_name;
                    $message = "Profil berhasil diperbarui!";
                    $user['full_name'] = $full_name;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                } else {
                    $error = "Gagal memperbarui profil!";
                }
            }
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'change_password') {
        $active_section = 'password';
        $old_password = sanitize($_POST['old_password']);
        $new_password = sanitize($_POST['new_password']);
        $confirm_password = sanitize($_POST['confirm_password']);
        
        if (password_hash_custom($old_password) !== $user['password']) {
            $error = "Password lama tidak sesuai!";
        } elseif (strlen($new_password) < 6) {
            $error = "Password baru harus minimal 6 karakter!";
        } elseif ($new_password !== $confirm_password) {
            $error = "Password baru tidak cocok!";
        } else {
            $hashed = password_hash_custom($new_password);
            $update = "UPDATE users SET password = '$hashed' WHERE id = $user_id";
            if (mysqli_query($conn, $update)) {
                $message = "Password berhasil diubah!";
            } else {
                $error = "Gagal mengubah password!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Kereta Api</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Figtree:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/user-style.css">
    <style>
        .profile-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 30px;
            max-width: 1000px;
        }
        
        .profile-sidebar {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: fit-content;
        }
        
        .profile-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto 20px;
            color: white;
        }
        
        .profile-name {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .profile-role {
            text-align: center;
            color: #999;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        .profile-menu {
            list-style: none;
            margin-top: 30px;
        }
        
        .profile-menu-item {
            padding: 12px;
            margin-bottom: 5px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .profile-menu-item:hover {
            background: #f0f0f0;
        }
        
        .profile-menu-item.active {
            background: #667eea;
            color: white;
        }
        
        .profile-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .profile-section {
            display: none;
        }
        
        .profile-section.active {
            display: block;
        }
        
        .profile-section h3 {
            margin-bottom: 25px;
            font-size: 20px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
            
            .form-row {
                grid-template-columns: 1fr;
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
                    <a href="tiket-saya.php" class="nav-item">Tiket Saya</a>
                    <a href="profile.php" class="nav-item active">Profile</a>
                    <a href="../logout.php" class="nav-item logout">⏻ Logout</a>
                </nav>
            </div>
        </header>
        
        <main class="page-wrap">
            <h2 class="page-title">Profil Saya</h2>
            <p class="page-subtitle">Kelola informasi akun dan preferensi keamanan Anda.</p>
            
            <div class="profile-container">
                <!-- Sidebar -->
                <div class="profile-sidebar">
                    <div class="profile-avatar">👤</div>
                    <div class="profile-name"><?php echo $user['full_name']; ?></div>
                    <div class="profile-role"><?php echo ucfirst($user['role']); ?></div>
                    
                    <nav class="profile-menu" role="tablist" aria-label="Pengaturan profil">
                        <button class="profile-menu-item <?php echo $active_section === 'profile' ? 'active' : ''; ?>" id="profileTab" type="button" role="tab" aria-selected="<?php echo $active_section === 'profile' ? 'true' : 'false'; ?>" aria-controls="profile" onclick="showSection('profile', this)">Data profil</button>
                        <button class="profile-menu-item <?php echo $active_section === 'password' ? 'active' : ''; ?>" id="passwordTab" type="button" role="tab" aria-selected="<?php echo $active_section === 'password' ? 'true' : 'false'; ?>" aria-controls="password" onclick="showSection('password', this)">Ubah password</button>
                        <button class="profile-menu-item" id="statsTab" type="button" role="tab" aria-selected="false" aria-controls="stats" onclick="showSection('stats', this)">Statistik</button>
                    </nav>
                </div>
                
                <!-- Content -->
                <div class="profile-content">
                    <?php if ($message): ?>
                        <div class="alert alert-success" role="status"><?php echo $message; ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-error" role="alert"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <!-- Profile Section -->
                    <div id="profile" class="profile-section <?php echo $active_section === 'profile' ? 'active' : ''; ?>" role="tabpanel" aria-labelledby="profileTab">
                        <h3>Data Profil</h3>
                        <p class="profile-section-description">Pastikan nama dan email Anda selalu terbaru.</p>
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" value="<?php echo $user['username']; ?>" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="full_name">Nama Lengkap</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="phone">Nomor Telepon</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo $user['phone'] ?? ''; ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="created_at">Anggota Sejak</label>
                                <input type="text" id="created_at" value="<?php echo format_date($user['created_at']); ?>" disabled>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
                        </form>
                    </div>
                    
                    <!-- Password Section -->
                    <div id="password" class="profile-section <?php echo $active_section === 'password' ? 'active' : ''; ?>" role="tabpanel" aria-labelledby="passwordTab">
                        <h3>Ubah Password</h3>
                        <p class="profile-section-description">Gunakan password minimal 6 karakter yang belum pernah Anda gunakan.</p>
                        <form method="POST">
                            <input type="hidden" name="action" value="change_password">
                            
                            <div class="form-group">
                                <label for="old_password">Password Lama</label>
                                <input type="password" id="old_password" name="old_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">Password Baru</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Konfirmasi Password Baru</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">Ubah password</button>
                        </form>
                    </div>
                    
                    <!-- Stats Section -->
                    <div id="stats" class="profile-section" role="tabpanel" aria-labelledby="statsTab">
                        <h3>Statistik Pemesanan</h3>
                        <p class="profile-section-description">Ringkasan aktivitas pemesanan Anda.</p>
                        
                        <?php
                        $total_bookings = mysqli_query($conn, "SELECT COUNT(*) as count FROM booking WHERE user_id = $user_id")->fetch_assoc()['count'];
                        $total_paid = mysqli_query($conn, "SELECT COUNT(*) as count FROM booking WHERE user_id = $user_id AND status_pembayaran = 'paid'")->fetch_assoc()['count'];
                        $total_pending = mysqli_query($conn, "SELECT COUNT(*) as count FROM booking WHERE user_id = $user_id AND status_pembayaran = 'pending'")->fetch_assoc()['count'];
                        $total_spent = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM booking WHERE user_id = $user_id AND status_pembayaran = 'paid'")->fetch_assoc()['total'] ?? 0;
                        $total_passengers = mysqli_query($conn, "SELECT SUM(jumlah_penumpang) as total FROM booking WHERE user_id = $user_id")->fetch_assoc()['total'] ?? 0;
                        ?>
                        
                        <div class="profile-stats">
                            <div class="profile-stat">
                                <div class="profile-stat-value"><?php echo number_format((int)$total_bookings); ?></div>
                                <p class="profile-stat-label">Total pemesanan</p>
                            </div>
                            
                            <div class="profile-stat">
                                <div class="profile-stat-value"><?php echo number_format((int)$total_paid); ?></div>
                                <p class="profile-stat-label">Pemesanan lunas</p>
                            </div>
                            
                            <div class="profile-stat">
                                <div class="profile-stat-value"><?php echo number_format((int)$total_pending); ?></div>
                                <p class="profile-stat-label">Menunggu pembayaran</p>
                            </div>
                            
                            <div class="profile-stat">
                                <div class="profile-stat-value"><?php echo number_format((int)$total_passengers); ?></div>
                                <p class="profile-stat-label">Total penumpang</p>
                            </div>
                            
                            <div class="profile-stat profile-stat--wide">
                                <div class="profile-stat-value"><?php echo format_currency($total_spent); ?></div>
                                <p class="profile-stat-label">Total pengeluaran (lunas)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        
        <!-- Footer -->
        <footer class="footer">
            <p>&copy; 2026 Sistem Pemesanan Tiket Kereta Api. All rights reserved.</p>
        </footer>
    </div>
    
    <script>
        function showSection(section, selectedTab) {
            document.querySelectorAll('.profile-section').forEach(el => {
                el.classList.remove('active');
            });
            document.querySelectorAll('.profile-menu-item').forEach(el => {
                el.classList.remove('active');
                el.setAttribute('aria-selected', 'false');
            });
            document.getElementById(section).classList.add('active');
            selectedTab.classList.add('active');
            selectedTab.setAttribute('aria-selected', 'true');
        }
    </script>
</body>
</html>
