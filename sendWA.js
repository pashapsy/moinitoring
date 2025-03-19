
        // Fungsi untuk memperbarui grafik dan tabel dengan data baru
        function sendWhatsAppMessage(suhuData) {
            if (suhuData.length === 0) return; // Jika tidak ada suhu di atas 34°C, tidak perlu kirim notifikasi

            var phoneNumber = "628994151709"; // Ganti dengan nomor tujuan
            var message = "⚠️ *PERINGATAN! Suhu Berbahaya!* ⚠️\n\n";

            suhuData.forEach((data, index) => {
                message += `${index + 1}. 🌡 Suhu: ${data.suhu}°C\n🕒 Waktu: ${data.waktu}\n\n`;
            });

            message += "Mohon segera ditindaklanjuti! 🚨";

            var url = `https://wa.me/${phoneNumber}?text=${encodeURIComponent(message)}`;
            window.open(url, "_blank");
        }