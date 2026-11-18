<?php
require_once __DIR__ . '/../includes/config.php';
/** @var mysqli $conn */
check_admin_login();

$action = $_GET['action'] ?? '';
$id = (int)($_GET['id'] ?? 0);
if ($action === 'confirm_payment' && $id) {
    mysqli_query($conn, "UPDATE booking SET status_pembayaran='paid' WHERE id=$id");
    header("Location: booking.php"); exit();
}

$summary_result = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(status_pembayaran = 'paid'), 0) AS paid,
            COALESCE(SUM(status_pembayaran = 'pending'), 0) AS pending,
            COALESCE(SUM(status_pembayaran = 'cancelled'), 0) AS cancelled
     FROM booking"
);
$booking_summary = mysqli_fetch_assoc($summary_result);

$allowed_statuses = ['paid', 'pending', 'cancelled'];
$requested_status = $_GET['status'] ?? '';
$filter_status = in_array($requested_status, $allowed_statuses, true) ? $requested_status : '';
$query = "SELECT b.*, u.full_name, u.phone, s.nama_stasiun
          FROM booking b
          JOIN users u ON b.user_id = u.id
          JOIN rute_kereta r ON b.rute_kereta_id = r.id
          JOIN stasiun s ON r.stasiun_akhir_id = s.id
          WHERE 1=1";
if ($filter_status) $query .= " AND b.status_pembayaran = '$filter_status'";
$query .= " ORDER BY b.tanggal_booking DESC";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking — Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;500;600;700;800&family=Figtree:ital,wght@0,300;0,400;0,500;0,600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/admin-style.css">
    <link rel="stylesheet" href="../assets/css/admin-booking.css">
