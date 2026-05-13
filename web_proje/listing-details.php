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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($ilan['baslik']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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

        <img src="uploads/<?php echo $resim; ?>" class="product-image mb-4">

        <h2>
            <?php echo htmlspecialchars($ilan['baslik']); ?>
        </h2>

        <h4 class="text-success mb-3">
            <?php echo $ilan['fiyat']; ?> TL
        </h4>

        <p>
            <?php echo nl2br(htmlspecialchars($ilan['aciklama'])); ?>
        </p>
        <a href="add-to-cart.php?id=<?php echo $ilan['id']; ?>&redirect=checkout.php" 
         class="btn btn-success">
        🛒 Satın Al
        </a>
        <div class="mt-4 d-flex gap-2">

            <a href="contact-seller.php?id=<?php echo $ilan['id']; ?>" 
               class="btn btn-primary">
                📩 Satıcıyla İletişime Geç
            </a>

            <a href="index.php" class="btn btn-secondary">
                Geri Dön
            </a>

        </div>

    </div>

</div>

</body>
</html>