<?php
session_start();
include 'config.php';

$error = "";

if (isset($_POST['register'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    // 🔥 DÜZELTİLDİ: eposta kullanılıyor
    $check = $conn->prepare("SELECT id FROM users WHERE eposta = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {

        $error = "Bu e-posta zaten kayıtlı!";

    } else {

        // 🔥 DÜZELTİLDİ: email değil eposta
        $insert = $conn->prepare("
            INSERT INTO users (ad_soyad, eposta, sifre)
            VALUES (?, ?, ?)
        ");

        $insert->execute([$name, $email, $hash]);

        header("Location: auth-login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kayıt Ol | KAMPUSHOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body { 
            background-color: #eef2f3; 
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
        }

        .btn-register { 
            border-radius: 12px; 
            padding: 12px; 
            font-weight: 600; 
        }
    </style>
</head>

<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">

            <div class="card register-card p-4 p-md-5">

                <div class="text-center mb-4">
                    <div style="text-align: center; width: 100%; margin-bottom: 15px;">
    <a href="index.php" style="text-decoration: none; display: inline-block;">
        <img src="logo.png" alt="Jet Kampüs" style="height: 86px; width: auto; display: block; margin: 0 auto -30px auto;">
       
    </a>
</div>
                    <h2 class="fw-bold text-primary">KAMPUSHOP</h2>
                    <p class="text-muted">Hemen katıl</p>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <input type="text" name="name" class="form-control mb-3" placeholder="Ad Soyad" required>

                    <input type="email" name="email" class="form-control mb-3" placeholder="E-posta" required>

                    <input type="password" name="password" class="form-control mb-3" placeholder="Şifre" required>

                    <button type="submit" name="register" class="btn btn-primary w-100">
                        Kayıt Ol
                    </button>

                </form>

            </div>

        </div>
    </div>
</div>

</body>
</html>