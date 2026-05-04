<?php
session_start();
include 'config.php'; // Veritabanı bağlantısı

$error = "";

if(isset($_POST['login'])){
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $q = $conn->prepare("SELECT * FROM users WHERE eposta = ?");
    $q->execute([$email]);
    $user = $q->fetch(PDO::FETCH_ASSOC);

    if($user && password_verify($pass, $user['sifre'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['ad_soyad'];
        $_SESSION['rol'] = $user['rol'];

        if($user['rol'] == "admin"){
            header("Location: admin/dashboard.php");
            exit();
        } else {
            header("Location: index.php");
            exit();
        }
    } else {
        $error = "E-posta veya şifre hatalı!";
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
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
            background: #0d6efd; /* Kart artık canlı mavi */
            padding: 40px; 
            border-radius: 25px; 
            box-shadow: 0 15px 40px rgba(13, 110, 253, 0.2); 
            width: 100%; 
            max-width: 400px;
            color: white; /* Kart içindeki tüm yazılar varsayılan olarak beyaz */
        }
        .form-control { 
            border-radius: 12px; 
            padding: 12px; 
            border: none;
            background-color: rgba(255, 255, 255, 0.9); /* Inputlar hafif şeffaf beyaz */
        }
        .form-control:focus {
            background-color: #ffffff;
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.2);
        }
        /* Beyaz Buton Tasarımı */
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
            color: white;
        }
        label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <h2 class="logo-text">KAMPU<span style="color:#2ecc71">$</span>HOP</h2>
            <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem;">Üniversitenin Pazaryeri</p>
        </div>

        <?php if($error) echo "<div class='alert alert-light py-2 small text-center text-danger fw-bold' style='border-radius:10px;'>$error</div>"; ?>

        <form method="POST">
            <div class="mb-3 text-start">
                <label class="fw-bold mb-1">E-POSTA ADRESİ</label>
                <input type="email" name="email" class="form-control" placeholder="ad.soyad@ogr.atauni.edu.tr" required>
            </div>
            <div class="mb-4 text-start">
                <label class="fw-bold mb-1">ŞİFRE</label>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            
            <button type="submit" name="login" class="btn btn-white-outline w-100 mb-3">GİRİŞ YAP</button>
            
            <div class="text-center mt-3">
                <p class="small mb-2" style="color: rgba(255, 255, 255, 0.7);">Henüz üye değil misin?</p>
                <a href="auth-register.php" class="text-white fw-bold text-decoration-none border-bottom border-2">
                    Hemen Kayıt Ol
                </a>
            </div>
        </form>
    </div>
</body>
</html>