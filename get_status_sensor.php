
<?php
include 'config.php';

$ $sql = "SELECT MAX(waktu) AS waktu_terakhir FROM tbl_temperature";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $waktuTerakhir = strtotime($row["waktu_terakhir"]); // Konversi ke UNIX timestamp
} else {
    $waktuTerakhir = 0;
}

$conn->close();

// Waktu sekarang
$waktuSekarang = time();

// Selisih waktu dalam detik
$selisihWaktu = $waktuSekarang - $waktuTerakhir;

// Tentukan status sensor
if ($selisihWaktu > 30) {
    $statusSensor = "OFF";
} else {
    $statusSensor = "ON";
}

// Kumpulkan data dalam bentuk array
$data = array(
    "waktu_terakhir" => date("Y-m-d H:i:s", $waktuTerakhir),
    "status_sensor" => $statusSensor
);

// Kirim data sebagai respons JSON
header('Content-Type: application/json');
echo json_encode($data);
?>