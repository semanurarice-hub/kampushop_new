<?php
session_start();
include 'config.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

if (isset($_POST['send'])) {

    // ---------------- RESİM UPLOAD ----------------
    $resim_adi = "default.jpg";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {

        $dosya_adi = $_FILES['image']['name'];
        $gecici_yol = $_FILES['image']['tmp_name'];
        $uzanti = strtolower(pathinfo($dosya_adi, PATHINFO_EXTENSION));

        $izin = ['jpg','jpeg','png','webp'];

        if (in_array($uzanti, $izin)) {
            $yeni_ad = uniqid('img_', true) . "." . $uzanti;
            move_uploaded_file($gecici_yol, "uploads/" . $yeni_ad);
            $resim_adi = $yeni_ad;
        }
    }

    // ---------------- DB KAYIT ----------------
    $stmt = $conn->prepare("
        INSERT INTO ilanlar 
        (user_id, baslik, aciklama, kategori, resim, fiyat, ilan_tipi)
        VALUES (?, ?, ?, ?, ?, ?, 1)
    ");

    $stmt->execute([
        $_SESSION['user_id'],
        $_POST['baslik'],
        $_POST['aciklama'],
        $_POST['kategori'],
        $resim_adi,
        0
    ]);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Acil İhtiyaç</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card p-4">

        <h3>🚨 Acil İhtiyaç Oluştur</h3>

        <form method="POST" enctype="multipart/form-data" class="mt-3">

            <!-- FOTO -->
            <div class="mb-3">
                <label>Fotoğraf (isteğe bağlı)</label>
                <input type="file" name="image" class="form-control" accept="image/*">
            </div>

            <!-- KATEGORİ -->
            <div class="mb-3">
                <label>Kategori</label>
                <select name="kategori" class="form-control" required>
                    <option value="Kitap">Kitap</option>
                    <option value="Elektronik">Elektronik</option>
                    <option value="Eşya">Eşya</option>
                    <option value="Diğer">Diğer</option>
                </select>
            </div>

            <!-- BAŞLIK -->
            <div class="mb-3">
                <label>Başlık</label>
                <input type="text" name="baslik" class="form-control" required>
            </div>

            <!-- AÇIKLAMA -->
            <div class="mb-3">
                <label>Açıklama</label>
                <textarea name="aciklama" class="form-control" rows="4" required></textarea>
            </div>

            <button name="send" class="btn btn-danger w-100">
                Gönder
            </button>

        </form>

    </div>

</div>

</body>
</html>