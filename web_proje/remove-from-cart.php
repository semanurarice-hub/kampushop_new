<?php
session_start();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if ($id > 0) {
    $_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($id) {
        return $item != $id;
    }));
}

header("Location: cart.php");
exit();
?>