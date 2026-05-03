<?php
session_start();
include 'config.php'; // Veritabanı bağlantısı

$error = "";

if (isset($_POST['register'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    // Şifreyi güvenli bir şekilde hashliyoruz
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    try {
        // 1. E-posta zaten var mı kontrolü
        // Dashboard ile uyumlu olması için tabloyu 'users', sütunu 'eposta' yaptık.
        $check = $conn->prepare("SELECT id FROM users WHERE eposta = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $error = "Bu e-posta zaten kayıtlı!";
        } else {
            // 2. Yeni kullanıcı kaydı
            $insert = $conn->prepare("
                INSERT INTO users (ad_soyad, eposta, sifre, rol)
                VALUES (?, ?, ?, 'user')
            ");

            if ($insert->execute([$name, $email, $hash])) {
                // Kayıt başarılıysa login sayfasına başarı mesajıyla gönder
                header("Location: auth-login.php?status=success");
                exit();
            } else {
                $error = "Kayıt sırasında teknik bir hata oluştu.";
            }
        }
    } catch (PDOException $e) {
        $error = "Veritabanı Hatası: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol | KAMPU$HOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background: #f8f9fa; 
            height: 100vh; 
            display: flex; 
            align-items: center; 
            font-family: 'Segoe UI', sans-serif;
        }
        .register-card { 
            border: none; 
            border-radius: 25px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.1); 
            background: white;
            padding: 40px;
        }
        .btn-register { 
            border-radius: 12px; 
            padding: 12px; 
            font-weight: 600; 
            background: #0d6efd; 
            border: none; 
            transition: 0.3s;
        }
        .btn-register:hover {
            background: #0056b3;
            transform: translateY(-2px);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card register-card">
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary">KAMPU<span style="color:#2ecc71">$</span>HOP</h2>
                    <p class="text-muted">Hemen aramıza katıl ve alışverişe başla!</p>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger py-2 small">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">AD SOYAD</label>
                        <input type="text" name="name" class="form-control" placeholder="Adınız ve Soyadınız" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">E-POSTA</label>
                        <input type="email" name="email" class="form-control" placeholder="ad.soyad@ogr.atauni.edu.tr" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ŞİFRE</label>
                        <input type="password" name="password" class="form-control" placeholder="Güçlü bir şifre belirle" required>
                    </div>
                    <button type="submit" name="register" class="btn btn-primary w-100 btn-register mt-2 text-white">Hesap Oluştur</button>
                </form>

                <div class="text-center mt-4">
                    <small class="text-muted">Zaten hesabın var mı? <a href="auth-login.php" class="text-decoration-none fw-bold text-primary">Giriş Yap</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>