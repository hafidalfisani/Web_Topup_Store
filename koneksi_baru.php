<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);
date_default_timezone_set('Asia/Jakarta');

// --- Konfigurasi Database ---
$host = "localhost";
$user = "root";      // Sesuaikan jika Anda menggunakan user MySQL lain
$pass = "";          // >>> PERIKSA INI: Coba "" (kosong) atau "root" <<<
$db = "db_topup_game";

// Buat koneksi database
$koneksi = new mysqli($host, $user, $pass, $db);

// Cek apakah koneksi gagal
if ($koneksi->connect_error) {
    // Tulis kegagalan ke log dan hentikan skrip
    error_log("Koneksi Database GAGAL: " . $koneksi->connect_error);
    // Jika Anda melihat pesan ini, masalahnya ada pada $user atau $pass di atas
    die("Sistem dalam perbaikan. (Error Koneksi: " . $koneksi->connect_error . ")");
}

// Mulai session, pastikan ini dijalankan HANYA SETELAH koneksi
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}