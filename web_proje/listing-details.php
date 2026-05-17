<?php
session_start();
include 'config.php';

if (!isset($_GET['id'])) {
    die("İlan bulunamadı");
}

$id = $_GET['id'];

$sql = "SELECT * FROM ilanlar WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$id]);

$ilan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ilan) {
    die("İlan bulunamadı");
}

$resim = !empty($ilan['resim']) ? $ilan['resim'] : 'default.jpg';

// Veritabanındaki satıcı sütun adının ne olduğunu otomatik bulmaya çalışalım
// Eğer ekleyen_id yoksa alternatif olabilecek sütun isimlerini kontrol eder
$satici_id = 0;
if (isset($ilan['ekleyen_id'])) {
    $satici_id = $ilan['ekleyen_id'];
} elseif (isset($ilan['user_id'])) {
    $satici_id = $ilan['user_id'];
} elseif (isset($ilan['kullanici_id'])) {
    $satici_id = $ilan['kullanici_id'];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($ilan['baslik']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body{
            background:#f5f5f5;
        }

        .card-box{
            max-width:800px;
            margin:auto;
            margin-top:40px;
            background:white;
            padding:25px;
            border-radius:15px;
            box-shadow:0 5px 20px rgba(0,0,0,0.1);
        }

        .product-image{
            width:100%;
            height:400px;
            object-fit:cover;
            border-radius:10px;
        }
    </style>
</head>
<body>

<div class="container">

    <div class="card-box">

        <img src="uploads/<?= htmlspecialchars($resim); ?>" class="product-image mb-4">

        <h2>
            <?= htmlspecialchars($ilan['baslik']); ?>
        </h2>

        <h4 class="text-success mb-3">
            <?= number_format($ilan['fiyat'], 2, ',', '.'); ?> TL
        </h4>

        <p>
            <?= nl2br(htmlspecialchars($ilan['aciklama'])); ?>
        </p>
        
        <a href="add-to-cart.php?id=<?= $ilan['id']; ?>&redirect=checkout.php" class="btn btn-success">
            🛒 Satın Al
        </a>
        
        <div class="mt-4 d-flex gap-2">

            <a href="messages.php?ilan_id=<?= $ilan['id']; ?>&alici_id=<?= $satici_id; ?>" class="btn btn-primary">
                <i class="fa-solid fa-envelope me-1"></i> Satıcıyla İletişime Geç
            </a>

            <a href="index.php" class="btn btn-secondary">
                Geri Dön
            </a>

        </div>

    </div>

</div>

</body>
</html>