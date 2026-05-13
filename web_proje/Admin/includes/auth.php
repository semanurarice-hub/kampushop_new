<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Eğer giriş yapılmamışsa VEYA rolü admin değilse erişimi engelle
if(!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin'){
    header("Location: ../auth-login.php?hata=yetkisiz_erisim");
    exit();
}
?>