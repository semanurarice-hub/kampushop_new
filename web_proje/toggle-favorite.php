<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    exit;
}

$user_id = $_SESSION['user_id'];
$ilan_id = $_GET['id'];

$check = $conn->prepare("SELECT id FROM favoriler WHERE user_id = ? AND ilan_id = ?");
$check->execute([$user_id, $ilan_id]);

if ($check->rowCount() > 0) {

    $delete = $conn->prepare("DELETE FROM favoriler WHERE user_id = ? AND ilan_id = ?");
    $delete->execute([$user_id, $ilan_id]);

    echo "removed";

} else {

    $insert = $conn->prepare("INSERT INTO favoriler (user_id, ilan_id) VALUES (?, ?)");
    $insert->execute([$user_id, $ilan_id]);

    echo "added";
}
?>