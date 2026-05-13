<?php
session_start();
include 'config.php';

$user_id = $_SESSION['user_id'];

$baslik = $_POST['baslik'];
$aciklama = $_POST['aciklama'];
$oncelik = $_POST['oncelik'];

$stmt = $conn->prepare("
    INSERT INTO talepler (user_id, baslik, aciklama, oncelik)
    VALUES (?, ?, ?, ?)
");

$stmt->execute([$user_id, $baslik, $aciklama, $oncelik]);

header("Location: profil.php?status=ok");
exit();