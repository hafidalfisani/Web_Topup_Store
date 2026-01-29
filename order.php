<?php
// ===================================
// BAGIAN PHP (Logika & Proses Database)
// ===================================

// Mulai Output Buffering
// Ini penting untuk menggunakan header() setelah output HTML telah dimulai, 
// seperti yang terjadi dengan penanganan error atau redirect.
ob_start(); 

// Koneksi ke Database
// Pastikan file 'koneksi_baru.php' sudah ada dan berisi variabel $koneksi
include 'koneksi_baru.php'; 

// Cek Sesi Login
// Jika user_id belum diset (belum login), redirect ke produk.php
if (!isset($_SESSION['user_id'])) {
    header("Location: produk.php");
    exit();
}

// Ambil ID Produk dari URL (GET request)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: produk.php");
    exit();
}
$id_produk = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$message = ""; // Variabel untuk pesan status

// --- 2. Ambil Detail Produk dari Database ---
$sql_produk = "SELECT nama_produk, gambar_produk FROM Produk WHERE id_produk = ?";
$stmt_produk = $koneksi->prepare($sql_produk);
$stmt_produk->bind_param("i", $id_produk);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();

if ($result_produk->num_rows === 0) {
    header("Location: produk.php");
    exit();
}
$produk = $result_produk->fetch_assoc();
$stmt_produk->close();

// ----------------------------------------------------
// !!! HARDCODE TIER HARGA !!!
// Data tier dan harga (seharusnya diambil dari DB, tapi disini di-hardcode)
$tiers = [
    't1' => ['jumlah' => 50, 'harga' => 15000, 'deskripsi' => '50 Diamond'],
    't2' => ['jumlah' => 100, 'harga' => 25000, 'deskripsi' => '100 Diamond'],
    't3' => ['jumlah' => 250, 'harga' => 50000, 'deskripsi' => '250 Diamond'],
    't4' => ['jumlah' => 500, 'harga' => 95000, 'deskripsi' => '500 Diamond (Bonus 5%)'],
    't5' => ['jumlah' => 750, 'harga' => 140000, 'deskripsi' => '750 Diamond (Bonus 7%)'],
    't6' => ['jumlah' => 1000, 'harga' => 180000, 'deskripsi' => '1000 Diamond (Bonus 10%)'],
    't7' => ['jumlah' => 1500, 'harga' => 260000, 'deskripsi' => '1500 Diamond (Bonus 12%)'],
    't8' => ['jumlah' => 2000, 'harga' => 340000, 'deskripsi' => '2000 Diamond (Bonus 15%)'],
];

// --- Daftar Metode Pembayaran ---
$payment_methods = [
    'gopay' => ['nama' => 'Gopay', 'logo' => 'https://placehold.co/50x25/03A678/ffffff?text=GOPAY'],
    'dana' => ['nama' => 'DANA', 'logo' => 'https://placehold.co/50x25/108DDA/ffffff?text=DANA'],
    'ovo' => ['nama' => 'OVO', 'logo' => 'https://placehold.co/50x25/5F3596/ffffff?text=OVO'],
    'bca' => ['nama' => 'Transfer BCA', 'logo' => 'https://placehold.co/50x25/00479A/ffffff?text=BCA'],
    'mandiri' => ['nama' => 'Transfer Mandiri', 'logo' => 'https://placehold.co/50x25/003472/ffffff?text=Mandiri'],
];
// ----------------------------------------------------

