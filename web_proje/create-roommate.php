<?php
session_start();
include 'config.php';

// Giriş kontrolü - Giriş yapmamış kullanıcı ilan veremesin
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $housing_status = $_POST['housing_status'];
    $location = trim($_POST['location']);
    $budget = $_POST['budget'];
    $smoking_allowed = isset($_POST['smoking_allowed']) ? 1 : 0;
    $pet_allowed = isset($_POST['pet_allowed']) ? 1 : 0;
    $user_id = $_SESSION['user_id'];

    if (empty($title) || empty($description) || empty($location) || empty($budget)) {
        $error = "Lütfen gerekli tüm alanları doldurun!";
    } else {
        try {
            $sql = "INSERT INTO roommate_listings (user_id, title, description, housing_status, location, budget, smoking_allowed, pet_allowed) 
                    VALUES (:user_id, :title, :description, :housing_status, :location, :budget, :smoking_allowed, :pet_allowed)";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                ':user_id' => $user_id,
                ':title' => $title,
                ':description' => $description,
                ':housing_status' => $housing_status,
                ':location' => $location,
                ':budget' => $budget,
                ':smoking_allowed' => $smoking_allowed,
                ':pet_allowed' => $pet_allowed
            ]);

            // Başarılıysa index.php'ye yönlendir ve mesaj uçur
            header("Location: index.php?durum=ev_ok");
            exit;

        } catch (PDOException $e) {
            $error = "Veritabanı hatası: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ev Arkadaşı İlanı Ver | KAMPU$HOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; font-family: 'Plus Jakarta Sans', sans-serif; }
        .form-card { border: none; border-radius: 20px; box-shadow: 0 4px 25px rgba(0,0,0,0.05); background: white; }
        .btn-submit { background-color: #f39c12; color: white; border: none; font-weight: 600; border-radius: 10px; padding: 12px; transition: 0.2s; }
        .btn-submit:hover { background-color: #e67e22; }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark">🏠 Ev Arkadaşı İlanı Oluştur</h2>
                <a href="index.php" class="btn btn-outline-secondary btn-sm" style="border-radius: 10px;">Vazgeç</a>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger" style="border-radius: 12px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card form-card p-4">
                <form action="create-roommate.php" method="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">İlan Başlığı</label>
                        <input type="text" name="title" class="form-control" placeholder="Örn: Evime 2. arkadaşı arıyorum (Masraflar ortak)" style="border-radius: 10px;">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Ev Durumu</label>
                            <select name="housing_status" class="form-select" style="border-radius: 10px;">
                                <option value="Has House">Evim Var, Arkadaş Arıyorum</option>
                                <option value="Looking For House">Ev Arıyorum, Birinin Yanına Çıkabilirim</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Aylık Tahmini Bütçe / Kira Payı (TL)</label>
                            <input type="number" name="budget" class="form-control" placeholder="Örn: 4000" style="border-radius: 10px;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Konum / Mahalle Bilgisi</label>
                        <input type="text" name="location" class="form-control" placeholder="Örn: Üniversite Kampüsü Karşısı, Bahçelievler Mh." style="border-radius: 10px;">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Detaylı Açıklama ve Kriterleriniz</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Ev düzeni, faturaların paylaşımı ve aradığınız ev arkadaşında olmasını istediğiniz özellikleri yazın..." style="border-radius: 10px;"></textarea>
                    </div>

                    <div class="card p-3 bg-light border-0 mb-4" style="border-radius: 12px;">
                        <h6 class="fw-bold mb-3">Alışkanlıklar & Kriterler</h6>
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="smoking_allowed" id="smoking">
                            <label class="form-check-label" for="smoking">Evde sigara içilmesine izin var mı? / İçiyor musunuz?</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="pet_allowed" id="pet">
                            <label class="form-check-label" for="pet">Evcil hayvan kabul ediliyor mu? / Evcil hayvanınız var mı?</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-submit w-100">İlanı Yayınla</button>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>