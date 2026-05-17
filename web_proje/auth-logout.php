<?php
session_start();

// Tüm session değişkenlerini temizle
$_SESSION = array();

// Eğer oturum çerezini (cookie) temizlemek istersen:
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Oturumu tamamen sonlandır
session_destroy();

// Kullanıcıyı giriş sayfasına veya ana sayfaya yönlendir
header("Location: auth-login.php");
exit();
?>