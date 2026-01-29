<?php
// ===================================
// BAGIAN PHP (Logika Login User & Admin)
// ===================================

// Tampilkan error saat development
ini_set('display_errors', 'On');
error_reporting(E_ALL);

// Pastikan sesi dimulai di awal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// PERINGATAN: Pastikan tidak ada karakter sebelum tag <?php di file ini atau koneksi_baru.php

include 'koneksi_baru.php'; // Sertakan koneksi database

$message = '';
$email = $_POST['email'] ?? ''; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // --- Pengecekan Kredensial User/Admin (Database) ---
    // Query ini mengambil ID, password, nama, dan ROLE dari tabel User
    $sql = "SELECT id_user, password, nama, role FROM User WHERE email = ?";
    $stmt = $koneksi->prepare($sql);
    
    // Periksa jika query gagal (seringkali karena kolom 'role' tidak ditemukan)
    if (!$stmt) {
        $message = "Terjadi kesalahan fatal pada query database. Error: " . $koneksi->error;
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verifikasi Password
            if (password_verify($password, $user['password'])) {
                // Login berhasil
                
                // Praktik Keamanan: Regenerasi ID sesi
                session_regenerate_id(true);

                // Atur Sesi berdasarkan data dari database
                $_SESSION['user_id'] = $user['id_user'];
                $_SESSION['user_nama'] = $user['nama'];
                $_SESSION['user_role'] = $user['role']; // KUNCI UTAMA: Ambil role dari database
                
                // Redirect berdasarkan role
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    // Role user biasa
                    header("Location: produk.php");
                }
                exit();
            } else {
                // Password salah
                $message = "Email atau Password salah.";
            }
        } else {
            // User tidak ditemukan
            $message = "Email atau Password salah.";
        }
        $stmt->close();
    }
}
// Tutup koneksi database
if (isset($koneksi) && $koneksi !== null) {
    $koneksi->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> 
    <title>Login ke GameTopUp</title>
    
    <style>
        /* CSS yang Sama seperti sebelumnya untuk tampilan yang konsisten */
        :root {
            --primary-color: #007bff;
            --primary-hover: #0056b3;
            --bg-color: #f8f9fa; 
            --card-bg: #ffffff; 
            --text-dark: #212529;
            --error-color: #dc3545;
            --shadow-soft: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--text-dark);
        }

        .auth-container {
            width: 100%;
            max-width: 400px;
            padding: 2.5rem;
            background-color: var(--card-bg);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-soft);
            transition: all 0.3s ease;
        }

        .auth-container h2 {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .auth-container form {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .auth-container input[type="email"],
        .auth-container input[type="password"] {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 1px solid #ccc;
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .auth-container input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .auth-container button[type="submit"] {
            padding: 0.9rem;
            border: none;
            border-radius: 0.5rem;
            background-color: var(--primary-color);
            color: white;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.1s;
            margin-top: 0.5rem;
        }

        .auth-container button[type="submit"]:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .message-error {
            padding: 0.75rem;
            background-color: #f8d7da; 
            color: var(--error-color) !important;
            border: 1px solid var(--error-color);
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            text-align: center !important;
            font-size: 0.95rem;
        }

        .link-auth {
            text-align: center;
            margin-top: 1.5rem;
            font-size: 0.95rem;
            color: var(--text-dark);
        }

        .link-auth a {
            color: var(--primary-color);
            font-weight: 600;
        }

        @media (max-width: 600px) {
            .auth-container {
                margin: 1rem;
                padding: 1.5rem;
            }
            body {
                align-items: flex-start;
                padding-top: 5vh;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h2><i class="fas fa-lock" style="margin-right: 10px;"></i> Login</h2>
        
        <?php 
        if ($message) {
            echo "<p class='message-error'>$message</p>"; 
        } 
        ?>
        
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Masukkan Email (Pelanggan atau Admin)" required value="<?php echo htmlspecialchars($email); ?>">
            <input type="password" name="password" placeholder="Masukkan Password" required>
            <button type="submit">Login</button>
        </form>
        
        <p class="link-auth">
            Belum punya akun user? <a href="registrasi.php">Daftar di sini</a>
        </p>
    </div>
</body>
</html>