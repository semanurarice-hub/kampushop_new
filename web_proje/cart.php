<?php
session_start();
include 'config.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    echo "<h3>Sepet boş</h3>";
    exit();
}

$ids = implode(',', $cart);

$query = $conn->query("SELECT * FROM ilanlar WHERE id IN ($ids)");
?>

<h2>Sepetim</h2>

<table border="1" cellpadding="10">

<tr>
    <th>Başlık</th>
    <th>Fiyat</th>
</tr>

<?php while($row = $query->fetch(PDO::FETCH_ASSOC)): ?>

<tr>
    <td><?= htmlspecialchars($row['baslik']) ?></td>
    <td><?= $row['fiyat'] ?> ₺</td>
</tr>

<?php endwhile; ?>

</table>