</head>
<body>
<div class="admin-container">
    <?php include 'includes/sidebar.php'; ?>
    <button class="booking-sidebar-backdrop" id="bookingSidebarBackdrop" type="button" aria-label="Tutup menu navigasi" hidden></button>

    <main class="main-content">
        <header class="top-bar">
            <button class="booking-sidebar-toggle" id="bookingMenuToggle" type="button" aria-controls="adminSidebar" aria-expanded="false">
                <span aria-hidden="true">☰</span>
                <span class="visually-hidden">Buka menu navigasi</span>
            </button>
            <h2>Pemesanan</h2>
        </header>

        <div class="content">
            <section class="booking-page" aria-labelledby="booking-title">
                <div class="booking-heading">
                    <div>
                        <p class="booking-eyebrow">OPERASIONAL</p>
                        <h1 id="booking-title">Kelola pemesanan</h1>
                        <p class="booking-description">Pantau transaksi dan konfirmasi pembayaran pelanggan.</p>
                    </div>
                    <div class="booking-total">
                        <span class="booking-total-label">Total pemesanan</span>
                        <strong><?php echo number_format((int)$booking_summary['total']); ?></strong>
                    </div>
                </div>

                <div class="booking-stats" aria-label="Ringkasan status pemesanan">
                    <article class="booking-stat">
                        <span class="booking-stat-icon booking-stat-icon--all" aria-hidden="true">BK</span>
                        <div><span class="booking-stat-label">Semua pesanan</span><strong><?php echo number_format((int)$booking_summary['total']); ?></strong></div>
                    </article>
                    <article class="booking-stat">
                        <span class="booking-stat-icon booking-stat-icon--pending" aria-hidden="true">!</span>
                        <div><span class="booking-stat-label">Menunggu pembayaran</span><strong><?php echo number_format((int)$booking_summary['pending']); ?></strong></div>
                    </article>
                    <article class="booking-stat">
                        <span class="booking-stat-icon booking-stat-icon--paid" aria-hidden="true">OK</span>
                        <div><span class="booking-stat-label">Sudah dibayar</span><strong><?php echo number_format((int)$booking_summary['paid']); ?></strong></div>
                    </article>
                    <article class="booking-stat">
                        <span class="booking-stat-icon booking-stat-icon--cancelled" aria-hidden="true">×</span>
                        <div><span class="booking-stat-label">Dibatalkan</span><strong><?php echo number_format((int)$booking_summary['cancelled']); ?></strong></div>
                    </article>
                </div>

                <section class="booking-list" aria-labelledby="booking-list-title">
                    <div class="booking-list-heading">
                        <div>
                            <h2 id="booking-list-title">Daftar pemesanan</h2>
                            <p>Pilih status atau cari berdasarkan kode, nama, telepon, dan tujuan.</p>
                        </div>
                    </div>
                    <div class="booking-toolbar">
                        <nav class="booking-filters" aria-label="Filter status pembayaran">
                            <a href="booking.php" class="booking-filter <?php echo !$filter_status ? 'is-active' : ''; ?>" <?php echo !$filter_status ? 'aria-current="page"' : ''; ?>>
                                Semua <span><?php echo number_format((int)$booking_summary['total']); ?></span>
                            </a>
                            <a href="booking.php?status=pending" class="booking-filter <?php echo $filter_status === 'pending' ? 'is-active' : ''; ?>" <?php echo $filter_status === 'pending' ? 'aria-current="page"' : ''; ?>>
                                Menunggu <span><?php echo number_format((int)$booking_summary['pending']); ?></span>
                            </a>
                            <a href="booking.php?status=paid" class="booking-filter <?php echo $filter_status === 'paid' ? 'is-active' : ''; ?>" <?php echo $filter_status === 'paid' ? 'aria-current="page"' : ''; ?>>
                                Lunas <span><?php echo number_format((int)$booking_summary['paid']); ?></span>
                            </a>
                            <a href="booking.php?status=cancelled" class="booking-filter <?php echo $filter_status === 'cancelled' ? 'is-active' : ''; ?>" <?php echo $filter_status === 'cancelled' ? 'aria-current="page"' : ''; ?>>
                                Dibatalkan <span><?php echo number_format((int)$booking_summary['cancelled']); ?></span>
                            </a>
                        </nav>
                        <label class="booking-search">
                            <span class="booking-search-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Cari pemesanan</span>
                            <input type="search" id="bookingSearch" placeholder="Cari pemesanan..." autocomplete="off">
                            <kbd>/</kbd>
                        </label>
                    </div>

                    <div class="booking-table-wrap">
                        <table class="data-table booking-table">
                            <thead>
                                <tr>
                                    <th scope="col">Kode pemesanan</th>
                                    <th scope="col">Pelanggan</th>
                                    <th scope="col">Tujuan</th>
                                    <th scope="col">Penumpang</th>
                                    <th scope="col">Total</th>
                                    <th scope="col">Pembayaran</th>
                                    <th scope="col">Tanggal pesan</th>
                                    <th scope="col">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="bookingRows">
                                <?php if (mysqli_num_rows($result) === 0): ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="booking-empty">
                                            <span class="booking-empty-icon" aria-hidden="true">—</span>
                                            <strong>Belum ada pemesanan</strong>
                                            <span><?php echo $filter_status ? 'Tidak ada pesanan dengan status ini.' : 'Pemesanan baru akan muncul di sini.'; ?></span>
                                        </div>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr class="booking-row">
                                    <td><span class="booking-code"><?php echo htmlspecialchars($row['booking_code'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                                    <td>
                                        <div class="booking-customer">
                                            <strong><?php echo htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <span><?php echo htmlspecialchars($row['phone'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['nama_stasiun'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo number_format((int)$row['jumlah_penumpang']); ?> orang</td>
                                    <td><strong class="booking-price"><?php echo format_currency($row['total_harga']); ?></strong></td>
                                    <td>
                                        <span class="badge badge-<?php echo htmlspecialchars($row['status_pembayaran'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars(ucfirst($row['status_pembayaran']), ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    </td>
                                    <td class="booking-date"><?php echo format_datetime($row['tanggal_booking']); ?></td>
                                    <td>
                                        <?php if ($row['status_pembayaran'] === 'pending'): ?>
                                        <a href="booking.php?action=confirm_payment&amp;id=<?php echo (int)$row['id']; ?>"
                                           class="btn btn-success btn-sm booking-confirm"
                                           onclick="return confirm('Konfirmasi pembayaran ini?')">
                                            Konfirmasi
                                        </a>
                                        <?php else: ?>
                                        <span class="booking-no-action">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php endif; ?>
                                <tr class="booking-search-empty" id="bookingSearchEmpty" hidden>
                                    <td colspan="8">
                                        <div class="booking-empty">
                                            <strong>Tidak ada hasil</strong>
                                            <span>Coba kata kunci lain atau ubah filter status.</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="booking-table-footer">
                        <span id="bookingResultCount"><?php echo number_format(mysqli_num_rows($result)); ?> pesanan ditampilkan</span>
                        <span>Data terbaru ditampilkan lebih dahulu</span>
                    </div>
                </section>
            </section>
        </div>
    </main>
</div>
<script>
const bookingSearch = document.getElementById('bookingSearch');
const bookingRows = [...document.querySelectorAll('.booking-row')];
const searchEmpty = document.getElementById('bookingSearchEmpty');
const resultCount = document.getElementById('bookingResultCount');
const sidebar = document.getElementById('adminSidebar');
const menuToggle = document.getElementById('bookingMenuToggle');
const sidebarBackdrop = document.getElementById('bookingSidebarBackdrop');

function filterBookings() {
    const query = bookingSearch.value.trim().toLocaleLowerCase('id');
    let visibleCount = 0;

    bookingRows.forEach((row) => {
        const matches = row.textContent.toLocaleLowerCase('id').includes(query);
        row.hidden = !matches;
        if (matches) visibleCount++;
    });

    searchEmpty.hidden = visibleCount > 0 || bookingRows.length === 0;
    resultCount.textContent = `${visibleCount} pesanan ditampilkan`;
}

bookingSearch.addEventListener('input', filterBookings);
function setSidebarOpen(isOpen) {
    sidebar.classList.toggle('is-open', isOpen);
    sidebarBackdrop.hidden = !isOpen;
    menuToggle.setAttribute('aria-expanded', String(isOpen));
    document.body.classList.toggle('booking-menu-open', isOpen);
}

menuToggle.addEventListener('click', () => {
    setSidebarOpen(!sidebar.classList.contains('is-open'));
});
sidebarBackdrop.addEventListener('click', () => setSidebarOpen(false));
sidebar.addEventListener('click', (event) => {
    if (event.target.closest('a')) setSidebarOpen(false);
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
        setSidebarOpen(false);
        menuToggle.focus();
        return;
    }
    if (event.key === '/' && document.activeElement !== bookingSearch) {
        event.preventDefault();
        bookingSearch.focus();
    }
});
</script>
</body>
</html>
