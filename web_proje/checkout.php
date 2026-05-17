<?php
session_start();
include 'config.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <title>Sepetim | KAMPU$HOP</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
        <style>
            body { background-color: #f4f7f6; font-family: 'Plus Jakarta Sans', sans-serif; }
            .navbar { background: linear-gradient(135deg, #1e2530 0%, #283344 100%) !important; height: 70px; }
            .empty-cart { text-align: center; padding: 60px 20px; background: white; border-radius: 24px; max-width: 500px; margin: 80px auto; box-shadow: 0 4px 20px rgba(0,0,0,0.02); }
            .empty-cart i { font-size: 64px; color: #cbd5e1; background: #f8fafc; padding: 25px; border-radius: 50%; margin-bottom: 24px; }
        </style>
    </head>
    <body>
        <nav class="navbar navbar-dark sticky-top"><div class="container"><a href="index.php" class="navbar-brand fw-bold">KAMPU<span class="text-success">$</span>HOP</a></div></nav>
        <div class="container"><div class="empty-cart"><i class="fa-solid fa-basket-shopping"></i><h5 class="fw-bold text-dark">Sepetiniz şu an boş</h5><p class="text-muted small">Kampüsteki fırsatları kaçırmamak için hemen vitrini gezmeye başla!</p><a href="index.php" class="btn btn-primary rounded-pill px-4 fw-bold mt-2" style="background-color:#4361ee; border:none;">Alışverişe Başla</a></div></div>
    </body>
    </html>
    <?php
    exit();
}

// Ürünleri çek
$placeholders = implode(',', array_fill(0, count($cart), '?'));
$stmt = $conn->prepare("SELECT * FROM ilanlar WHERE id IN ($placeholders)");
$stmt->execute($cart);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim ve Ödeme | KAMPU$HOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Plus Jakarta Sans', sans-serif; }
        .navbar { background: linear-gradient(135deg, #1e2530 0%, #283344 100%) !important; height: 70px; box-shadow: 0 4px 25px rgba(0,0,0,0.1); }
        .navbar-brand { font-weight: 800; font-size: 1.6rem; color: white !important; }
        .cart-card { border: none; border-radius: 16px; background: white; box-shadow: 0 4px 15px rgba(0,0,0,0.02); }
        .product-img { width: 70px; height: 70px; object-fit: contain; background: #f8fafc; border-radius: 12px; border: 1px solid #edf2f7; padding: 4px; }
        .summary-card { border: none; border-radius: 20px; background: white; box-shadow: 0 4px 20px rgba(0,0,0,0.03); }
        .btn-checkout { background: #2ecc71; color: white; font-weight: 700; border-radius: 12px; padding: 14px; width: 100%; border: none; transition: 0.2s; display: block; text-align: center; text-decoration: none; }
        .btn-checkout:hover { background: #27ae60; box-shadow: 0 4px 15px rgba(46,204,113,0.25); color: white; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="navbar-brand">KAMPU<span class="text-success">$</span>HOP</a>
        <a href="index.php" class="btn btn-outline-light btn-sm px-3 fw-bold" style="border-radius: 9px;">
            <i class="fa-solid fa-arrow-left me-1"></i> Alışverişe Devam Et
        </a>
    </div>
</nav>

<div class="container mt-5">
    <div class="row g-4">
        
        <div class="col-lg-8">
            <h4 class="fw-bold text-dark mb-4"><i class="fa-solid fa-basket-shopping text-primary me-2"></i>Alışveriş Sepetim</h4>
            
            <?php foreach ($items as $item): 
                $total += $item['fiyat'];
                $img = !empty($item['resim']) ? $item['resim'] : 'default.jpg';
            ?>
                <div class="card cart-card p-3 mb-3">
                    <div class="row align-items-center g-3">
                        <div class="col-auto">
                            <img src="uploads/<?= $img ?>" class="product-img" onerror="this.src='uploads/default.jpg'">
                        </div>
                        <div class="col">
                            <h6 class="fw-bold text-dark m-0 mb-1"><?= htmlspecialchars($item['baslik']) ?></h6>
                            <span class="text-muted small">Kampüs İçi Teslimat</span>
                        </div>
                        <div class="col-auto text-end">
                            <b class="text-dark d-block mb-2 fs-5"><?= number_format($item['fiyat'], 0, ',', '.') ?> TL</b>
                            <a href="remove-from-cart.php?id=<?= $item['id'] ?>" class="text-danger small fw-bold text-decoration-none"><i class="fa-solid fa-trash-can me-1"></i>Kaldır</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="col-lg-4">
            <h4 class="fw-bold text-dark mb-4"><i class="fa-solid fa-receipt text-primary me-2"></i>Sipariş Özeti</h4>
            <div class="card summary-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted">Ara Toplam</span>
                    <span class="text-dark fw-bold"><?= number_format($total, 0, ',', '.') ?> TL</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="text-muted">Güvenli Teslimat</span>
                    <span class="text-success fw-bold">Ücretsiz</span>
                </div>
                <hr class="opacity-25 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-dark fs-5">Genel Toplam</span>
                    <span class="fw-bold text-primary fs-4"><?= number_format($total, 0, ',', '.') ?> TL</span>
                </div>

                <form action="purchase.php" method="POST">
                    <input type="hidden" name="total_amount" value="<?= $total ?>"> 
                    
                    <?php foreach ($cart as $id): ?>
                        <input type="hidden" name="cart_items[]" value="<?= $id ?>">
                    <?php endforeach; ?>

                    <button type="submit" class="btn-checkout">
                        <i class="fa-solid fa-credit-card me-2"></i>Ödeme Adımına Geç
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>