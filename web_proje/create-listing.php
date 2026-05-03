<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

if (isset($_POST['save_listing'])) {

    $u_id  = $_SESSION['user_id'];
    $title = htmlspecialchars($_POST['title']);
    $desc  = htmlspecialchars($_POST['description']);
    $price = $_POST['price'];

    // URL'den gelen type
    $type = $_GET['type'] ?? 'normal';
    $ilan_tipi = ($type == 'urgent') ? 1 : 0;

    $stmt = $conn->prepare("
        INSERT INTO ilanlar 
        (user_id, baslik, aciklama, kategori, resim, fiyat, ilan_tipi) 
        VALUES (?, ?, ?, 'Genel', 'default.jpg', ?, ?)
    ");

    if ($stmt->execute([$u_id, $title, $desc, $price, $ilan_tipi])) {
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İlan Ver</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    <a href="index.php" class="btn btn-dark mb-3">⬅ Geri</a>

    <div class="card p-4 shadow">

        <h3 class="text-center mb-3">
            <?= (($_GET['type'] ?? '') == 'urgent') ? '🚨 ACİL İHTİYAÇ' : '🛍️ İLAN VER' ?>
        </h3>

        <form method="POST">

            <div class="mb-3">
                <label>Başlık</label>
                <input type="text" name="title" class="form-control" required>
            </div>

            <div class="mb-3">
                <label>Açıklama</label>
                <textarea name="description" class="form-control" required></textarea>
            </div>

            <div class="mb-3">
                <label>Fiyat</label>
                <input type="number" name="price" class="form-control" required>
            </div>

            <button type="submit" name="save_listing" class="btn btn-primary w-100">
                Yayınla
            </button>

        </form>

    </div>

</div>

</body>
</html>