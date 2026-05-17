<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

if (isset($_POST['save_listing'])) {

    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $category = $_POST['category'];

    // FOTOĞRAF YÜKLEME
    $imageName = $_FILES['image']['name'];
    $tmpName = $_FILES['image']['tmp_name'];

    $newImageName = time() . "_" . $imageName;

    move_uploaded_file($tmpName, "uploads/" . $newImageName);

    // VERİTABANINA EKLE
    $stmt = $conn->prepare("
    INSERT INTO ilanlar
    (baslik, aciklama, fiyat, kategori, resim, user_id)
    VALUES (?, ?, ?, ?, ?, ?)
");

    // PDO execute kullanımı
    $stmt->execute([
        $title,
        $description,
        $price,
        $category,
        $newImageName,
        $_SESSION['user_id']
    ]);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İlan Ver</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">

    <h2 class="mb-4">İlan Ver</h2>

    <form method="POST" enctype="multipart/form-data">

        <div class="mb-3">
            <label class="form-label">Ürün Fotoğrafı</label>
            <input type="file" name="image" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Kategori</label>

            <select name="category" class="form-control" required>

                <option value="">Kategori Seç</option>

                <option value="Elektronik">Elektronik</option>

                <option value="Kitap">Kitap</option>

                <option value="Giyim">Giyim</option>

                <option value="Ev Eşyası">Ev Eşyası</option>

                <option value="Diğer">Diğer</option>

            </select>

        </div>

        <div class="mb-3">
            <label class="form-label">Başlık</label>

            <input type="text"
                   name="title"
                   class="form-control"
                   required>
        </div>

        <div class="mb-3">
            <label class="form-label">Açıklama</label>

            <textarea name="description"
                      class="form-control"
                      required></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Fiyat</label>

            <input type="number"
                   name="price"
                   class="form-control"
                   required>
        </div>

        <button type="submit"
                name="save_listing"
                class="btn btn-primary">

            İlan Ver

        </button>

    </form>

</div>

</body>
</html>