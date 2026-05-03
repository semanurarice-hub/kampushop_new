<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];

$ad_soyad = $_POST['ad_soyad'];
$eposta = $_POST['eposta'];

$stmt = $conn->prepare("
    UPDATE kullanicilar 
    SET ad_soyad = ?, eposta = ? 
    WHERE id = ?
");

$stmt->execute([$ad_soyad, $eposta, $user_id]);

header("Location: profile.php");
?>
