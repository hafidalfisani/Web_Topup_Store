<?php
// Tiga baris ini akan membantu mendiagnosis error tersembunyi
error_reporting(E_ALL); 
ini_set('display_errors', 1);

include 'koneksi_baru.php'; 

// Pastikan user sudah login dan ada ID transaksi
if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: produk.php"); 
    exit();
}

$id_transaksi = intval($_GET['id']);
$user_id = $_SESSION['user_id'];
$message = "";

// 1. Ambil detail transaksi (hanya yang dimiliki user yang sedang login dan statusnya 'Pending')
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
    t.id_transaksi = ? AND t.id_user = ? AND t.status = 'Pending'";

$stmt_detail = $koneksi->prepare($sql_detail);

if ($stmt_detail === false) {
    die("Error SQL saat menyiapkan detail transaksi: " . $koneksi->error);
}

$stmt_detail->bind_param("ii", $id_transaksi, $user_id);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

if ($result_detail->num_rows === 0) {
    // Transaksi tidak ditemukan atau bukan milik user, atau sudah selesai/gagal.
    // Redirect ke halaman final jika statusnya bukan Pending lagi.
    header("Location: pembayaran_final.php?id=" . $id_transaksi);
    exit();
}

$detail = $result_detail->fetch_assoc();
$stmt_detail->close();

// Format data untuk tampilan
$tanggal_transaksi = (new DateTime($detail['tanggal']))->format('d M Y H:i:s');
$total_harga_formatted = 'Rp ' . number_format($detail['total_harga'], 0, ',', '.');
$metode_bayar = htmlspecialchars($detail['metode_bayar']);
$status_class = strtolower($detail['status']);

// Simulasi Nomor VA atau ID Pembayaran
$nomor_pembayaran = $metode_bayar == "BCA Virtual Account" ? "1234567890123" : 
                    ($metode_bayar == "DANA" ? "0812XXXXXX (Otomatis)" : 
                    ($metode_bayar == "Gopay" ? "Tunjukkan QR Code di Aplikasi" : "Cek Aplikasi Pembayaran Anda"));

