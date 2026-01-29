<?php
// ===================================
// BAGIAN PHP (Logika & Proses Database)
// ===================================

// Pastikan sesi dimulai di awal file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Asumsi 'koneksi_baru.php' berisi detail koneksi ke database dan objek $koneksi
include 'koneksi_baru.php';

// Cek apakah pengguna sudah login, jika belum, arahkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil nama pengguna dari sesi untuk ditampilkan di header
$user_name = isset($_SESSION['user_nama']) ? htmlspecialchars($_SESSION['user_nama']) : 'Pengguna';

// 1. Ambil semua data produk KECUALI 'Roblox Robux'
$sql = "SELECT id_produk, nama_produk, gambar_produk 
        FROM Produk 
        WHERE nama_produk != 'Roblox Robux' 
        ORDER BY id_produk ASC";

$result = $koneksi->query($sql);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <title>Katalog Top Up Game - Selamat Datang <?php echo $user_name; ?></title>
    
    <style>
        :root {
            --primary-color: #007bff; /* Biru terang untuk aksi utama */
            --primary-hover: #0056b3;
            --bg-color: #f8f9fa; /* Latar belakang halaman */
            --card-bg: #ffffff; /* Latar belakang card/section */
            --text-dark: #212529;
            --text-light: #6c757d;
            --error-color: #dc3545;
            --shadow-light: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --border-radius: 0.75rem;
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

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.5rem;
        }

        /* Header & Navigasi */
        .header-topup {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            margin-bottom: 2rem;
            border-bottom: 2px solid #ddd;
        }

        .header-topup h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .user-info-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info-menu span {
            font-weight: 500;
            color: var(--text-dark);
            font-size: 0.95rem;
        }

        .nav-link {
            font-weight: 600;
            color: var(--primary-color);
            transition: color 0.3s;
        }
        
        .nav-link:hover {
            color: var(--primary-hover);
        }

        .btn-logout {
            background-color: var(--error-color);
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.3s;
        }

        .btn-logout:hover {
            background-color: #a71d2a;
        }

        /* GRID PRODUK */
        .product-grid {
            display: grid;
            /* Layout default 4 kolom, responsif */
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); 
            gap: 2rem;
        }

        .product-card {
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-light);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        /* Area Gambar */
        .product-card-image {
            width: 100%;
            height: 180px; 
            overflow: hidden;
        }

        .product-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card:hover .product-card-image img {
            transform: scale(1.05);
        }

        /* Area Informasi */
        .product-info {
            padding: 1rem;
            flex-grow: 1;
        }

        .product-info h4 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
            line-height: 1.3;
        }

        /* Tombol Aksi */
        .product-card > a {
            display: block;
            text-align: center;
            padding: 0.75rem 1rem;
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-top: 1px solid var(--primary-hover);
            transition: background-color 0.3s;
        }

        .product-card > a:hover {
            background-color: var(--primary-hover);
        }

        /* Media Queries (Responsiveness) */
        @media (max-width: 992px) {
            .product-grid {
                /* 3 kolom di layar tablet */
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
                gap: 1.5rem;
            }
        }
        
        @media (max-width: 600px) {
            .container {
                padding: 1rem;
            }

            .header-topup {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
                margin-bottom: 1.5rem;
            }

            .header-topup h1 {
                font-size: 1.75rem;
            }

            .user-info-menu {
                flex-wrap: wrap;
                gap: 0.75rem;
            }
            
            .product-grid {
                /* 2 kolom di layar ponsel */
                grid-template-columns: repeat(2, 1fr); 
                gap: 1rem;
            }
            
            .product-card-image {
                height: 120px; 
            }
            
            .product-info h4 {
                font-size: 0.9rem;
            }
            
            .product-card > a {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-topup">
            <h1>GameTopUp</h1>
            <div class="user-info-menu">
                <span>Selamat Datang, **<?php echo $user_name; ?>**!</span>
                <a href="riwayat.php" class="nav-link"><i class="fas fa-history"></i> Riwayat</a>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <div class="product-grid">
            <?php
            if ($result->num_rows > 0) {
                // Loop melalui setiap baris produk
                while ($row = $result->fetch_assoc()) {
                    $image_path = "images/" . htmlspecialchars($row["gambar_produk"]);
                    $product_id = $row["id_produk"];
                    $product_name = htmlspecialchars($row["nama_produk"]);

                    echo "<div class='product-card'>";

                    // --- 1. Area Gambar ---
                    echo "<div class='product-card-image'>";
                    // Pastikan file gambar ada di folder 'images/'
                    if (file_exists($image_path) && !empty($row["gambar_produk"])) {
                        echo "<img src='$image_path' alt='Topup $product_name'>";
                    } else {
                        // Placeholder jika gambar tidak ada
                        echo "<img src='https://placehold.co/600x400/e0e0e0/555555?text=NO+IMAGE' alt='Gambar Tidak Tersedia'>";
                    }
                    echo "</div>";

                    // --- 2. Area Informasi (Nama Produk) ---
                    echo "<div class='product-info'>";
                    echo "<h4>$product_name</h4>";
                    echo "</div>";

                    // --- 3. Tombol Aksi (Arahkan ke order.php) ---
                    echo "<a href='order.php?id=$product_id'>Pesan Sekarang</a>";

                    echo "</div>"; // Tutup product-card
                }
            } else {
                // Pesan jika tidak ada produk (setelah Roblox dihapus atau database kosong)
                echo "<p style='text-align:center; padding: 20px; background: var(--card-bg); border-radius: var(--border-radius); box-shadow: var(--shadow-light); grid-column: 1 / -1; margin-top: 20px;'>Katalog sedang diperbarui. Tidak ada produk tersedia saat ini.</p>";
            }

            $koneksi->close();
            ?>
        </div>
    </div> 
</body>
</html>