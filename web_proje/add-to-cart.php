<?php
session_start();

$id = $_GET['id'] ?? null;
$redirect = $_GET['redirect'] ?? "cart.php";

if (!$id) {
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (!in_array($id, $_SESSION['cart'])) {
    $_SESSION['cart'][] = $id;
}

header("Location: " . $redirect);
exit();
?>