?>
<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <title>Pembayaran - Transaksi #<?php echo $id_transaksi; ?></title>
    <link rel="stylesheet" href="style.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container small-container">
        
        <div class="header-topup">
            <h1>Selesaikan Pembayaran</h1>
            <p>Transaksi Anda **#<?php echo $id_transaksi; ?>** menunggu pembayaran.</p>
        </div>

        <!-- Kotak Status Pesan -->
        <div class="status-message-box pending" id="status-box">
            <i class="fas fa-clock"></i>
            <h3>Menunggu Pembayaran (<?php echo htmlspecialchars($detail['status']); ?>)</h3>
            <p>Harap transfer sebesar <span class="total-price-display"><?php echo $total_harga_formatted; ?></span> ke detail di bawah.</p>
            <p>Batas waktu pembayaran: <strong id="countdown">15:00</strong></p>
        </div>

        <div class="invoice-card card">
            <h2>Detail Pembayaran</h2>

            <div class="detail-item detail-important">
                <span class="detail-label">Total Pembayaran</span>
                <span class="detail-value total-price-display"><?php echo $total_harga_formatted; ?></span>
            </div>
            
            <div class="detail-item">
                <span class="detail-label">Metode Pembayaran</span>
                <span class="detail-value"><?php echo $metode_bayar; ?></span>
            </div>
            
            <div class="detail-item detail-important">
                <span class="detail-label">Nomor Tujuan</span>
                <span class="detail-value" id="nomor-pembayaran"><?php echo htmlspecialchars($nomor_pembayaran); ?></span>
                <button class="btn-copy" onclick="copyToClipboard('nomor-pembayaran')"><i class="fas fa-copy"></i> Salin</button>
            </div>
            
            <div class="detail-item">
                <span class="detail-label">Produk</span>
                <span class="detail-value"><?php echo htmlspecialchars($detail['nama_produk']) . ' (' . htmlspecialchars($detail['tier_beli']) . ')'; ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">ID Pemain</span>
                <span class="detail-value"><?php echo htmlspecialchars($detail['player_id']); ?></span>
            </div>
            <div class="detail-item">
                <span class="detail-label">Tanggal Transaksi</span>
                <span class="detail-value"><?php echo $tanggal_transaksi; ?></span>
            </div>
        </div>

        <!-- Tombol Aksi Simulasi (Hanya untuk keperluan demonstrasi) -->
        <div class="simulation-actions card">
            <p><strong>Simulasi:</strong> Setelah Anda membayar, sistem akan mengkonfirmasi status. (Klik tombol di bawah untuk simulasi notifikasi sukses).</p>
            <button class="btn-primary" id="btn-konfirmasi-sukses">
                <i class="fas fa-check-circle"></i> Selesai Bayar (Simulasi Sukses)
            </button>
        </div>
        
        <p style="text-align: center; margin-top: 30px;">
            <a href="riwayat.php" class="btn-back-riwayat">&larr; Cek Riwayat Transaksi</a>
        </p>

    </div> 

    <script>
        // --- Countdown Timer Simulasi (15 Menit) ---
        function startCountdown(duration, display) {
            let timer = duration, minutes, seconds;
            const interval = setInterval(function () {
                minutes = parseInt(timer / 60, 10);
                seconds = parseInt(timer % 60, 10);

                minutes = minutes < 10 ? "0" + minutes : minutes;
                seconds = seconds < 10 ? "0" + seconds : seconds;

                display.textContent = minutes + ":" + seconds;

                if (--timer < 0) {
                    clearInterval(interval);
                    display.textContent = "Waktu Habis";
                    // Nonaktifkan tombol simulasi jika waktu habis
                    document.getElementById('btn-konfirmasi-sukses').disabled = true;
                    document.getElementById('status-box').querySelector('h3').textContent = "Transaksi Dibatalkan";
                    document.getElementById('status-box').classList.remove('pending');
                    document.getElementById('status-box').classList.add('error');
                }
            }, 1000);
        }
        
        const display = document.getElementById('countdown');
        // 15 menit * 60 detik
        startCountdown(60 * 15, display); 

        // --- Fungsi Salin ke Clipboard ---
        function copyToClipboard(elementId) {
            const textToCopy = document.getElementById(elementId).textContent;
            
            // Menggunakan execCommand('copy') sebagai fallback yang lebih umum di lingkungan iframe
            const tempInput = document.createElement('input');
            tempInput.value = textToCopy;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);

            // Ganti teks tombol sebentar untuk konfirmasi
            const copyButton = document.getElementById(elementId).nextElementSibling;
            copyButton.innerHTML = '<i class="fas fa-check"></i> Tersalin!';
            setTimeout(() => {
                copyButton.innerHTML = '<i class="fas fa-copy"></i> Salin';
            }, 1500);
        }
        window.copyToClipboard = copyToClipboard; // Agar bisa diakses dari HTML
        
        // --- Logika Simulasi Konfirmasi Sukses ---
        document.getElementById('btn-konfirmasi-sukses').addEventListener('click', function() {
            const btn = this;
            btn.textContent = 'Memproses...';
            btn.disabled = true;

            // Kirim request ke konfirmasi_sukses.php
            fetch('konfirmasi_sukses.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `id_transaksi=<?php echo $id_transaksi; ?>`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Redirect ke halaman final setelah sukses
                    // Ini mensimulasikan pembaruan status oleh callback payment gateway
                    alertModal("Pembayaran Berhasil Dikonfirmasi!", 
                               "Status transaksi Anda telah diubah menjadi **Success**. Anda akan diarahkan ke halaman detail.",
                               () => { window.location.href = `pembayaran_final.php?id=<?php echo $id_transaksi; ?>`; });
                } else {
                    alertModal("Gagal Simulasi", 
                               "Gagal mengupdate status transaksi di database. Coba lagi.",
                               () => { btn.textContent = 'Selesai Bayar (Simulasi Sukses)'; btn.disabled = false; });
                }
            })
            .catch(error => {
                alertModal("Error Jaringan", 
                           "Terjadi kesalahan saat berkomunikasi dengan server.",
                           () => { btn.textContent = 'Selesai Bayar (Simulasi Sukses)'; btn.disabled = false; });
            });
        });

        // --- Custom Alert Modal (Ganti alert() bawaan) ---
        function alertModal(title, message, callback = null) {
            const existingModal = document.getElementById('custom-alert-modal');
            if (existingModal) existingModal.remove();

            const modalHtml = `
                <div id="custom-alert-modal" class="modal-overlay">
                    <div class="modal-content card">
                        <h3>${title}</h3>
                        <p>${message.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')}</p>
                        <button class="btn-primary" id="modal-ok-button">OK</button>
                    </div>
                </div>
            `;
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            const modalOverlay = document.getElementById('custom-alert-modal');
            const okButton = document.getElementById('modal-ok-button');

            okButton.onclick = () => {
                modalOverlay.remove();
                if (callback) callback();
            };
        }
        
        // Tambahkan styling untuk Modal di runtime agar tidak mengganggu style.css
        const modalStyle = document.createElement('style');
        modalStyle.innerHTML = `
            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            }
            .modal-content {
                padding: 30px;
                max-width: 400px;
                text-align: center;
                border-radius: 12px;
                box-shadow: var(--shadow-soft-light);
            }
            .modal-content h3 {
                margin-top: 0;
                color: var(--color-accent-blue);
            }
            .modal-content p {
                margin-bottom: 20px;
                color: var(--color-text-dark);
            }
            #modal-ok-button {
                padding: 10px 20px;
                font-size: 1em;
            }
        `;
        document.head.appendChild(modalStyle);
    </script>
</body>
</html>
<?php $koneksi->close(); ?>