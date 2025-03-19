<!DOCTYPE html>

<head>
    <link rel="stylesheet" id="theme-style" href="light.css">
    <title>Monitoring Suhu, Kelembapan, dan Tegangan</title>
    <style>
        include light.css
    </style>
</head>

<body>

    <label for="mode-switch">Mode Gelap</label>
    <input type="checkbox" id="mode-switch" onchange="toggleMode()">
    <script>
        function toggleMode() {
            var themeStyle = document.getElementById('theme-style');
            var modeSwitch = document.getElementById('mode-switch');

            if (modeSwitch.checked) {
                themeStyle.href = 'dark.css';
            } else {
                themeStyle.href = 'light.css';
            }
        }
    </script>

    <h1>Data Rekap</h1>

    <div class="container" style="width: 98%;">
        <a href="index.php" class="button">Kembali</a>

        <div>
            <label for="searchInput">Cari berdasarkan waktu:</label>
            <input type="text" id="searchInput" placeholder="Masukkan waktu...">
        </div>

        <table>
            <thead>
            </thead>
            <tbody id="searchTableBody">
            </tbody>
        </table>
    </div>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const tableRows = document.querySelectorAll("#dataTableBody tr");

    searchInput.addEventListener("keyup", function () {
        const searchTerm = searchInput.value.toLowerCase();

        tableRows.forEach(function (row) {
            const waktuCell = row.cells[0]; // Kolom pertama adalah kolom waktu

            if (waktuCell) {
                const waktuValue = waktuCell.textContent || waktuCell.innerText;

                if (waktuValue.toLowerCase().includes(searchTerm)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        });
    });
});
</script>

    <div class="container" style="width: 98%;">
        <table>
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Suhu</th>
                    <th>Kadar_asap</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="dataTableBody">
                <?php
                include 'config.php';

                $query = "SELECT waktu, suhu, kadar_asap, status FROM tbl_temperature ORDER BY waktu DESC";
                $result = $conn->query($query);

                // Periksa apakah kueri berhasil dieksekusi
                if ($result === false) {
                    die("Error: " . $conn->error); // Tampilkan pesan kesalahan jika terjadi error
                }

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . ($row["waktu"]) . "</td>";
                        echo "<td>" . ($row["suhu"]) . "°C</td>";
                        echo "<td>" . ($row["kadar_asap"]) . "</td>";
                        echo "<td>" . ($row["status"]) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Tidak ada data.</td></tr>"; // Perbaikan colspan agar sesuai dengan jumlah kolom
                }

                $conn->close();
                ?>

            </tbody>
        </table>
    </div>


</body>

</html>