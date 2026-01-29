<?php
// Pastikan skrip ini hanya dapat diakses melalui POST atau dengan validasi ketat di lingkungan produksi.

// 1. Sertakan koneksi database dan mulai session
include 'koneksi_baru.php';

// Pastikan skrip berjalan hanya jika data yang diperlukan tersedia
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_transaksi'])) {
    
    // Ambil ID Transaksi dari input POST
    $id_transaksi = intval($_POST['id_transaksi']);
    $status_baru = "Success"; // Status yang ingin disetel

    // 2. Query untuk update status di tabel Transaksi
    $sql_update_transaksi = "UPDATE Transaksi SET status = ? WHERE id_transaksi = ?";
    
    // 3. Query untuk update status di tabel Pembayaran (opsional, tergantung desain skema)
    // Asumsi status transaksi saja yang perlu diubah.
    
    $stmt = $koneksi->prepare($sql_update_transaksi);

    if ($stmt === false) {
        die("Error Prepare SQL: " . $koneksi->error);
    }
    
    // Bind parameter: 's' untuk string (status), 'i' untuk integer (id_transaksi)
    $stmt->bind_param("si", $status_baru, $id_transaksi);

    if ($stmt->execute()) {
        // --- LOGIKA SETELAH SUCCESS (Misalnya, Menambah saldo/Diamond ke akun Pemain) ---
        
        // Contoh: Ambil Player ID dan Tier untuk simulasi top-up
        $sql_fetch_data = "SELECT player_id, tier_beli FROM Transaksi WHERE id_transaksi = ?";
        $stmt_fetch = $koneksi->prepare($sql_fetch_data);
        $stmt_fetch->bind_param("i", $id_transaksi);
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();
        $data = $result_fetch->fetch_assoc();
        $stmt_fetch->close();

        // Di sini Anda bisa menambahkan logika untuk memberikan diamond ke Player ID.
        
        // --------------------------------------------------------------------------------
        
        echo json_encode([
            "status" => "success",
            "message" => "Notifikasi sukses berhasil diterima dan status Transaksi #$id_transaksi diupdate menjadi '$status_baru'.",
            "transaksi_id" => $id_transaksi
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Gagal mengupdate status transaksi di database. Error: " . $stmt->error
        ]);
    }

    $stmt->close();
    $koneksi->close();

} else {
    // Jika diakses tanpa metode POST atau tanpa parameter
    echo json_encode([
        "status" => "error",
        "message" => "Akses tidak sah atau parameter ID Transaksi hilang."
    ]);
}
?>