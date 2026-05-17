<?php
session_start();
include 'config.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// GÜNCELLEME: Sadece durumu 'aktif' olan (yani satılmamış) ilanları favorilerde listeler
$query = $conn->prepare("
    SELECT ilanlar.* FROM ilanlar 
    INNER JOIN favoriler ON ilanlar.id = favoriler.ilan_id 
    WHERE favoriler.user_id = ? AND ilanlar.durum = 'aktif'
    ORDER BY favoriler.id DESC
");

$query->execute([$user_id]);

$favori_ilanlar = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorilerim | KAMPU$HOP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            background-color: #f4f7f6;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Tüm Panel ile Uyumlu Gece Mavisi Geçişli Navbar */
        .navbar {
            background: linear-gradient(135deg, #1e2530 0%, #283344 100%);
            padding: 16px 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .navbar-brand {
            font-weight: 800;
            color: white !important;
            font-size: 1.4rem;
            letter-spacing: -0.5px;
        }

        /* Gelişmiş Modern İlan Kartları */
        .card-ilan {
            transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 20px !important;
            border: none !important;
            position: relative;
            overflow: hidden;
            background: white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
        }

        .card-ilan:hover {
            transform: translateY(-6px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08) !important;
        }

        .card-img-wrapper {
            position: relative;
            height: 180px;
            overflow: hidden;
            background-color: #f8fafc;
        }

        .card-ilan-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .card-ilan:hover .card-ilan-img {
            transform: scale(1.06);
        }

        /* Kalp Butonu Efekti */
        .fav-remove-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
            background: white;
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #ff4757;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        .fav-remove-btn:hover {
            transform: scale(1.1);
            background: #ff4757;
            color: white;
        }

        /* Detay Butonu Stili */
        .btn-detay {
            background-color: #4361ee;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-detay:hover {
            background-color: #354fd1;
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.2);
        }

        /* Boş Durum Tasarımı */
        .empty-state {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
            max-width: 500px;
            margin: 60px auto;
        }

        .empty-state i {
            font-size: 64px;
            color: #e2e8f0;
            background: #f8fafc;
            padding: 25px;
            border-radius: 50%;
            margin-bottom: 24px;
        }
    </style>
</head>

<body>

<nav class="navbar sticky-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            KAMPU<span style="color: #2ecc71;">$</span>HOP
        </a>

        <div class="ms-auto d-flex align-items-center">
            <a href="profil.php" class="text-white text-decoration-none fw-bold me-4 d-flex align-items-center gap-2">
                <i class="fa-solid fa-user-circle fa-lg opacity-75"></i>
                Profilim
            </a>
            <a href="index.php" class="btn btn-outline-light btn-sm px-4 rounded-pill fw-bold">
                Vitrini Gez
            </a>
        </div>
    </div>
</nav>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-heart text-danger me-2"></i>
            Favori İlanlarım
        </h4>
        <span class="badge bg-primary rounded-pill px-3 py-2 fw-bold" style="font-size: 0.85rem;">
            <?= count($favori_ilanlar); ?> Aktif İlan
        </span>
    </div>

    <div class="row">
        <?php if(count($favori_ilanlar) > 0): ?>
            <?php foreach($favori_ilanlar as $row): 
                $resim_adi = !empty($row['resim']) ? $row['resim'] : 'default.jpg';
            ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card h-100 card-ilan">
                        
                        <button class="fav-remove-btn remove-favorite" data-id="<?= $row['id']; ?>" type="button">
                            <i class="fa-solid fa-heart"></i>
                        </button>

                        <div class="card-img-wrapper">
                            <img src="uploads/<?= htmlspecialchars($resim_adi); ?>" class="card-ilan-img" alt="Ürün Resmi" onerror="this.src='uploads/default.jpg'">
                        </div>

                        <div class="card-body d-flex flex-column p-3">
                            <h6 class="fw-bold text-dark text-truncate mb-2">
                                <?= htmlspecialchars($row['baslik']); ?>
                            </h6>

                            <p class="text-muted small mb-3 flex-grow-1">
                                <?= mb_strimwidth(htmlspecialchars($row['aciklama'] ?? ''), 0, 70, "..."); ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                                <span class="fw-bold text-dark fs-5">
                                    <?= number_format($row['fiyat'] ?? 0, 0, ',', '.'); ?> <span style="font-size: 0.9rem; font-weight: 700; color: #64748b;">TL</span>
                                </span>

                                <a href="listing-details.php?id=<?= $row['id']; ?>" class="btn-detay text-decoration-none">
                                    Detay <i class="fa-solid fa-arrow-right ms-1" style="font-size: 0.75rem;"></i>
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="fa-solid fa-heart-crack"></i>
                <h5 class="fw-bold text-dark">Henüz favori ilanınız yok</h5>
                <p class="text-muted small px-3">
                    Kampüste ilgini çeken ilanları kalbe basarak buraya ekleyebilir, fiyat değişimlerini kolayca takip edebilirsin.
                </p>
                <a href="index.php" class="btn btn-primary px-4 rounded-pill mt-2 fw-bold" style="background-color: var(--accent-blue); border:none;">
                    İlanları Keşfet
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.querySelectorAll('.remove-favorite').forEach(button => {
    button.addEventListener('click', function () {
        const btn = this;
        const ilanId = btn.dataset.id;

        fetch('toggle-favorite.php?id=' + ilanId)
        .then(response => response.text())
        .then(data => {
            if (data.trim() === "removed") {
                // Kartı sayfadan akıcı bir animasyonla kaldırır
                const cardColumn = btn.closest('.col-lg-3, .col-md-4, .col-sm-6');
                cardColumn.style.opacity = '0';
                cardColumn.style.transform = 'scale(0.9)';
                cardColumn.style.transition = 'all 0.3s ease';
                
                setTimeout(() => {
                    cardColumn.remove();
                    // Eğer hiç ilan kalmadıysa sayfayı yenileyerek boş durum ekranını getirir
                    if(document.querySelectorAll('.card-ilan').length === 0){
                        location.reload();
                    }
                }, 300);
            }
        })
        .catch(error => {
            console.log(error);
            alert("Bir hata oluştu.");
        });
    });
});
</script>

</body>
</html>