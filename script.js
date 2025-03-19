// script.js

// This section can handle any additional functionality
// For example, if you want to update the chart dynamically, you can add that logic here.

function toggleMode() {
    const themeStyle = document.getElementById('theme-style');
    if (themeStyle.getAttribute('href') === 'light.css') {
        themeStyle.setAttribute('href', 'dark.css');
    } else {
        themeStyle.setAttribute('href', 'light.css');
    }
}

// Additional functions can be added as needed

// Document ready function
$(document).ready(function() {
    // Initialize gauge for temperature
    var suhuMeter = new JustGage({
        id: "suhu",
        value: suhuValue, // This variable will be defined in index.php
        min: 0,
        max: 100,
        title: "Suhu",
        label: "°C",
        gaugeWidthScale: 0.8,
        levelColors: ['#0000FF'],
        counter: true
    });

    // Initialize gauge for smoke level
    var asapMeter = new JustGage({
        id: "asap",
        value: asapValue, // This variable will be defined in index.php
        min: 0,
        max: 400,
        title: "Kadar Asap",
        label: "ppm",
        gaugeWidthScale: 0.8,
        levelColors: ['#00ff00'],
        counter: true
    });

    // Display value labels
    $('#suhu-label').text(suhuValue + ' °C');
    $('#asap-label').text(asapValue + ' ppm');
    
    
    // Initial load of database data
    loadDataFromDatabase();
});

// Function Untuk memperbarui status sensor
function updateStatusSensor() {
    $.ajax({
        url: 'get_status_sensor.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var statusSensor = response.status_sensor;
            // Update sensor status on page if needed
            var statusSensorElement = document.getElementById("status-sensor");
            if (statusSensorElement) {
                statusSensorElement.textContent = statusSensor;
            }
        }
    });
}

// Function untuk memuat data dari database
function loadDataFromDatabase() {
    $.ajax({
        url: 'get_status_sensor.php',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var waktuTerakhir = response.waktu_terakhir;
            var statusSensor = response.status_sensor;

            // Perbarui tampilan dengan data terbaru
            if (document.getElementById("waktu-terakhir")) {
                document.getElementById("waktu-terakhir").textContent = waktuTerakhir;
            }
            if (document.getElementById("status-sensor")) {
                document.getElementById("status-sensor").textContent = statusSensor;
            }
        }
    });
}

// Function to send WhatsApp notification for temperature
function sendTemperatureWarning(suhuData) {
    if (suhuData.length === 0) return; // If no temperature above threshold, no need to send notification

    var phoneNumber = "628994151709"; // Replace with destination number
    var message = "⚠️ *PERINGATAN! Suhu Berbahaya!* ⚠️\n\n";

    suhuData.forEach((data, index) => {
        message += `${index + 1}. 🌡 Suhu: ${data.suhu}°C\n🕒 Waktu: ${data.waktu}\n\n`;
    });

    message += "Mohon segera ditindaklanjuti! 🚨";

    var url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
    window.open(url, "_blank");
}

//Fungsi untuk mengirim notifikasi WhatsApp tentang tingkat asap
function sendSmokeWarning(asapData) {
    if (!asapData || asapData.length === 0) return; // Jika tidak ada data, keluar dari fungsi

    var phoneNumber = "628994151709"; // Ganti dengan nomor tujuan
    var message = "⚠️ *PERINGATAN DARURAT!* ⚠️\n\n";
    message += "🚨 *Kadar Asap Berbahaya!* 🚨\n";
    message += "Mohon segera lakukan tindakan pencegahan!\n\n";

    asapData.forEach((data) => {
        if (data.kadar_asap > 300 && !uniqueAsapLevels.has(data.kadar_asap)) {
            message += `💨 Kadar Asap: ${data.kadar_asap} ppm\n`;
            message += `🕒 Waktu: ${data.waktu}\n`;
            message += "🚨 *BAHAYA! SEGERA AMANKAN AREA!* 🚨\n\n";
        }
    });

    message += "‼️ *Segera ambil tindakan yang diperlukan!* ‼️";

    var url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
    window.open(url, "_blank");
}


