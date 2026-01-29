<?php
error_reporting(E_ALL); 
ini_set('display_errors', 1);

// 1. Pastikan sesi dimulai di awal file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi_baru.php'; 

// =================================================================================
// ✅ PEMERIKSAAN OTORISASI ADMIN
// =================================================================================
if (($_SESSION['user_role'] ?? 'user') !== 'admin' || !isset($_SESSION['user_id'])) {
    error_log("Akses Ditolak di admin_dashboard.");
    session_destroy();
    header("Location: login.php?status=denied"); 
    exit();
}

$admin_name = htmlspecialchars($_SESSION['user_nama'] ?? 'Administrator'); 
$admin_id = $_SESSION['user_id']; 
// =================================================================================


$message = $_GET['msg'] ?? ''; // Ambil pesan notifikasi dari URL

// 2. Fungsi untuk format harga
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// 3. Query untuk mengambil transaksi yang statusnya 'Pending'
$sql_pending = "SELECT 
                    t.id_transaksi, 
                    t.tanggal, 
                    u.nama AS nama_user, 
                    p.nama_produk, 
                    t.player_id, 
                    b.metode AS metode_bayar,
                    t.total_harga
                FROM 
                    Transaksi t
                JOIN 
                    User u ON t.id_user = u.id_user
                JOIN 
                    Produk p ON t.id_produk = p.id_produk
                LEFT JOIN 
                    Pembayaran b ON t.id_transaksi = b.id_transaksi
                WHERE 
                    t.status = 'Pending'
                ORDER BY t.tanggal DESC";

$stmt_pending = $koneksi->prepare($sql_pending);
if (!$stmt_pending) {
    die("Error persiapan query: " . $koneksi->error);
}
$stmt_pending->execute();
$result_pending = $stmt_pending->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <style>
        :root {
            --color-primary: #1e90ff; 
            --color-primary-dark: #1c7ed6;
            --color-bg-light: #f4f7f9;
            --color-text-dark: #333;
            --color-success: #28a745;
            --color-reject: #dc3545;
            --color-pending: #ffc107;
            --shadow-soft: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-soft-inset: inset 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--color-bg-light);
            color: var(--color-text-dark);
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow-soft);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        header h1 {
            color: var(--color-primary);
            font-size: 2rem;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-logout {
            padding: 8px 15px;
            background-color: var(--color-reject);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .btn-logout:hover {
            background-color: #c82333;
        }

        .btn-history {
            padding: 8px 15px;
            background-color: var(--color-success);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background-color 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-history:hover {
            background-color: #218838;
        }

        .alert-message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 600;
            text-align: center;
        }

        .alert-success {
            background-color: #d4edda;
            color: var(--color-success);
            border: 1px solid #c3e6cb;
        }
        
        h2 {
            font-size: 1.5rem;
            color: var(--color-text-dark);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--color-primary);
            padding-bottom: 5px;
            display: inline-block;
        }

        .table-responsive {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: var(--shadow-soft-inset);
            border: 1px solid #eee;
        }

        .transaction-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 900px; 
        }

        .transaction-table th, .transaction-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        .transaction-table th {
            background-color: var(--color-primary);
            color: white;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .transaction-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .transaction-table tr:hover {
            background-color: #f1f1f1;
        }

        .transaction-table td {
            font-size: 0.95rem;
        }
        
        .btn-action {
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s, transform 0.1s;
            margin-left: 5px;
        }
        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        .btn-confirm {
            background-color: var(--color-success);
        }

        .btn-reject {
            background-color: var(--color-reject);
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            header {
                flex-direction: column;
                align-items: flex-start;
            }
            .header-actions {
                margin-top: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1><i class="fas fa-chart-line"></i> Dashboard Admin</h1>
            <div class="header-actions">
                <span style="font-weight: 600; color: var(--color-primary);">Halo, **<?php echo $admin_name; ?>**! </span>
                <a href="riwayat.php" class="btn-history"><i class="fas fa-history"></i> Lihat Riwayat</a>
                <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </header>

        <?php 
        if ($message) {
            echo "<div class='alert-message alert-success'>$message</div>";
        }
        ?>

        <h2><i class="fas fa-list-alt"></i> Transaksi Menunggu Konfirmasi</h2>

        <?php if ($result_pending->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="transaction-table">
                    <thead>
                        <tr>
                            <th>ID Transaksi</th>
                            <th>Tanggal</th>
                            <th>User</th>
                            <th>Produk</th>
                            <th>ID Pemain</th>
                            <th>Metode Bayar</th>
                            <th>Total Harga</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result_pending->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id_transaksi']; ?></td>
                                <td><?php echo date('d M Y, H:i', strtotime($row['tanggal'])); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_user']); ?></td>
                                <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                <td><?php echo htmlspecialchars($row['player_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['metode_bayar']); ?></td>
                                <td><?php echo format_rupiah($row['total_harga']); ?></td>
                                <td>
                                    <form action="action_admin.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="id_transaksi" value="<?php echo $row['id_transaksi']; ?>">
                                        <input type="hidden" name="action" value="confirm">
                                        <button type="submit" class="btn-action btn-confirm" onclick="return confirm('Yakin ingin KONFIRMASI Transaksi #<?php echo $row['id_transaksi']; ?>? Tindakan ini akan memproses top-up.');">Konfirmasi</button>
                                    </form>
                                    
                                    <form action="action_admin.php" method="POST" style="display:inline-block;">
                                        <input type="hidden" name="id_transaksi" value="<?php echo $row['id_transaksi']; ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn-action btn-reject" onclick="return confirm('Yakin ingin TOLAK Transaksi #<?php echo $row['id_transaksi']; ?>?');">Tolak</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>
            <p style="text-align:center; padding: 20px; background: #e0e0e0; border-radius: 12px; box-shadow: var(--shadow-soft-inset); color: var(--color-text-dark); margin-top: 30px;">
                Tidak ada transaksi yang menunggu konfirmasi saat ini.
            </p>
        <?php endif; ?>

    </div> 
</body>
</html>
<?php 
if (isset($stmt_pending)) {
    $stmt_pending->close();
}
if (isset($koneksi)) {
    $koneksi->close(); 
}
?>