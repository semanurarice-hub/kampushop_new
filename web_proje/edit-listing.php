<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) { header("Location: auth-login.php"); exit; }
if (!isset($_GET['id'])) { header("Location: index.php"); exit; }

$id = $_GET['id'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM ilanlar WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$ilan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ilan) { echo "Yetkisiz erişim!"; exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $baslik = $_POST['baslik'];
    $aciklama = $_POST['aciklama'];
    $fiyat = isset($_POST['fiyat']) ? $_POST['fiyat'] : 0;
    $kategori = $_POST['kategori'];
    
    $resim_ad = $ilan['resim']; // Varsayılan olarak eski resim kalsın

    // YENİ RESİM YÜKLENİYOR MU?
    if (!empty($_FILES['resim']['name'])) {
        $hedef_klasor = "uploads/";
        $dosya_uzantisi = pathinfo($_FILES['resim']['name'], PATHINFO_EXTENSION);
        $yeni_ad = time() . '_' . uniqid() . '.' . $dosya_uzantisi;

        if (move_uploaded_file($_FILES['resim']['tmp_name'], $hedef_klasor . $yeni_ad)) {
            // Eski resmi klasörden sil (default değilse)
            if ($ilan['resim'] != 'default.jpg' && file_exists($hedef_klasor . $ilan['resim'])) {
                unlink($hedef_klasor . $ilan['resim']);
            }
            $resim_ad = $yeni_ad;
        }
    }

    $update = $conn->prepare("UPDATE ilanlar SET baslik=?, aciklama=?, fiyat=?, kategori=?, resim=? WHERE id=? AND user_id=?");
    if ($update->execute([$baslik, $aciklama, $fiyat, $kategori, $resim_ad, $id, $user_id])) {
        header("Location: index.php?durum=ok");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İlanı Düzenle</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .container { max-width: 700px; margin-top: 50px; }
        .card { border-radius: 15px; border: none; shadow: 0 4px 12px rgba(0,0,0,0.1); }
        #imagePreview { max-width: 200px; display: block; margin-top: 10px; border-radius: 10px; border: 2px solid #ddd; }
    </style>
</head>
<body>

<div class="container">
    <div class="card p-4">
        <h3 class="mb-4">🖼️ İlanı ve Resmi Güncelle</h3>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-bold">İlan Başlığı</label>
                <input type="text" name="baslik" class="form-control" value="<?php echo htmlspecialchars($ilan['baslik']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Açıklama</label>
                <textarea name="aciklama" class="form-control" rows="3"><?php echo htmlspecialchars($ilan['aciklama']); ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Fiyat (TL)</label>
                    <input type="number" name="fiyat" class="form-control" value="<?php echo $ilan['fiyat']; ?>">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold">Kategori</label>
                    <select name="kategori" class="form-select">
                        <option value="kitap" <?php echo ($ilan['kategori'] == 'kitap' ? 'selected' : ''); ?>>Kitap</option>
                        <option value="elektronik" <?php echo ($ilan['kategori'] == 'elektronik' ? 'selected' : ''); ?>>Elektronik</option>
                        <option value="esya" <?php echo ($ilan['kategori'] == 'esya' ? 'selected' : ''); ?>>Eşya</option>
                    </select>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold text-danger">Resmi Değiştir</label>
                <input type="file" name="resim" id="resimInput" class="form-control" accept="image/*">
                <div class="mt-3">
                    <small class="text-muted d-block mb-2">Şu Anki / Yeni Resim Önizleme:</small>
                    <img id="imagePreview" src="uploads/<?php echo $ilan['resim']; ?>" alt="Önizleme">
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg shadow">💾 Değişiklikleri Kaydet</button>
                <a href="index.php" class="btn btn-outline-secondary">İptal Et</a>
            </div>
        </form>
    </div>
</div>

<script>
    // JS ile anlık resim önizleme
    document.getElementById('resimInput').onchange = function (evt) {
        const [file] = this.files;
        if (file) {
            document.getElementById('imagePreview').src = URL.createObjectURL(file);
        }
    };
</script>

</body>
</html>