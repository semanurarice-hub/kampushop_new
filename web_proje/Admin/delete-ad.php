<?php
include 'includes/auth.php';
include '../config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID yok");
}

$query = $conn->prepare("DELETE FROM ilanlar WHERE id = ?");
$query->execute([$id]);

header("Location: dashboard.php?type=ads");
exit();
?>