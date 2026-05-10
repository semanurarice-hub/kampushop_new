<?php
session_start();
include '../config.php';

// admin kontrol
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../index.php");
    exit();
}

// id kontrol
if(!isset($_GET['id'])){
    die("ID gelmedi!");
}

$id = (int) $_GET['id'];

try {

    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

} catch (PDOException $e) {
    die("Silme hatası: " . $e->getMessage());
}

// geri dön
header("Location: dashboard.php?deleted=1");
exit();
?>