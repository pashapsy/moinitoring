<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" id="theme-style" href="light.css">
    <title>Monitoring Suhu dan Kadar Asap Data Center</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/raphael@2.1.4/raphael-min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/justgage@1.3.0/justgage.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>


<body>
    <label for="mode-switch">Mode Gelap</label>
    <input type="checkbox" id="mode-switch" onchange="toggleMode()">

    <h1>Monitoring Suhu dan Kadar Asap Data Center</h1>
    <h2>
        <a href="https://www.google.com/maps/place/Daerah+Istimewa+Yogyakarta/@-7.8722374,110.0939422,10z/data=!3m1!4b1!4m6!3m5!1s0x2e7a5787bd5b6bc5:0x6d1b92b2cac8b3f0!8m2!3d-7.8753849!4d110.4262088!16zL20vMDE1djc3!5m1!1e1?entry=ttu&g_ep=EgoyMDI0MTIxMS4wIKXMDSoASAFQAw%3D%3D" target="_blank">DIY</a>
    </h2>

    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 20px;">
        <!-- Chart -->
        <div class="container" style="flex: 54%; min-width: 300px;">
            <canvas id="dataChart" width="600" height="300"></canvas>
        </div>

        <!-- Table -->
        <div class="container" style="flex: 39.5%; min-width: 250px;">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Suhu</th>
                        <th>Kadar asap</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody id="dataTableBody">
                    <?php
                    include 'config.php';

                    $query = "SELECT waktu, suhu, kadar_asap, status FROM tbl_temperature ORDER BY waktu DESC LIMIT 10";
                    $result = $conn->query($query);

                    // Periksa apakah kueri berhasil dieksekusi
                    if ($result === false) {
                        die("Error: " . $conn->error); // Tampilkan pesan kesalahan jika terjadi error
                    }

                    $data = array();
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . ($row["waktu"]) . "</td>";
                            echo "<td>" . ($row["suhu"]) . "°C</td>";
                            echo "<td>" . ($row["kadar_asap"]) . "</td>";
                            echo "<td>" . ($row["status"]) . "</td>";
                            echo "</tr>";
                            $data[] = $row;
                        }
                    } else {
                        echo "<tr><td colspan='4'>Tidak ada data.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        var chartData = <?php echo json_encode($data); ?>;

        var labels = chartData.map(function(e) {
            return e.waktu;
        });
        var suhuData = chartData.map(function(e) {
            return e.suhu;
        });
        var asapData = chartData.map(function(e) {
            return e.kadar_asap;
        });

        var ctx = document.getElementById('dataChart').getContext('2d');
        var dataChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Suhu (°C)',
                    data: suhuData,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    fill: false
                }, {
                    label: 'Kadar Asap (ppm)',
                    data: asapData,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    fill: false
                }]
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'minute'
                        },
                        title: {
                            display: true,
                            text: 'Waktu'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: 'Nilai'
                        }
                    }
                }
            }
        });
    </script>

    <!-- Measurement gauges -->
    <div class="container">
        <?php
        include 'config.php';

        // Query to get latest data
        $query = "SELECT waktu, suhu, kadar_asap, status FROM tbl_temperature ORDER BY waktu DESC LIMIT 1";
        $result = $conn->query($query);

        $last_temp = 0;
        $last_asap = 0;
        $last_status = "Tidak tersedia";

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $last_temp = $row["suhu"];
            $last_asap = $row["kadar_asap"];
            $last_status = $row["status"];
        } else {
            echo "<p>Data tidak tersedia.</p>";
        }

        $conn->close();
        ?>


        <!-- Temperature gauge -->
        <div class="col-md-6 col-sm-12 mb-3">
            <div class="well">
                <h4 class="text-center">Suhu (&deg;C)</h4>
                <div id="suhu" class="gauge"></div>
                <div id="suhu-label" class="text-center"></div>
            </div>
        </div>

        <!-- Smoke level gauge -->
        <div class="col-md-6 col-sm-12 mb-3">
            <div class="well">
                <h4 class="text-center">Kadar Asap (ppm)</h4>
                <div id="asap" class="gauge"></div>
                <div id="asap-label" class="text-center"></div>
            </div>
        </div>

    </div>

    <!-- Sensor status display -->
    <?php
    date_default_timezone_set('Asia/Jakarta');
    include 'config.php';

    $sql = "SELECT MAX(waktu) AS waktu_terakhir FROM tbl_temperature";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $waktuTerakhir = strtotime($row["waktu_terakhir"]); // Convert to UNIX timestamp
    } else {
        $waktuTerakhir = 0;
    }

    $conn->close();

    // Current time
    $waktuSekarang = time();

    // Time difference in seconds
    $selisihWaktu = $waktuSekarang - $waktuTerakhir;

    // Determine sensor status
    if ($selisihWaktu > 30) {
        $statusSensor = "OFF";
    } else {
        $statusSensor = "ON";
    }
    ?>

    <div class="container">
        <button class="button" onclick="window.location.href='rekap.php'">Data Rekap</button>

        <div id="status-box">
            <?php
            echo "Terakhir Terhubung: " . date("Y-m-d H:i:s", $waktuTerakhir); // Tampilkan waktu terakhir
            ?>

            <?php
            // Tampilkan pemberitahuan berdasarkan status sensor
            if ($statusSensor === "OFF") {
                echo "<br><span style='color: red;'>Sensor Mati</span>";
            } else {
                echo "<br><span style='color: green;'>Sensor Hidup</span>";
            }
            ?>
        </div>
    </div>

    <!-- Pass PHP variables to JavaScript -->
    <script>
        var suhuValue = <?php echo json_encode($last_temp); ?>;
        var asapValue = <?php echo json_encode($last_asap); ?>;
    </script>

    <!-- Include the main JavaScript file -->
    <script src="script.js"></script>
</body>

</html>