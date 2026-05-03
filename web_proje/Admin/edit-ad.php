<?php
include 'includes/auth.php';
include '../config.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID bulunamadı");
}

/* güncelle */
if (isset($_POST['update'])) {

    $update = $conn->prepare("
        UPDATE ilanlar 
        SET baslik = ?, fiyat = ?
        WHERE id = ?
    ");

    $update->execute([
        $_POST['baslik'],
        $_POST['fiyat'],
        $id
    ]);

    header("Location: dashboard.php?type=ads");
    exit();
}

/* veri çek */
$query = $conn->prepare("SELECT * FROM ilanlar WHERE id = ?");
$query->execute([$id]);

$data = $query->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("İlan bulunamadı");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İlan Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card p-4">

        <h3>İlan Düzenle</h3>

        <form method="POST">

            <div class="mb-3">
                <label>Başlık</label>
                <input type="text" name="baslik" class="form-control"
                       value="<?= htmlspecialchars($data['baslik']) ?>" required>
            </div>

            <div class="mb-3">
                <label>Fiyat</label>
                <input type="number" name="fiyat" class="form-control"
                       value="<?= $data['fiyat'] ?>" required>
            </div>

            <button type="submit" name="update" class="btn btn-success">
                Güncelle
            </button>

            <a href="dashboard.php?type=ads" class="btn btn-secondary">
                Geri
            </a>

        </form>

    </div>

</div>

</body>
</html>