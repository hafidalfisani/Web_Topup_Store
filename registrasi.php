<?php
// File: registrasi.php

ini_set('display_errors', 'On');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'koneksi_baru.php'; // Menggunakan koneksi yang sama

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // 1. Validasi Password
    if ($password !== $konfirmasi_password) {
        $message = "Konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 6) {
        $message = "Password minimal 6 karakter.";
    } else {
        // 2. Cek apakah Email sudah ada
        $sql_check = "SELECT id_user FROM User WHERE email = ?";
        $stmt_check = $koneksi->prepare($sql_check);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $message = "Email ini sudah terdaftar. Silakan login.";
        } else {
            // 3. Hash Password dan Simpan ke Database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $default_saldo = 0;
            $default_role = 'user'; // Atur role default
            
            $sql_insert = "INSERT INTO User (nama, email, password, saldo, role) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $koneksi->prepare($sql_insert);
            
            if ($stmt_insert) {
                $stmt_insert->bind_param("sssis", $nama, $email, $hashed_password, $default_saldo, $default_role);
                
                if ($stmt_insert->execute()) {
                    // Registrasi Berhasil
                    $_SESSION['registration_success'] = true;
                    header("Location: login.php?msg=Registrasi Berhasil! Silakan Login.");
                    exit();
                } else {
                    $message = "Registrasi Gagal: Terjadi kesalahan database saat menyimpan user.";
                }
                $stmt_insert->close();
            } else {
                $message = "Registrasi Gagal: Terjadi kesalahan database (Prepare Statement).";
            }
        }
        $stmt_check->close();
    }
}
if (isset($koneksi) && $koneksi !== null) {
    $koneksi->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Registrasi User Baru</title>
    <style>
        /* Anda bisa menyalin semua CSS dari login.php ke sini */
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

        /* ... Salin semua style dari login.php ... */
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

        .auth-container input[type="text"],
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
        /* ... */
    </style>
</head>
<body>
    <div class="auth-container">
        <h2><i class="fas fa-user-plus" style="margin-right: 10px;"></i> Registrasi User Baru</h2>
        
        <?php 
        if ($message) {
            echo "<p class='message-error'>$message</p>"; 
        } 
        ?>
        
        <form action="registrasi.php" method="POST">
            <input type="text" name="nama" placeholder="Nama Lengkap" required>
            <input type="email" name="email" placeholder="Email (Contoh: user@gmail.com)" required>
            <input type="password" name="password" placeholder="Buat Password" required>
            <input type="password" name="konfirmasi_password" placeholder="Konfirmasi Password" required>
            <button type="submit">Daftar Sekarang</button>
        </form>
        
        <p class="link-auth">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </p>
    </div>
</body>
</html>