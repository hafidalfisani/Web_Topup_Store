<?php
// ===================================
// BAGIAN PHP (Logika & Proses Database)
// ===================================

error_reporting(E_ALL); 
ini_set('display_errors', 1);

// 1. Pastikan sesi dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi_baru.php';

// 2. Pemeriksaan Sesi Wajib
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($koneksi === false) {
    die("Error: Gagal memuat koneksi database.");
}

$user_id = $_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_nama'] ?? 'Pengguna'); 
$user_role = $_SESSION['user_role'] ?? 'user';

// Tentukan apakah pengguna adalah admin
$is_admin = ($user_role === 'admin');

// Fungsi untuk format harga
function format_rupiah_riwayat($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// 3. Query untuk mengambil riwayat transaksi
$select_cols = "t.id_transaksi, t.tanggal AS tanggal_transaksi, p.nama_produk, t.player_id, b.metode AS metode_bayar, t.total_harga, t.status";
$joins = "FROM Transaksi t 
          JOIN Produk p ON t.id_produk = p.id_produk
          LEFT JOIN Pembayaran b ON t.id_transaksi = b.id_transaksi";

$where_clause = "";
$bind_types = "";
$bind_params = [];
$title_display = "Daftar Transaksi Anda (" . $user_name . ")";

if ($is_admin) {
    // Admin melihat SEMUA transaksi
    $select_cols .= ", u.nama AS nama_user"; // Tambahkan kolom nama user
    $joins = "FROM Transaksi t 
              JOIN Produk p ON t.id_produk = p.id_produk
              JOIN User u ON t.id_user = u.id_user  /* Tambah join ke tabel User */
              LEFT JOIN Pembayaran b ON t.id_transaksi = b.id_transaksi";
    $title_display = "Daftar Semua Transaksi Pengguna";
} else {
    // User biasa hanya melihat transaksinya sendiri
    $where_clause = "WHERE t.id_user = ?";
    $bind_types = "i";
    $bind_params[] = $user_id;
}

$sql_riwayat = "SELECT $select_cols $joins $where_clause ORDER BY t.tanggal DESC";

$stmt_riwayat = $koneksi->prepare($sql_riwayat);

if ($stmt_riwayat === false) {
    die("Error SQL saat menyiapkan riwayat: " . $koneksi->error);
}

// Binding parameter hanya jika ada klausa WHERE (yaitu untuk user biasa)
if (!empty($bind_params)) {
    $stmt_riwayat->bind_param($bind_types, ...$bind_params);
}

$stmt_riwayat->execute();
$result_riwayat = $stmt_riwayat->get_result();

// Fungsi untuk menentukan kelas CSS status (TIDAK BERUBAH)
function get_status_class($status) {
    switch (strtolower($status)) {
        case 'success':
            // Diubah dari 'success' ke 'selesai' sesuai gambar contoh
            return 'status-Success'; 
        case 'failed':
            // Diubah dari 'failed' ke 'ditolak' sesuai gambar contoh
            return 'status-Failed'; 
        case 'pending':
        default:
            return 'status-Pending';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <title><?php echo $title_display; ?> - TopUp Game</title>
    
    <style>
        /* Gaya CSS yang Ada (Dipotong untuk singkatnya, tetapi tetap disertakan di file akhir) */
        :root {
            --primary-color: #007bff; 
            --primary-hover: #0056b3;
            --bg-color: #f8f9fa; 
            --card-bg: #ffffff; 
            --text-dark: #212529;
            --text-light: #6c757d;
            --shadow-light: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --border-radius: 0.75rem;
            --color-accent-blue: #007bff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Poppins', sans-serif; background-color: var(--bg-color); color: var(--text-dark); line-height: 1.6; }
        .container { max-width: 1200px; margin: 0 auto; padding: 1.5rem; }
        .header-topup { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; margin-bottom: 1.5rem; border-bottom: 2px solid #ddd; }
        .header-topup h1 { font-size: 2rem; font-weight: 700; color: var(--text-dark); }
        .btn-riwayat { background-color: var(--primary-color); color: white; padding: 0.5rem 1rem; border-radius: 0.5rem; text-decoration: none; font-weight: 600; transition: background-color 0.3s; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-riwayat:hover { background-color: var(--primary-hover); }
        .transaksi-section { background-color: var(--card-bg); padding: 1.5rem; border-radius: var(--border-radius); box-shadow: var(--shadow-light); overflow-x: auto; }
        .transaksi-table-wrapper { overflow-x: auto; }
        .transaksi-table { width: 100%; border-collapse: separate; border-spacing: 0; min-width: 800px; }
        .transaksi-table thead th { text-align: left; padding: 1rem 0.5rem; background-color: #eee; color: var(--text-dark); font-size: 0.9rem; font-weight: 600; text-transform: uppercase; border-bottom: 2px solid #ddd; }
        .transaksi-table tbody td { padding: 0.75rem 0.5rem; border-bottom: 1px solid #eee; vertical-align: middle; font-size: 0.95rem; }
        .transaksi-table tbody tr:last-child td { border-bottom: none; }
        .transaksi-table tbody tr:hover { background-color: #f5f5f5; }
        .status-Success, .status-Pending, .status-Failed { display: inline-block; padding: 0.3rem 0.6rem; border-radius: 0.3rem; font-weight: 600; font-size: 0.8rem; text-align: center; }
        .status-Success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; } /* Hijau Muda untuk Selesai/Success */
        .status-Pending { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; } /* Kuning Muda untuk Pending */
        .status-Failed { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; } /* Merah Muda untuk Ditolak/Failed */
        
        /* Tambahan CSS untuk kolom Admin */
        .admin-col { 
            display: none;
        }
        <?php if ($is_admin): ?>
        /* Tampilkan kolom Nama User untuk Admin */
        .admin-col {
            display: table-cell !important;
        }
        .admin-col-header {
            min-width: 120px;
        }
        <?php endif; ?>

        @media (max-width: 600px) {
            .transaksi-table thead th:nth-child(<?php echo $is_admin ? '5' : '4'; ?>), /* ID Pemain */
            .transaksi-table tbody td:nth-child(<?php echo $is_admin ? '5' : '4'; ?>) {
                display: none;
            }
            /* Sembunyikan Nama User di mobile meskipun admin */
            .admin-col {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <div class="header-topup">
            <h1>Riwayat Transaksi</h1>
            <div class="header-topup-actions">
                <?php if ($user_role === 'admin'): ?>
                    <a href="admin_dashboard.php" class="btn-riwayat" style="background-color: #28a745;">
                        <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                    </a>
                <?php else: ?>
                    <a href="produk.php" class="btn-riwayat">
                        <i class="fas fa-arrow-left"></i> Kembali ke Katalog
                    </a>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($result_riwayat->num_rows > 0): ?>
            <div class="transaksi-section">
                <h4 style="margin-top: 0; margin-bottom: 20px; color: var(--color-accent-blue);"><?php echo $title_display; ?></h4>
                
                <div class="transaksi-table-wrapper">
                    <table class="transaksi-table">
                        <thead>
                            <tr>
                                <th>ID Transaksi</th>
                                <th>Tanggal</th>
                                <?php if ($is_admin): ?>
                                <th class="admin-col admin-col-header">User</th> <?php endif; ?>
                                <th>Produk</th>
                                <th class="d-none-mobile">ID Pemain</th>
                                <th>Metode Bayar</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                        
                        <?php while ($row = $result_riwayat->fetch_assoc()): ?>
                            <?php 
                                $status_class = get_status_class($row['status']); 
                                $tanggal_db = new DateTime($row['tanggal_transaksi']);
                                $tanggal_format = $tanggal_db->format('d M Y H:i'); 
                                
                                // Sesuaikan tampilan status di tabel
                                $status_text = htmlspecialchars($row['status']);
                                if (strtolower($status_text) == 'success') {
                                    $status_text = 'Selesai';
                                } elseif (strtolower($status_text) == 'failed') {
                                    $status_text = 'Ditolak';
                                } elseif (strtolower($status_text) == 'pending') {
                                    $status_text = 'Pending';
                                }
                            ?>
                            
                            <tr>
                                <td><?php echo htmlspecialchars($row['id_transaksi']); ?></td>
                                <td><?php echo $tanggal_format; ?></td>
                                <?php if ($is_admin): ?>
                                <td class="admin-col"><?php echo htmlspecialchars($row['nama_user']); ?></td> <?php endif; ?>
                                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td class="d-none-mobile"><?php echo htmlspecialchars($row['player_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['metode_bayar'] ?? 'N/A'); ?></td>
                                <td><?php echo format_rupiah_riwayat($row['total_harga']); ?></td>
                                <td><span class="<?php echo $status_class; ?>"><?php echo $status_text; ?></span></td>
                            </tr>
                            
                        <?php endwhile; ?>

                        </tbody>
                    </table>
                </div>
            </div>
            
        <?php else: ?>
            <div style="text-align:center; padding: 30px; background: var(--card-bg); border-radius: var(--border-radius); box-shadow: var(--shadow-light); margin-top: 30px;">
                <i class="fas fa-history fa-4x" style="color: #ccc; margin-bottom: 15px;"></i>
                <p style="font-size: 1.1rem; color: var(--text-light);">
                    <?php echo $is_admin ? 'Tidak ada transaksi yang tercatat saat ini.' : 'Anda belum memiliki riwayat transaksi saat ini. Mulailah bertransaksi!'; ?>
                </p>
            </div>
        <?php endif; ?>
        
    </div> 
</body>
</html>
<?php 
if (isset($stmt_riwayat)) {
    $stmt_riwayat->close();
}
if (isset($koneksi)) {
    $koneksi->close(); 
}
?>