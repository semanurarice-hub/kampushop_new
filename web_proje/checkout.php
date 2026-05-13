<?php
session_start();
include 'config.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    echo "Sepet boş";
    exit();
}

// ürünleri çek
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
    <title>Ödeme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="container mt-4">

<h2>🛒 Sepetim</h2>

<a href="index.php" class="btn btn-primary mb-3">← Alışverişe Devam Et</a>

<!-- SEPET ÜRÜNLERİ -->
<?php foreach ($items as $item): 
    $total += $item['fiyat'];
?>
    <div class="border p-2 mb-2 d-flex justify-content-between">
        <div>
            <b><?= htmlspecialchars($item['baslik']) ?></b>
            <br>
            <?= $item['fiyat'] ?> TL
        </div>

        <a href="remove-from-cart.php?id=<?= $item['id'] ?>" class="btn btn-danger btn-sm">
            Sil
        </a>
    </div>
<?php endforeach; ?>

<h4>Toplam: <?= $total ?> TL</h4>

<hr>

<!-- ÖDEME -->
<h3>Ödeme Yap</h3>

<form id="payment-form">

    <button type="submit" id="purchase-btn" class="btn btn-success">
        Ödemeyi Tamamla
    </button>

</form>

<div id="success-message" style="display:none; color:green; font-weight:bold;">
    Ödeme Başarılı!
</div>

<script>
document.getElementById('payment-form').addEventListener('submit', function(e) {

    e.preventDefault();

    const btn = document.getElementById('purchase-btn');

    btn.disabled = true;
    btn.innerText = "İşleniyor...";

    fetch('purchase.php', {
        method: 'POST'
    })
    .then(res => res.text())
    .then(data => {

        if (data.trim() === "basarili") {

            document.getElementById('payment-form').style.display = "none";
            document.getElementById('success-message').style.display = "block";

            setTimeout(() => {
                window.location.href = "index.php";
            }, 2000);

        } else {
            alert("Hata: " + data);

            btn.disabled = false;
            btn.innerText = "Ödemeyi Tamamla";
        }
    });

});
</script>

</body>
</html>