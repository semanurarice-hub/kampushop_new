<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

if (isset($_POST['submit'])) {

    $baslik = $_POST['baslik'];
    $aciklama = $_POST['aciklama'];
    $fiyat = $_POST['fiyat'];

    // FOTOĞRAF YÜKLEME
    $resimAdi = "";

    if (isset($_FILES['resim']) && $_FILES['resim']['error'] == 0) {

        $tmpName = $_FILES['resim']['tmp_name'];
        $originalName = $_FILES['resim']['name'];

        // Uzantı al
        $uzanti = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        // Yeni benzersiz isim
        $yeniAd = time() . "_" . rand(1000,9999) . "." . $uzanti;

        // uploads klasörüne taşı
        move_uploaded_file($tmpName, "uploads/" . $yeniAd);

        $resimAdi = $yeniAd;
    }

    // VERİTABANINA EKLE
    $sql = "INSERT INTO ilanlar (baslik, aciklama, fiyat, resim, ilan_tipi)
            VALUES (?, ?, ?, ?, 0)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        $baslik,
        $aciklama,
        $fiyat,
        $resimAdi
    ]);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İlan Ver</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body{
            background:#f5f5f5;
        }

        .box{
            max-width:600px;
            margin:auto;
            margin-top:50px;
            background:white;
            padding:30px;
            border-radius:15px;
            box-shadow:0 5px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container">

    <div class="box">

        <h2 class="mb-4">🛍️ İlan Ver</h2>

        <form method="POST" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label>Ürün Fotoğrafı</label>
                <input type="file" name="resim" class="form-control" accept="image/*">
            </div>

            <div class="mb-3">
                <label>İlan Başlığı</label>
                <input type="text" name="baslik" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Açıklama</label>
                <textarea name="aciklama" class="form-control" rows="4" required></textarea>
            </div>

            <div class="mb-3">
                <label>Fiyat</label>
                <input type="number" name="fiyat" class="form-control" required>
            </div>

            

            <button type="submit" name="submit" class="btn btn-primary w-100">
                İlanı Yayınla
            </button>

        </form>

    </div>

</div>

</body>
</html>