<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tarombo - Smart Family</title>
    <link rel="stylesheet" href="../../assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/style.css">
</head>
<body>
    <!-- Container Utama -->
    <div class="container mt-5 content">
        <!-- Judul -->
        <h2 class="text-center mb-4">Tarombo Smart Family</h2>

        <!-- Penjelasan -->
        <p class="text-center mb-4">
            Pilih sundut (generasi) untuk melihat anggota keluarga pada generasi tersebut.
        </p>

        <!-- Grid Tombol Sundut -->
        <div class="sundut-grid">
            <?php
            // Anda bisa mengubah jumlah generasi sesuai kebutuhan
            $jumlah_generasi = 10;
            
            for ($i = 1; $i <= $jumlah_generasi; $i++) {
                echo "<a href='detail_sundut.php?generasi=$i' class='btn btn-primary sundut-button'>Sundut $i</a>";
            }
            ?>
        </div>

        <!-- Link Kembali ke Halaman Utama -->
        <div class="text-center mt-4">
            <a href="../../index.html" class="btn btn-secondary">Kembali ke Beranda</a>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p class="mb-0">&copy; 2024 Smart Family. All rights reserved.</p>
    </footer>

    <script src="/assets/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>