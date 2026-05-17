<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $sql_orders = "SELECT o.*, i.baslik, i.resim FROM orders o 
                   LEFT JOIN ilanlar i ON o.ilan_id = i.id 
                   WHERE o.user_id = ? 
                   ORDER BY o.id DESC";
    $query_orders = $conn->prepare($sql_orders);
    $query_orders->execute([$user_id]);
    $tum_siparisler = $query_orders->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tum_siparisler = [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişlerim | KAMPU$HOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #f8fafc; 
            font-family: 'Plus Jakarta Sans', sans-serif; 
            color: #334155;
        }
        
        /* Modern Üst Bar */
        .navbar { 
            background: linear-gradient(135deg, #1e2530 0%, #283344 100%) !important; 
            height: 70px; 
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        .navbar-brand { font-weight: 800; font-size: 1.5rem; letter-spacing: -0.5px; }
        
        /* Sayfa Başlığı Konteyneri */
        .page-header {
            background: white;
            padding: 24px 0;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 40px;
        }

        /* Yeni Nesil Sipariş Kartları */
        .order-card { 
            border: 1px solid #e2e8f0; 
            border-radius: 16px; 
            background: white; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.01); 
            transition: all 0.25s ease;
            overflow: hidden;
        }
        .order-card:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 12px 24px rgba(0,0,0,0.04); 
            border-color: #cbd5e1;
        }
        
        /* Gelişmiş Ürün Resim Kutusu */
        .img-container {
            width: 90px;
            height: 90px;
            background: #f1f5f9;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .product-img { 
            width: 100%; 
            height: 100%; 
            object-fit: cover; 
        }
        .no-img-icon {
            font-size: 1.8rem;
            color: #94a3b8;
        }

        /* Bölüm Başlıkları */
        .section-title { 
            font-size: 1rem; 
            font-weight: 700; 
            color: #475569; 
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Modern Yumuşak Badgeler */
        .badge-custom {
            padding: 6px 12px;
            font-weight: 600;
            font-size: 0.75rem;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .badge-active { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .badge-success-custom { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }

        .price-text {
            font-weight: 800;
            color: #0f172a;
            font-size: 1.3rem;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-dark sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a href="index.php" class="navbar-brand text-white text-decoration-none">KAMPU<span class="text-success">$</span>HOP</a>
        <a href="profil.php" class="btn btn-outline-light btn-sm px-4 fw-bold" style="border-radius: 10px;">
            <i class="fa-solid fa-arrow-left me-1"></i> Profile Dön
        </a>
    </div>
</nav>

<div class="page-header">
    <div class="container d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center">
            <div class="bg-primary text-white rounded-3 p-2 me-3 d-flex align-items-center justify-content-center" style="width:48px; height:48px; background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%) !important;">
                <i class="fa-solid fa-box-open fa-lg"></i>
            </div>
            <div>
                <h4 class="fw-bold text-dark m-0">Sipariş Takip Paneli</h4>
                <p class="text-muted small m-0 mt-1">Satın aldığınız ürünlerin güncel durumunu buradan inceleyebilirsiniz.</p>
            </div>
        </div>
        <span class="badge bg-slate text-secondary border px-3 py-2 fw-bold" style="background:#f1f5f9; border-radius:10px;">
            Toplam: <?= count($tum_siparisler); ?> Sipariş
        </span>
    </div>
</div>

<div class="container" style="max-width: 1000px;">
    
    <div class="mb-5">
        <div class="section-title mb-3"><i class="fa-solid fa-circle-dot text-warning me-2"></i>Güncel / Anlık Siparişlerim</div>
        
        <?php if(empty($tum_siparisler)): ?>
            <div class="p-5 text-center text-muted bg-white rounded-4 border small shadow-sm">
                <i class="fa-solid fa-calendar-xmark fa-2x mb-3 d-block text-slate-300"></i>
                Şu anda aktif veya işleme alınmış bir siparişiniz bulunmuyor.
            </div>
        <?php else: 
            $en_yeni = $tum_siparisler[0]; 
            $img = !empty($en_yeni['resim']) ? 'uploads/' . $en_yeni['resim'] : '';
        ?>
            <div class="card order-card p-4">
                <div class="row align-items-center g-4">
                    <div class="col-auto">
                        <div class="img-container">
                            <?php if(!empty($en_yeni['resim'])): ?>
                                <img src="<?= $img ?>" class="product-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                <i class="fa-solid fa-box no-img-icon" style="display:none;"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-box no-img-icon"></i>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted fw-600" style="font-size: 0.8rem;">Sipariş Kodu: <strong class="text-dark">#KMP-<?= $en_yeni['id']; ?></strong></span>
                            <span class="text-slate-300">•</span>
                            <span class="text-muted small">Alıcı: <?= htmlspecialchars($en_yeni['card_name']); ?></span>
                        </div>
                        <h5 class="fw-bold text-slate-800 m-0 mt-2"><?= htmlspecialchars($en_yeni['baslik'] ?? 'Kampüs İçi Ürün Alımı'); ?></h5>
                        <div class="mt-3">
                            <span class="badge-custom badge-active">
                                <i class="fa-solid fa-spinner fa-spin"></i> Hazırlanıyor / Teslimat Aşamasında
                            </span>
                        </div>
                    </div>
                    <div class="col-md-auto text-md-end border-start-md ps-md-4">
                        <span class="text-muted small d-block mb-1">Ödenen Tutar</span>
                        <div class="price-text text-success"><?= number_format($en_yeni['total_amount'], 2, ',', '.'); ?> TL</div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="mb-5">
        <div class="section-title mb-3"><i class="fa-solid fa-history text-secondary me-2"></i>Önceden Verdiğim Siparişler</div>
        
        <?php if(count($tum_siparisler) <= 1): ?>
            <div class="p-5 text-center text-muted bg-white rounded-4 border small shadow-sm">
                Geçmiş dönemlere ait tamamlanmış bir sipariş kaydınız bulunmuyor.
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php for($i = 1; $i < count($tum_siparisler); $i++): 
                    $gecmis = $tum_siparisler[$i];
                    $g_img = !empty($gecmis['resim']) ? 'uploads/' . $gecmis['resim'] : '';
                ?>
                    <div class="card order-card p-4">
                        <div class="row align-items-center g-4">
                            <div class="col-auto">
                                <div class="img-container">
                                    <?php if(!empty($gecmis['resim'])): ?>
                                        <img src="<?= $g_img ?>" class="product-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                        <i class="fa-solid fa-box no-img-icon" style="display:none;"></i>
                                    <?php else: ?>
                                        <i class="fa-solid fa-box no-img-icon"></i>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col">
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="text-muted fw-600" style="font-size: 0.8rem;">Sipariş Kodu: <strong class="text-dark">#KMP-<?= $gecmis['id']; ?></strong></span>
                                    <span class="text-slate-300">•</span>
                                    <span class="text-muted small">Ödeme Yapan: <?= htmlspecialchars($gecmis['card_name']); ?></span>
                                </div>
                                <h5 class="fw-bold text-slate-800 m-0 mt-2"><?= htmlspecialchars($gecmis['baslik'] ?? 'Geçmiş Kampüs Alışverişi'); ?></h5>
                                <div class="mt-3">
                                    <span class="badge-custom badge-success-custom">
                                        <i class="fa-solid fa-circle-check"></i> Tamamlandı / Teslim Edildi
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-auto text-md-end">
                                <span class="text-muted small d-block mb-1">İşlem Tutarı</span>
                                <div class="price-text text-secondary" style="font-size: 1.15rem;"><?= number_format($gecmis['total_amount'], 2, ',', '.'); ?> TL</div>
                            </div>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>