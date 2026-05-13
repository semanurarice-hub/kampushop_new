<?php
include 'includes/auth.php';
include '../config.php';

/* ID kontrol */
$id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$id) {
    die("ID bulunamadı");
}

/* Güncelleme işlemi */
if (isset($_POST['update'])) {

    $update = $conn->prepare("
        UPDATE users 
        SET ad_soyad = ?, eposta = ?, rol = ?
        WHERE id = ?
    ");

    $update->execute([
        $_POST['ad_soyad'],
        $_POST['eposta'],
        $_POST['rol'],
        $id
    ]);

    header("Location: dashboard.php");
    exit();
}

/* Kullanıcı bilgisi çek */
$user = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user->execute([$id]);

$data = $user->fetch(PDO::FETCH_ASSOC);

if (!$data) {
    die("Kullanıcı bulunamadı");
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Kullanıcı Düzenle</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card p-4">

        <h3 class="mb-4">Kullanıcı Düzenle</h3>

        <form method="POST">

            <div class="mb-3">
                <label class="form-label">Ad Soyad</label>

                <input type="text"
                       name="ad_soyad"
                       class="form-control"
                       value="<?= htmlspecialchars($data['ad_soyad']) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">E-posta</label>

                <input type="email"
                       name="eposta"
                       class="form-control"
                       value="<?= htmlspecialchars($data['eposta']) ?>"
                       required>
            </div>

            <div class="mb-3">
                <label class="form-label">Rol</label>

                <select name="rol" class="form-select">

                    <option value="user"
                        <?= $data['rol'] == 'user' ? 'selected' : '' ?>>
                        Kullanıcı
                    </option>

                    <option value="admin"
                        <?= $data['rol'] == 'admin' ? 'selected' : '' ?>>
                        Admin
                    </option>

                </select>
            </div>

            <button type="submit"
                    name="update"
                    class="btn btn-success">
                Güncelle
            </button>

            <a href="dashboard.php"
               class="btn btn-secondary">
                Geri Dön
            </a>

        </form>

    </div>

</div>

</body>
</html>