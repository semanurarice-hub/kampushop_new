<?php
session_start();
include 'config.php';

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    die("bos");
}

foreach ($cart as $id) {
    $stmt = $conn->prepare("UPDATE ilanlar SET durum='satildi' WHERE id=?");
    $stmt->execute([$id]);
}

// sepeti temizle
$_SESSION['cart'] = [];

echo "basarili";
?>