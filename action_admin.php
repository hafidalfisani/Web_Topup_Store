<?php
// File: action_admin.php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi_baru.php'; 

// ========================================================================
// !!! KODE KEAMANAN (DINONAKTIFKAN SEMENTARA UNTUK DEBUGGING) !!!
// AKTIFKAN KEMBALI SETELAH SEMUA FUNGSI BERHASIL
// ========================================================================
/*
if (($_SESSION['user_role'] ?? 'user') !== 'admin') {
    // User tidak terautentikasi atau bukan admin
    header("Location: login.php?status=unauthorized"); 
    exit();
}
*/
// ========================================================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validasi data input
    $id_transaksi = $_POST['id_transaksi'] ?? null;
    $action = $_POST['action'] ?? null;

    if (!$id_transaksi || !$action) {
        header("Location: admin_dashboard.php?msg=Error: ID Transaksi atau Aksi tidak valid.");
        exit();
    }

    $new_status = '';
    $success_msg = '';

    if ($action === 'confirm') {
        $new_status = 'Selesai';
        $success_msg = "Transaksi #$id_transaksi berhasil dikonfirmasi dan status diubah menjadi Selesai.";
        
        // Catatan: Jika Anda memiliki sistem saldo, di sini adalah tempat 
        // Anda akan menambahkan logika untuk mengurangi saldo admin atau 
        // menandai transaksi di sistem top-up eksternal.

    } elseif ($action === 'reject') {
        $new_status = 'Ditolak';
        $success_msg = "Transaksi #$id_transaksi berhasil ditolak dan status diubah menjadi Ditolak.";

        // Catatan: Jika ada pemotongan saldo user di awal, di sini adalah tempat
        // Anda harus menambahkan logika untuk MENGEMBALIKAN saldo (REFUND) user.
        
    } else {
        header("Location: admin_dashboard.php?msg=Error: Aksi tidak dikenal.");
        exit();
    }

    // --- Eksekusi Query Update Status ---
    $sql_update = "UPDATE Transaksi SET status = ? WHERE id_transaksi = ?";
    $stmt_update = $koneksi->prepare($sql_update);

    if ($stmt_update) {
        $stmt_update->bind_param("si", $new_status, $id_transaksi);
        
        if ($stmt_update->execute()) {
            // Sukses, redirect ke dashboard dengan pesan sukses
            header("Location: admin_dashboard.php?msg=" . urlencode($success_msg));
            exit();
        } else {
            // Gagal eksekusi
            $error_msg = "Gagal memperbarui status transaksi di database. Error: " . $stmt_update->error;
            header("Location: admin_dashboard.php?msg=" . urlencode($error_msg));
            exit();
        }
        $stmt_update->close();
    } else {
        // Gagal prepare statement
        $error_msg = "Error persiapan statement database. Error: " . $koneksi->error;
        header("Location: admin_dashboard.php?msg=" . urlencode($error_msg));
        exit();
    }
} else {
    // Jika diakses tidak melalui POST
    header("Location: admin_dashboard.php");
    exit();
}

if (isset($koneksi)) {
    $koneksi->close(); 
}
?>