// --- 3. Proses Form Submission (Metode POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validasi input
    $player_id = isset($_POST['player_id']) ? trim($_POST['player_id']) : '';
    $tier_key = isset($_POST['tier_id']) ? $_POST['tier_id'] : '';
    $metode_bayar_key = isset($_POST['metode_bayar']) ? $_POST['metode_bayar'] : '';
    $topup_custom = isset($_POST['topup_custom']) ? intval($_POST['topup_custom']) : 0;

    if (empty($player_id) || empty($metode_bayar_key) || !isset($payment_methods[$metode_bayar_key])) {
        $message = "Error: Pastikan ID Pemain dan Metode Pembayaran sudah dipilih.";
    } else if (empty($tier_key) && $topup_custom <= 0) {
        $message = "Error: Pilih Nominal Top Up preset atau masukkan nominal custom.";
    } else {
        // Tentukan apakah menggunakan tier preset atau custom
        if (!empty($tier_key) && isset($tiers[$tier_key])) {
            $selected_tier = $tiers[$tier_key];
            $total_harga = $selected_tier['harga'];
            $tier_deskripsi = $selected_tier['deskripsi'];
        } else if ($topup_custom > 0) {
            // Custom nominal
            $total_harga = $topup_custom;
            $tier_deskripsi = "Custom Top Up";
        } else {
            $message = "Error: Nominal Top Up tidak valid.";
        }
        
        if (empty($message)) {
            $metode_bayar_nama = $payment_methods[$metode_bayar_key]['nama'];
            
            // 1. Mulai Transaksi di tabel Transaksi (status Awal: Pending)
            $status_awal = "Pending";
            $tanggal_transaksi = date("Y-m-d H:i:s"); // Ambil tanggal dan waktu saat ini

            $sql_transaksi = "INSERT INTO Transaksi (id_user, id_produk, player_id, tier_beli, total_harga, status, tanggal) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_transaksi = $koneksi->prepare($sql_transaksi);
            
            if ($stmt_transaksi === false) {
               $message = "Error SQL Transaksi: " . $koneksi->error;
            } else {
                // "iisdiss" -> i=integer, s=string, d=double/float
                $stmt_transaksi->bind_param("iisdiss", 
                    $user_id, 
                    $id_produk, 
                    $player_id, 
                    $tier_deskripsi, 
                    $total_harga, 
                    $status_awal, 
                    $tanggal_transaksi
                );

                if ($stmt_transaksi->execute()) {
                    $id_transaksi_baru = $koneksi->insert_id; // Ambil ID Transaksi yang baru dibuat
                    $stmt_transaksi->close();

                    // 2. Catat Detail Pembayaran di tabel Pembayaran
                    $sql_pembayaran = "INSERT INTO Pembayaran (id_transaksi, metode) VALUES (?, ?)";
                    $stmt_pembayaran = $koneksi->prepare($sql_pembayaran);
                    
                    if ($stmt_pembayaran === false) {
                       $message = "Error SQL Pembayaran: " . $koneksi->error;
                    } else {
                        $metode_bayar_nama = $payment_methods[$metode_bayar_key]['nama'];
                        $stmt_pembayaran->bind_param("is", $id_transaksi_baru, $metode_bayar_nama);

                        if ($stmt_pembayaran->execute()) {
                            $stmt_pembayaran->close();
                            
                            // Sukses: Redirect ke halaman konfirmasi pembayaran
                            header("Location: pembayaran_final.php?id=" . $id_transaksi_baru);
                            exit();
                        } else {
                            $message = "Error: Gagal mencatat pembayaran. Error: " . $stmt_pembayaran->error;
                        }
                    }
                } else {
                    $message = "Error: Gagal membuat transaksi. Error: " . $stmt_transaksi->error;
                }
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
    <title>Beli Diamond - <?php echo htmlspecialchars($produk['nama_produk']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    
    <style>
        :root {
            --primary-color: #007bff; /* Biru terang untuk aksi utama */
            --primary-hover: #0056b3;
            --secondary-color: #6c757d;
            --bg-color: #f8f9fa; /* Latar belakang halaman */
            --card-bg: #ffffff; /* Latar belakang card/section */
            --text-dark: #212529;
            --text-light: #6c757d;
            --success-color: #28a745;
            --error-color: #dc3545;
            --shadow-light: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --border-radius: 0.5rem;
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
        }

        a {
            text-decoration: none;
            color: var(--primary-color);
        }

        /* Header & Navigation */
        .header {
            background-color: var(--card-bg);
            padding: 1rem 1rem;
            box-shadow: var(--shadow-light);
            display: flex;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header h2 {
            margin: 0 auto; 
            font-size: 1.25rem;
            font-weight: 600;
        }

        .back-link {
            color: var(--primary-color);
            font-weight: 500;
            display: flex;
            align-items: center;
            position: absolute; 
            left: 1rem;
        }

        .back-link i {
            margin-right: 0.5rem;
        }

        /* Container & Layout */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 1rem;
        }

        .order-page {
            padding-bottom: 8rem; 
        }

        .card-section {
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-light);
        }

        .section-title {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            border-bottom: 1px solid #eee;
            padding-bottom: 0.5rem;
        }

        .step-badge {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 2rem;
            height: 2rem;
            background-color: var(--primary-color);
            color: white;
            border-radius: 50%;
            font-weight: 700;
            font-size: 1rem;
            margin-right: 0.75rem;
        }

        .section-title h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        /* Product Detail Header */
        .product-header-detail {
            display: flex;
            align-items: center;
            gap: 1rem;
            background-color: var(--card-bg);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-light);
        }

        .product-image-small {
            width: 4rem;
            height: 4rem;
            flex-shrink: 0;
            border-radius: 0.5rem;
            overflow: hidden;
            border: 1px solid #ddd;
        }

        .product-image-small img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-title-detail h3 {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0 0 0.25rem 0;
        }

        .text-light {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        /* Form Inputs & Steps */
        .input-form-text {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ccc;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .input-form-text:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        /* Step 2: Pilih Nominal Top Up (Tier Grid) */
        .tier-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); 
            gap: 1rem;
        }

        .tier-card {
            display: block;
            cursor: pointer;
            background-color: var(--bg-color);
            border: 2px solid #ddd;
            border-radius: var(--border-radius);
            transition: all 0.2s ease;
            text-align: center;
            padding: 1rem;
            position: relative;
        }

        /* Sembunyikan radio button bawaan */
        .tier-card input[type="radio"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* Style saat dipilih */
        .tier-card input[type="radio"]:checked + .tier-content {
            border-color: var(--primary-color);
            background-color: #eaf3ff; 
        }

        .tier-card input[type="radio"]:checked + .tier-content .tier-amount {
            color: var(--primary-color);
        }

        .tier-card:has(input[type="radio"]:checked) {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.4);
        }

        .tier-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
        }

        .tier-icon {
            font-size: 1.5rem;
            color: #ffd700; 
            margin-bottom: 0.5rem;
        }

        .tier-amount {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .tier-label {
            font-size: 0.8rem;
            color: var(--text-light);
        }

        .tier-price {
            margin-top: 0.5rem;
            font-size: 1rem;
            font-weight: 600;
            color: var(--error-color); 
        }

        /* Custom Topup Section */
        .custom-topup-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #eee;
        }

        .custom-topup-label {
            display: block;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.75rem;
        }

        .custom-topup-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #ccc;
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .custom-topup-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .custom-topup-note {
            font-size: 0.85rem;
            color: var(--text-light);
            margin-top: 0.5rem;
        }

        /* Step 3: Pilih Metode Pembayaran */
        .payment-methods-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .payment-option {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            background-color: var(--bg-color);
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.2s ease;
        }

        /* Style saat dipilih */
        .payment-option:has(input[type="radio"]:checked) {
            border-color: var(--primary-color);
            background-color: #eaf3ff;
        }

        .payment-option input[type="radio"] {
            display: none; 
        }

        .payment-content {
            display: flex;
            align-items: center;
            flex-grow: 1; 
        }

        .payment-logo {
            width: 50px;
            height: 25px;
            object-fit: contain;
            margin-right: 1rem;
            border-radius: 4px;
            border: 1px solid #eee;
        }

        .payment-name {
            font-weight: 600;
            color: var(--text-dark);
        }

        .payment-indicator {
            width: 1rem;
            height: 1rem;
            border: 2px solid #ccc;
            border-radius: 50%;
            flex-shrink: 0;
            position: relative;
            transition: all 0.2s ease;
        }

        .payment-option input[type="radio"]:checked + .payment-content + .payment-indicator {
            border-color: var(--primary-color);
        }

        .payment-option input[type="radio"]:checked + .payment-content + .payment-indicator::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 0.5rem;
            height: 0.5rem;
            background-color: var(--primary-color);
            border-radius: 50%;
        }

        /* Sticky Footer Summary */
        .sticky-footer-summary {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: var(--card-bg);
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            z-index: 999;
        }

        .summary-price-box {
            display: flex;
            flex-direction: column;
        }

        .final-harga {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--error-color); 
            line-height: 1;
        }

        .btn-primary-full {
            flex-grow: 1;
            max-width: 60%; 
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--border-radius);
            background-color: var(--primary-color);
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary-full:hover:not(:disabled) {
            background-color: var(--primary-hover);
        }

        /* Style untuk tombol yang Disabled/Belum Siap */
        .btn-primary-full:disabled {
            background-color: var(--secondary-color);
            cursor: not-allowed;
            opacity: 0.7;
        }

        /* Status Message Box */
        .status-message-box {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1rem;
        }

        .status-message-box.error {
            background-color: #f8d7da; 
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }

        .status-message-box.error h3 {
            margin-top: 0;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-message-box p {
            margin: 0.5rem 0 0 0;
            font-size: 0.95rem;
        }

        /* Media Queries (Responsiveness) */
        @media (max-width: 600px) {
            .container {
                padding: 0.5rem;
            }

            .card-section {
                padding: 1rem;
            }
            
            .tier-grid {
                grid-template-columns: repeat(2, 1fr); 
            }

            .header h2 {
                font-size: 1.1rem;
            }

            .back-link {
                font-size: 0.9rem;
            }

            .sticky-footer-summary {
                flex-direction: column;
                align-items: stretch;
                padding: 0.75rem;
            }
            
            .summary-price-box {
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                width: 100%;
            }

            .btn-primary-full {
                max-width: 100%;
                width: 100%;
                font-size: 1.05rem;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="produk.php" class="back-link"><i class="fas fa-chevron-left"></i> Kembali</a>
        <h2>Detail Top Up</h2>
    </div>

    <div class="container order-page">
        
        <?php if ($message): ?>
            <div class="status-message-box error">
                <h3><i class="fas fa-exclamation-triangle"></i> Gagal!</h3>
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <div class="product-header-detail">
            <div class="product-image-small">
                <?php $image_path = 'images/' . htmlspecialchars($produk['gambar_produk']); ?>
                <?php if (file_exists($image_path) && !empty($produk['gambar_produk'])): ?>
                    <img src="<?php echo $image_path; ?>" alt="Topup <?php echo htmlspecialchars($produk['nama_produk']); ?>">
                <?php else: ?>
                    <img src="images/placeholder.jpg" alt="Gambar Tidak Tersedia">
                <?php endif; ?>
            </div>
            <div class="product-title-detail">
                <h3><?php echo htmlspecialchars($produk['nama_produk']); ?></h3>
                <p class="text-light">Top Up Diamond Instan, Proses Otomatis.</p>
            </div>
        </div>

        <form method="POST" action="order.php?id=<?php echo $id_produk; ?>" class="order-form">
            
            <div class="card-section">
                <div class="section-title">
                    <span class="step-badge">1</span>
                    <h4>Masukkan ID Pemain</h4>
                </div>
                <input type="text" name="player_id" id="player_id" placeholder="Contoh: 12345678" required class="input-form-text" value="<?php echo isset($_POST['player_id']) ? htmlspecialchars($_POST['player_id']) : ''; ?>">
            </div>

            <div class="card-section">
                <div class="section-title">
                    <span class="step-badge">2</span>
                    <h4>Pilih Nominal Top Up</h4>
                </div>
                <div class="tier-grid">
                    <?php foreach ($tiers as $key => $tier): ?>
                    <label class="tier-card">
                        <input type="radio" name="tier_id" value="<?php echo $key; ?>" data-harga="<?php echo $tier['harga']; ?>" required <?php echo (isset($_POST['tier_id']) && $_POST['tier_id'] == $key) ? 'checked' : ''; ?>>
                        <div class="tier-content">
                            <i class="fas fa-gem tier-icon"></i>
                            <span class="tier-amount"><?php echo number_format($tier['jumlah'], 0, ',', '.'); ?></span>
                            <span class="tier-label">Diamond</span>
                            <span class="tier-price">Rp <?php echo number_format($tier['harga'], 0, ',', '.'); ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                
                <div class="custom-topup-section">
                    <label class="custom-topup-label">Atau Masukkan Nominal Custom (Rp)</label>
                    <input type="number" name="topup_custom" id="topup_custom" placeholder="Contoh: 50000" min="0" step="1000" class="custom-topup-input" value="<?php echo isset($_POST['topup_custom']) && !empty($_POST['tier_id']) == false ? htmlspecialchars($_POST['topup_custom']) : ''; ?>">
                    <div class="custom-topup-note">Kosongkan jika memilih nominal preset di atas. Nominal custom akan diabaikan jika ada pilihan preset.</div>
                </div>
            </div>

            <div class="card-section">
                <div class="section-title">
                    <span class="step-badge">3</span>
                    <h4>Pilih Metode Pembayaran</h4>
                </div>
                <div class="payment-methods-list">
                    <?php foreach ($payment_methods as $key => $method): ?>
                    <label class="payment-option">
                        <input type="radio" name="metode_bayar" value="<?php echo $key; ?>" required <?php echo (isset($_POST['metode_bayar']) && $_POST['metode_bayar'] == $key) ? 'checked' : ''; ?>>
                        <div class="payment-content">
                            <img src="<?php echo $method['logo']; ?>" alt="<?php echo $method['nama']; ?> Logo" class="payment-logo">
                            <span class="payment-name"><?php echo $method['nama']; ?></span>
                        </div>
                        <span class="payment-indicator"></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="sticky-footer-summary">
                <div class="summary-price-box">
                    <span class="text-light">Total Pembayaran:</span>
                    <span class="final-harga" id="final-harga">Rp 0</span>
                </div>
                <button type="submit" id="submit-button" disabled class="btn-primary-full">
                    Pilih Nominal Top Up
                </button>
            </div>
        </form>

    <div> <script>
        const tierRadios = document.querySelectorAll('input[name="tier_id"]');
        const paymentRadios = document.querySelectorAll('input[name="metode_bayar"]');
        const customTopupInput = document.getElementById('topup_custom');
        const finalHargaSpan = document.getElementById('final-harga');
        const submitButton = document.getElementById('submit-button');

        function updatePriceAndButton() {
            // 1. Cek Nominal Top Up yang dipilih (preset atau custom)
            const selectedTier = document.querySelector('input[name="tier_id"]:checked');
            const customTopupValue = parseFloat(customTopupInput.value) || 0;
            
            // 2. Cek Metode Pembayaran yang dipilih
            const selectedPay = document.querySelector('input[name="metode_bayar"]:checked');
            
            // 3. Cek apakah ID Pemain sudah diisi
            const playerIdInput = document.getElementById('player_id');
            const playerIdFilled = playerIdInput.value.trim() !== '';

            // Tentukan harga yang digunakan
            let harga = 0;
            const hasPresetTier = selectedTier !== null;
            const hasCustomTopup = customTopupValue > 0;
            const hasAnyTopup = hasPresetTier || hasCustomTopup;

            if (hasPresetTier) {
                harga = parseFloat(selectedTier.getAttribute('data-harga'));
            } else if (hasCustomTopup) {
                harga = customTopupValue;
            }

            // Status Global
            const isReady = hasAnyTopup && selectedPay && playerIdFilled;

            if (isReady) {
                // Format harga Rupiah
                const formattedPrice = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(harga);

                // Update UI
                finalHargaSpan.textContent = formattedPrice;
                submitButton.disabled = false;
                submitButton.textContent = `Pilih & Bayar: ${formattedPrice}`;

            } else if (hasAnyTopup) {
                // Jika tier/custom sudah ada tapi pembayaran/ID belum
                const formattedPrice = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(harga);

                finalHargaSpan.textContent = formattedPrice;
                submitButton.disabled = true;
                
                if (!playerIdFilled) {
                    submitButton.textContent = 'Isi ID Pemain Dahulu';
                } else if (!selectedPay) {
                    submitButton.textContent = 'Pilih Metode Pembayaran';
                }

            } else {
                // Default state
                finalHargaSpan.textContent = 'Rp 0';
                submitButton.disabled = true;
                submitButton.textContent = 'Pilih Nominal Top Up';
            }
        }

        // Tambahkan event listener untuk semua pilihan Diamond
        tierRadios.forEach(radio => {
            radio.addEventListener('change', () => {
                // Clear custom input ketika pilih preset
                if (radio.checked) {
                    customTopupInput.value = '';
                }
                updatePriceAndButton();
            });
        });

        // Tambahkan event listener untuk custom topup
        customTopupInput.addEventListener('input', () => {
            // Uncheck semua preset tier ketika input custom
            if (customTopupInput.value.trim() !== '') {
                tierRadios.forEach(radio => radio.checked = false);
            }
            updatePriceAndButton();
        });

        // Tambahkan event listener untuk semua pilihan Pembayaran
        paymentRadios.forEach(radio => {
            radio.addEventListener('change', updatePriceAndButton);
        });
        
        // Tambahkan event listener untuk input ID Pemain
        document.getElementById('player_id').addEventListener('input', updatePriceAndButton);
        
        // Atur status awal saat halaman dimuat
        updatePriceAndButton(); 
    </script>
</body>
</html>
<?php 
// Tutup koneksi database dan hapus output buffer
$koneksi->close(); 
ob_end_flush();
?>