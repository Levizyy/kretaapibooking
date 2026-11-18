<aside class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <div class="brand">
            <div class="brand-icon">🚄</div>
            <div>
                <h1>Kereta Api</h1>
            </div>
        </div>
        <div class="tagline">Admin Panel</div>
    </div>

    <nav class="sidebar-menu">
        <div class="menu-section-label">Utama</div>
        <a href="index.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">
            <span class="icon">📊</span> Dashboard
        </a>
        <a href="booking.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'booking.php' ? 'active' : ''; ?>">
            <span class="icon">🎫</span> Booking
        </a>

        <div class="menu-section-label">Master Data</div>
        <a href="kereta.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'kereta.php' ? 'active' : ''; ?>">
            <span class="icon">🚆</span> Kereta
        </a>
        <a href="rute.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'rute.php' ? 'active' : ''; ?>">
            <span class="icon">🛤️</span> Rute Kereta
        </a>
        <a href="stasiun.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'stasiun.php' ? 'active' : ''; ?>">
            <span class="icon">🏢</span> Stasiun
        </a>
        <a href="pengguna.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'pengguna.php' ? 'active' : ''; ?>">
            <span class="icon">👥</span> Pengguna
        </a>

        <div class="menu-section-label">Laporan</div>
        <a href="laporan.php" class="menu-item <?php echo basename($_SERVER['PHP_SELF']) === 'laporan.php' ? 'active' : ''; ?>">
            <span class="icon">📈</span> Laporan &amp; Analisis
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['full_name'] ?? 'A', 0, 1)); ?></div>
            <div class="user-meta">
                <div class="name"><?php echo $_SESSION['full_name'] ?? 'Admin'; ?></div>
                <div class="role">Administrator</div>
            </div>
        </div>
        <a href="../logout.php" class="logout-btn">⎋ Logout</a>
    </div>
</aside>
