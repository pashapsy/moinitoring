<?php
        $servername = "localhost"; 
        $username = "root"; 
        $password = ""; 
        $dbname = "vemoss1";
        
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
            die("Koneksi ke database gagal: " . $conn->connect_error);
        }

        // Memeriksa koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Memeriksa apakah database sudah ada
$result = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");

if ($result->num_rows == 0) {
    // Database tidak ditemukan, mencoba membuatnya
    $sql = "CREATE DATABASE $dbname";
    if ($conn->query($sql) === TRUE) {
        echo "Database berhasil dibuat.<br>";
    } else {
        die("Error membuat database: " . $conn->error);
    }
}

// Memilih database
$conn->select_db($dbname);

// Memeriksa apakah tabel tbl_log sudah ada
$result = $conn->query("SHOW TABLES LIKE 'tbl_log'");

if ($result->num_rows == 0) {
    // Tabel tidak ditemukan, mencoba membuatnya
    $sql = "CREATE TABLE tbl_log (
        waktu DATETIME NOT NULL,
        suhu FLOAT,
        kadar_asap FLOAT,  -- Menambahkan spasi antara 'kadar_asap' dan 'FLOAT'
        status VARCHAR(50)  -- Mengubah tipe data kondisi menjadi VARCHAR untuk menyimpan teks
    )";
    
    if ($conn->query($sql) === TRUE) {
        echo "Tabel tbl_log berhasil dibuat.<br>";
    } else {
        die("Error membuat tabel: " . $conn->error);
    }
}

// Anda bisa menggunakan $conn untuk query selanjutnya

// Jangan lupa untuk menutup koneksi ketika sudah selesai
// $conn->close();
?>

    