<?php
include 'config.php';

$query = "SELECT waktu, suhu, kadar_asap, status FROM tbl_temperature ORDER BY waktu DESC LIMIT 10";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $data = [
        "labels" => [],
        "suhuData" => [],
        "kadar_asapData" => [],
        "statusData" => []
    ];

    while ($row = $result->fetch_assoc()) {
        $data["labels"][] = $row["waktu"];
        $data["suhuData"][] = (float) $row["suhu"];
        $data["kadar_asapData"][] = (float) $row["kadar_asap"];
        $data["statusData"][] = $row["status"];
    }

    echo json_encode($data);
} else {
    echo json_encode([]);
}

$conn->close();
?>
