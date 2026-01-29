<?php
// ===================================
// BAGIAN PHP (Logika & Proses Database)
// ===================================

// Tiga baris ini akan membantu mendiagnosis error tersembunyi
error_reporting(E_ALL); 
ini_set('display_errors', 1);

// Pastikan sesi dimulai di awal file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi_baru.php'; 

// Pastikan user sudah login dan ada ID transaksi
if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirect ke halaman login atau produk jika tidak memenuhi syarat
    header("Location: " . (isset($_SESSION['user_id']) ? "produk.php" : "login.php")); 
    exit();
}

$id_transaksi = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$message = "";

// 1. Ambil detail transaksi (hanya yang dimiliki user yang sedang login)
$sql_detail = "SELECT 
    t.id_transaksi, 
    t.tanggal, 
    t.total_harga, 
    t.status, 
    t.player_id, 
    t.tier_beli,
    p.nama_produk, 
    b.metode AS metode_bayar
FROM 
    Transaksi t
JOIN 
    Produk p ON t.id_produk = p.id_produk
LEFT JOIN 
    Pembayaran b ON t.id_transaksi = b.id_transaksi
WHERE 
    t.id_transaksi = ? AND t.id_user = ?";

$stmt_detail = $koneksi->prepare($sql_detail);

if ($stmt_detail === false) {
    die("Error SQL saat menyiapkan detail transaksi: " . $koneksi->error);
}

$stmt_detail->bind_param("ii", $id_transaksi, $user_id);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

if ($result_detail->num_rows === 0) {
    die("Transaksi tidak ditemukan atau bukan milik Anda.");
}

$detail = $result_detail->fetch_assoc();

// Format data untuk tampilan
$tanggal_transaksi = date('d F Y, H:i:s', strtotime($detail['tanggal']));
$total_harga_formatted = 'Rp ' . number_format($detail['total_harga'], 0, ',', '.');
$status_transaksi = htmlspecialchars($detail['status']);

// Tentukan class CSS berdasarkan status
$status_class = "status-" . str_replace(' ', '', $status_transaksi);
$status_message_class = strtolower($status_transaksi);

// Tentukan pesan dan judul berdasarkan status
$status_title = "Pembayaran " . $status_transaksi;
$status_description = "";

switch ($status_transaksi) {
    case 'Success':
        $status_title = "🎉 Pembayaran Berhasil!";
        $status_description = "Pesanan Diamond Anda telah diproses dan akan segera dikirimkan ke ID Pemain Anda. Terima kasih!";
        break;
    case 'Pending':
        $status_title = "⏳ Menunggu Pembayaran";
        // Di aplikasi nyata, Anda akan menampilkan instruksi pembayaran di sini.
        $status_description = "Kami masih menunggu konfirmasi pembayaran Anda melalui **" . htmlspecialchars($detail['metode_bayar']) . "**. Silakan selesaikan pembayaran sesuai instruksi yang mungkin dikirim ke email Anda."; 
        break;
    case 'Failed':
        $status_title = "❌ Pembayaran Gagal";
        $status_description = "Pembayaran Anda gagal diproses. Silakan coba lagi atau gunakan metode pembayaran lain.";
        break;
    default:
        $status_title = "Status Tidak Dikenal";
        $status_description = "Status transaksi ini tidak dapat diverifikasi.";
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pembayaran - <?php echo $detail['id_transaksi']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --bg-color: #f0f2f5; /* Latar belakang agak abu */
            --card-bg: #ffffff;
            --text-dark: #212529;
            --text-light: #6c757d;
            --success-color: #28a745;
            --pending-color: #ffc107;
            --failed-color: #dc3545;
            --border-radius: 1rem;
            --shadow-soft: 8px 8px 15px #d1d9e6, -8px -8px 15px #ffffff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            line-height: 1.6;
            padding: 2rem 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 1.5rem;
        }
        
        .container h2 {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* Kotak Status Pesan */
        .status-message-box {
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        .status-message-box h3 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .status-message-box p {
            font-size: 1rem;
            color: var(--text-dark);
        }

        .status-message-box.success {
            background-color: #d4edda;
            border: 1px solid var(--success-color);
            color: #155724;
        }
        .status-message-box.pending {
            background-color: #fff3cd;
            border: 1px solid var(--pending-color);
            color: #856404;
        }
        .status-message-box.failed {
            background-color: #f8d7da;
            border: 1px solid var(--failed-color);
            color: #721c24;
        }

        /* Detail Card */
        .detail-card {
            background-color: var(--card-bg);
            padding: 2rem;
            border-radius: var(--border-radius);
            /* Soft Shadow (mirip Neumorphism) */
            box-shadow: var(--shadow-soft);
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px dashed #eee;
        }
        
        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 0.95rem;
            color: var(--text-light);
        }

        .detail-value {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-dark);
            text-align: right;
        }

        .detail-item.total-price {
            border-top: 2px solid #ddd;
            margin-top: 1rem;
            padding-top: 1.5rem;
        }
        
        .detail-item.total-price .detail-value {
            font-size: 1.5rem;
            color: var(--failed-color); /* Atau warna yang menonjol */
        }
        
        /* Status Badge di dalam detail */
        .status-Success, .status-Pending, .status-Failed {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 0.3rem;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        
        .status-Success { background-color: #c3e6cb; color: #155724; }
        .status-Pending { background-color: #ffeeba; color: #856404; }
        .status-Failed { background-color: #f5c6cb; color: #721c24; }
        
        /* Tombol Kembali */
        .btn-back-riwayat {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        .btn-back-riwayat:hover {
            background-color: var(--primary-hover);
        }

        /* Responsif */
        @media (max-width: 600px) {
            .container {
                padding: 1rem;
            }
            .detail-card {
                padding: 1.5rem;
            }
            .container h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Detail Transaksi Anda</h2>
        
        <div class="status-message-box <?php echo $status_message_class; ?>">
            <h3><?php echo $status_title; ?></h3>
            <p><?php echo $status_description; ?></p>
        </div>

        <div class="detail-card">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 1.2em; text-align: left;">Informasi Pembelian</h3>

            <div class="detail-item">
                <span class="detail-label">ID Transaksi</span>
                <span class="detail-value"><?php echo htmlspecialchars($detail['id_transaksi']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Produk</span>
                <span class="detail-value"><?php echo htmlspecialchars($detail['nama_produk']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Transaksi</span>
                <span class="detail-value"><?php echo $tanggal_transaksi; ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">ID Pemain</span>
                <span class="detail-value"><?php echo htmlspecialchars($detail['player_id']); ?></span>
            </div>
             <div class="detail-item">
                <span class="detail-label">Tier Dibeli</span>
                <span class="detail-value"><?php echo htmlspecialchars($detail['tier_beli']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Metode Pembayaran</span>
                <span class="detail-value"><?php echo htmlspecialchars($detail['metode_bayar'] ?? 'N/A'); ?></span>
            </div>
            
            <div class="detail-item total-price">
                <span class="detail-label">Total Harga</span>
                <span class="detail-value"><?php echo $total_harga_formatted; ?></span>
            </div>
            
            <div class="detail-item">
                <span class="detail-label">Status Akhir</span>
                <span class="<?php echo $status_class; ?>"><?php echo htmlspecialchars($detail['status']); ?></span>
            </div>
        </div>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="riwayat.php" class="btn-back-riwayat"><i class="fas fa-history"></i> Cek Riwayat Transaksi</a>
        </p>

    </div> 
</body>
</html>
<?php 
// Tutup statement dan koneksi database
$stmt_detail->close();
$koneksi->close(); 
?>