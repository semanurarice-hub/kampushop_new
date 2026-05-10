<?php
session_start();
include 'config.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $user_id = $_SESSION['user_id'];

    $ad_soyad = trim($_POST['ad_soyad']);
    $eposta   = trim($_POST['eposta']);

    try {
        $sql = "UPDATE users SET ad_soyad = ?, eposta = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$ad_soyad, $eposta, $user_id]);

        header("Location: profil.php?status=ok");
        exit();

    } catch (PDOException $e) {

        header("Location: profil.php?status=db_error");
        exit();
    }

} else {
    header("Location: index.php");
    exit();
}
?>