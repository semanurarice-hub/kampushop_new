<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// checkout.php'den gelen gizli dizi verisini veya session verisini yakalıyoruz
$cart = isset($_POST['cart_items']) ? $_POST['cart_items'] : ($_SESSION['cart'] ?? []);
$total_amount = isset($_POST['total_amount']) ? $_POST['total_amount'] : '0.00';

if (empty($cart)) {
    header("Location: checkout.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_payment'])) {
    $card_name = isset($_POST['card_name']) ? trim($_POST['card_name']) : 'Gizli Kullanıcı';

    try {
        $conn->beginTransaction();

        // Sepetteki her bir ürün için orders tablosuna ilan_id'yi de ekleyerek satır açıyoruz
        foreach ($cart as $ilan_id) {
            
            // Ürünün kendi fiyatını çekelim
            $stmt_fiyat = $conn->prepare("SELECT fiyat FROM ilanlar WHERE id = ?");
            $stmt_fiyat->execute([$ilan_id]);
            $ilan_fiyat = $stmt_fiyat->fetchColumn() ?: 0;

            // KESİN ÇÖZÜM: Artık ilan_id sütununa aldığın ürünün ID'si tam yazılıyor!
            $stmt_order = $conn->prepare("
                INSERT INTO orders (user_id, ilan_id, total_amount, card_name) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt_order->execute([$user_id, $ilan_id, $ilan_fiyat, $card_name]);

            // Ürünü satıldı yapıyoruz ki vitrinden ve favorilerden otomatik düşsün
            $stmt_ilan = $conn->prepare("UPDATE ilanlar SET durum = 'satildi' WHERE id = ?");
            $stmt_ilan->execute([$ilan_id]);
        }

        $conn->commit();
        $_SESSION['cart'] = []; // sepeti sıfırla

        echo "<script>
                alert('Ödemeniz başarıyla alındı! Ürününüz Siparişlerim sayfasına eklendi.');
                window.location.href = 'orders.php';
              </script>";
        exit();

    } catch (Exception $e) {
        $conn->rollBack();
        echo "Sipariş kaydedilirken bir hata oluştu: " . $e->getMessage();
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Güvenli Ödeme | KAMPU$HOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Plus Jakarta Sans', sans-serif; }
        .navbar { background: linear-gradient(135deg, #1e2530 0%, #283344 100%) !important; height: 70px; }
        .payment-card { max-width: 480px; margin: 50px auto; background: white; padding: 35px; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); }
        .form-label { font-weight: 700; font-size: 0.8rem; color: #636e72; text-transform: uppercase; margin-bottom: 6px; }
        .form-control { border-radius: 12px; padding: 12px 16px; border: 2px solid #edf2f7; background: #f8fafc; font-weight: 600; }
        .btn-pay { background: #2ecc71; color: white; font-weight: 700; border-radius: 12px; padding: 14px; width: 100%; border: none; font-size: 1.05rem; }
        .btn-pay:hover { background: #27ae60; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark sticky-top">
    <div class="container"><a href="index.php" class="text-white text-decoration-none fw-bold fs-4">KAMPU<span class="text-success">$</span>HOP</a></div>
</nav>
<div class="container">
    <div class="card payment-card">
        <div class="text-center mb-4">
            <div class="text-primary mb-2"><i class="fa-solid fa-shield-halved fa-3x" style="color: #4361ee;"></i></div>
            <h4 class="fw-bold text-dark mb-1">Güvenli Kart Ödemesi</h4>
        </div>
        <div class="d-flex justify-content-between align-items-center p-3 mb-4 rounded-3" style="background: #f0fdf4; border: 1px solid #bbf7d0;">
            <span class="text-success fw-600 small">Ödenecek Toplam Tutar:</span>
            <span class="fw-bold text-success fs-4"><?= htmlspecialchars($total_amount); ?> TL</span>
        </div>
        <form action="" method="POST">
            <input type="hidden" name="total_amount" value="<?= htmlspecialchars($total_amount); ?>">
            
            <?php foreach ($cart as $id): ?>
                <input type="hidden" name="cart_items[]" value="<?= $id ?>">
            <?php endforeach; ?>

            <div class="mb-3">
                <label class="form-label">Kart Üzerindeki İsim</label>
                <input type="text" name="card_name" class="form-control" placeholder="Ad Soyad" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Kart Numarası</label>
                <input type="text" name="card_number" class="form-control" maxlength="16" placeholder="0000 0000 0000 0000" required>
            </div>
            <div class="row g-3 mb-4">
                <div class="col-6"><label class="form-label">Son Kullanma (AA/YY)</label><input type="text" name="expiry" class="form-control" placeholder="AA/YY" required></div>
                <div class="col-6"><label class="form-label">CVV</label><input type="text" name="cvv" class="form-control" maxlength="3" placeholder="000" required></div>
            </div>
            <button type="submit" name="complete_payment" class="btn-pay"><i class="fa-solid fa-circle-check me-2"></i>Siparişi Tamamla</button>
        </form>
    </div>
</div>
</body>
</html>