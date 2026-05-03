<?php
include 'includes/auth.php';
session_start();
include '../config.php';

// sadece admin erişsin
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin'){
    header("Location: ../index.php");
    exit();
}

// id kontrolü
$id = isset($_GET['id']) ? $_GET['id'] : null;

if(!$id){
    header("Location: dashboard.php");
    exit();
}

// kullanıcı sil
$query = $db->prepare("DELETE FROM kullanicilar WHERE id = ?");
$query->execute([$id]);

header("Location: dashboard.php");
exit();
?>