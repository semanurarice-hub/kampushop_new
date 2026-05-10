<?php
session_start();
include 'config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header("Location: index.php");
    exit();
}

/* sepet yoksa oluştur */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* zaten varsa ekleme */
if (!in_array($id, $_SESSION['cart'])) {
    $_SESSION['cart'][] = $id;
}

header("Location: cart.php");
exit();
?>