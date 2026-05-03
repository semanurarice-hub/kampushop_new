<?php
session_start();
include 'config.php'; // Veritabanı bağlantısı

$error = "";
// Eğer giriş yapmışsa ve tekrar bu sayfaya gelmişse ana sayfaya gönder
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit();
}

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // Veritabanı bağlantı değişkeninin adını config.php'ye göre kontrol et ($conn veya $db)
    // Burada standart olan $conn kullanılmıştır.
    $q = $conn->prepare("SELECT * FROM users WHERE eposta = ?");
    $q->execute([$email]);
    $user = $q->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($pass, $user['sifre'])){
        // Oturum güvenliği için ID yenile
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['ad_soyad'];
        $_SESSION['rol'] = $user['rol'];

        // Rol kontrolü: Admin ise panele, kullanıcı ise ana sayfaya
        if($user['rol'] == "admin"){
            header("Location: admin/dashboard.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "E-posta veya şifre hatalı!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş | KAMPU$HOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f8f9fa; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card { 
            background: #0d6efd; 
            padding: 40px; 
            border-radius: 25px; 
            box-shadow: 0 15px 40px rgba(13, 110, 253, 0.2); 
            width: 100%; 
            max-width: 400px;
            color: white; 
        }
        .form-control { 
            border-radius: 12px; 
            padding: 12px; 
            border: none;
            background-color: rgba(255, 255, 255, 0.9); 
        }
        .btn-white-outline { 
            border-radius: 12px; 
            background-color: white; 
            color: #0d6efd; 
            border: 2px solid white; 
            font-weight: 700;
            padding: 10px;
            transition: all 0.3s ease;
        }
        .btn-white-outline:hover { 
            background-color: transparent; 
            color: white; 
        }
        .logo-text {
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: -1.5px;
        }
    </style>
</head>
<body>
    <div class="login-card text-center">
        <div class="mb-4">
            <h2 class="logo-text">KAMPU<span style="color:#2ecc71">$</span>HOP</h2>
            <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">Üniversitenin Pazaryeri</p>
        </div>

        <?php if($error): ?>
            <div class="alert alert-light py-2 small text-danger fw-bold" style="border-radius:10px;">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3 text-start">
                <label class="small fw-bold mb-1" style="color: rgba(255,255,255,0.8);">E-POSTA ADRESİ</label>
                <input type="email" name="email" class="form-control" placeholder="ad.soyad@ogr.atauni.edu.tr" required>
            </div>
            <div class="mb-4 text-start">
                <label class="small fw-bold mb-1" style="color: rgba(255,255,255,0.8);">ŞİFRE</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" name="login" class="btn btn-white-outline w-100 mb-3">GİRİŞ YAP</button>
            
            <div class="mt-3">
                <p class="small mb-2" style="color: rgba(255, 255, 255, 0.7);">Henüz üye değil misin?</p>
                <a href="auth-register.php" class="text-white fw-bold text-decoration-none border-bottom border-2">
                    Hemen Kayıt Ol
                </a>
            </div>
        </form>
    </div>
</body>
</html>