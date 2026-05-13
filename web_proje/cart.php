<?php
session_start();
include 'config.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    echo "<h3>🛒 Sepet boş</h3>";
    exit();
}

$placeholders = implode(',', array_fill(0, count($cart), '?'));

$query = $conn->prepare("SELECT * FROM ilanlar WHERE id IN ($placeholders)");
$query->execute($cart);

$items = $query->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sepetim</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<h2>🛒 Sepetim</h2>

<?php foreach ($items as $item): 
    $total += $item['fiyat'];
?>
    <div class="border p-2 mb-2">
        <a href="remove-from-cart.php?id=<?= $item['id'] ?>" 
   class="btn btn-danger btn-sm float-end">
    Kaldır
</a>
        <b><?= htmlspecialchars($item['baslik']) ?></b> - 
        <?= $item['fiyat'] ?> TL
    </div>
<?php endforeach; ?>

<h4 class="mt-3">Toplam: <?= $total ?> TL</h4>

<a href="checkout.php" class="btn btn-success mt-3">
    Satın Al
</a>

</body>
</html>