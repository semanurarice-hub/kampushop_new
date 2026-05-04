<?php
include "includes/auth.php";
include "includes/header.php";
include "config.php";

if (isset($_POST['submit'])) {

    $title = $_POST['title'];
    $description = $_POST['description'];
    $price = $_POST['price'];

    // FOTO YÜKLEME
    $imageName = $_FILES['image']['name'];
    $tmpName = $_FILES['image']['tmp_name'];

    $newImageName = "img_" . uniqid() . "." . pathinfo($imageName, PATHINFO_EXTENSION);
    $uploadPath = "uploads/" . $newImageName;

    if (move_uploaded_file($tmpName, $uploadPath)) {

        $stmt = $conn->prepare("INSERT INTO listings (title, description, price, image) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $title, $description, $price, $newImageName);
        $stmt->execute();

        echo "<p style='color:green;'>İlan başarıyla eklendi!</p>";

    } else {
        echo "<p style='color:red;'>Fotoğraf yüklenemedi!</p>";
    }
}
?>

<h2>İlan Ekle</h2>

<form method="POST" enctype="multipart/form-data">

    <input type="text" name="title" placeholder="İlan Başlığı" required><br><br>

    <textarea name="description" placeholder="Açıklama" required></textarea><br><br>

    <input type="number" name="price" placeholder="Fiyat" required><br><br>

    <input type="file" name="image" accept="image/*" required><br><br>

    <button type="submit" name="submit">İlanı Yayınla</button>

</form>

<?php include "includes/footer.php"; ?>