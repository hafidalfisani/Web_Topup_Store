<?php
// Pastikan session dimulai sebelum mencoba mengakses atau menghancurkannya
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Hancurkan semua variabel session
$_SESSION = array();

// 2. Jika ingin menghancurkan session cookie, hapus juga cookie session
// Catatan: Ini akan menghancurkan session, bukan hanya data session
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hancurkan session
session_destroy();

// 4. Redirect pengguna kembali ke halaman login
header("Location: login.php");
exit();
?>