// Function untuk memperbarui bagan dan tabel dengan data baru
function updateChartAndTable(data) {
    // Ekstrak data dari respons JSON
    var labels = data.labels;
    var suhuData = data.suhuData;
    var kadar_asapData = data.kadar_asapData;

    // ====================================================================================
     // Perbarui grafik
     var ctx = document.getElementById("dataChart").getContext("2d");
     var dataChart = new Chart(ctx, {
         type: 'line',
         data: {
             labels: labels,
             datasets: [{
                     label: "Suhu (°C)",
                     data: suhuData,
                     borderColor: 'rgba(75, 192, 192, 1)',
                     borderWidth: 2,
                     fill: false
                 },
                 {
                     label: "kadar_asap (ppm)",
                     data: kadar_asapData,
                     borderColor: 'rgba(255, 99, 132, 1)',
                     borderWidth: 2,
                     fill: false
                 }
             ]
         },
         options: {
             responsive: true,
             scales: {
                 x: {
                     type: 'time',
                     time: {
                         unit: 'minute',
                         displayFormats: {
                             minute: 'HH:mm'
                         }
                     }
                 }
             }
         }
     });

    // Update table
    var dataTableBody = document.getElementById("dataTableBody");
    if (dataTableBody) {
        dataTableBody.innerHTML = "";
        
        for (var i = 0; i < labels.length; i++) {
            var row = document.createElement("tr");

            var waktuCell = document.createElement("td");
            waktuCell.textContent = labels[i];

            var suhuCell = document.createElement("td");
            suhuCell.textContent = suhuData[i] + "°C";

            var asapCell = document.createElement("td");
            asapCell.textContent = kadar_asapData[i] + " ppm";
            
            var statusCell = document.createElement("td");
            
            // Determine status based on temperature and smoke level
            var status = "Aman";
            if (suhuData[i] > 32 || kadar_asapData[i] > 300) {
                status = "Bahaya";
                row.style.backgroundColor = "red";
                row.style.color = "white";
            }
            
            statusCell.textContent = status;

            row.appendChild(waktuCell);
            row.appendChild(suhuCell);
            row.appendChild(asapCell);
            row.appendChild(statusCell);

            dataTableBody.appendChild(row);
        }
    }

    // Send notification if there are temperatures above threshold
    if (suhuData.some(suhu => suhu > 32)) {
        sendTemperatureWarning(suhuData.filter(suhu => suhu > 32));
    }

    // Send notification if there are smoke levels above threshold
    if (kadar_asapData.some(asap => asap > 300)) {
        sendSmokeWarning(kadar_asapData.filter(asap => asap > 300));
    }
}

// Function untuk memperbarui data terbaru
function updateLatestData() {
    $.ajax({
        url: "data.php",
        type: "GET",
        dataType: "json",
        success: function(data) {
            // Update latest temperature
            if (document.getElementById("latestsuhu")) {
                document.getElementById("latestsuhu").textContent = "Suhu: " + data.latestsuhu + "°C";
            }

            // Update latest smoke level
            if (document.getElementById("latestkadar_asap")) {
                document.getElementById("latestkadar_asap").textContent = "Kadar Asap: " + data.latestkadar_asap + " ppm";
            }
            
            // Update gauges if they exist
            if (window.suhuMeter && data.latestsuhu) {
                window.suhuMeter.refresh(data.latestsuhu);
            }
            
            if (window.asapMeter && data.latestkadar_asap) {
                window.asapMeter.refresh(data.latestkadar_asap);
            }
        },
        error: function(xhr, status, error) {
            console.error("Error updating latest data:", error);
        }
    });
}

// Mengambil dan memperbarui data awalnya
 // Fungsi untuk mengambil data dari server dan memperbarui grafik dan tabel
 function fetchDataAndUpdate() {
    $.ajax({
        url: "data.php", 
        type: "GET",
        dataType: "json",
        success: function (data) {
            updateChartAndTable(data);
        }
    });
}
fetchDataAndUpdate();

setInterval(fetchDataAndUpdate, 1500);

setInterval(updateLatestData, 1500);
