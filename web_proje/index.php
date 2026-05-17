<?php
session_start();
include 'config.php';

// Filtreleme Değişkenleri
$ara = isset($_GET['ara']) ? trim($_GET['ara']) : '';
$kat = isset($_GET['kategori']) ? $_GET['kategori'] : '';


// --- İLANLARI GETİR ---
$sql = "SELECT * FROM ilanlar WHERE ilan_tipi = 0 AND durum = 'aktif'";
$params = [];

if(!empty($ara)) { 
    $sql .= " AND (baslik LIKE :ara OR aciklama LIKE :ara)"; 
    $params[':ara'] = "%$ara%";
}
 
if(!empty($kat)) { 
    $sql .= " AND kategori = :kat"; 
    $params[':kat'] = $kat;
}

$sql .= " ORDER BY id DESC";
$query = $conn->prepare($sql);
$query->execute($params);

// --- ACİL İHTİYAÇLARI GETİR ---
$sql_urgent = "SELECT * FROM ilanlar WHERE ilan_tipi = 1 ORDER BY id DESC";
$urgent_query = $conn->query($sql_urgent);

// --- EV ARKADAŞI İLANLARINI GETİR (YENİ EKLENDİ) ---
$sql_roommate = "SELECT * FROM roommate_listings ORDER BY id DESC";
$roommate_query = $conn->query($sql_roommate);

// Favori kontrolü
$user_favs = [];
if(isset($_SESSION['user_id'])) {
    $fav_q = $conn->prepare("SELECT ilan_id FROM favoriler WHERE user_id = ?");
    $fav_q->execute([$_SESSION['user_id']]);
    $user_favs = $fav_q->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KAMPU$HOP | Kampüs Vitrini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        body { background-color: #f4f7f6; font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Yenilenen Kusursuz Üst Bar Tasarımı */
        .navbar { 
            background: linear-gradient(135deg, #1e2530 0%, #283344 100%) !important; 
            padding: 0 !important;
            height: 70px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        
        /* Logo ve Başlık Hizalama Alanı */
        .brand-container {
            display: flex;
            align-items: center;
            height: 70px;
            text-decoration: none;
        }

        .brand-logo-img {
            height: 85px;
            margin-right: -35px;
            z-index: 5;
            object-fit: contain;
        }

        .brand-text-h2 {
            margin: 0;
            font-weight: 800;
            font-size: 1.7rem;
            color: #ffffff;
            z-index: 4;
        }

        .brand-text-h2 span { color: #2ecc71; }
        
        /* Modern Buton Tasarımları */
        .nav-btn-custom {
            font-weight: 600;
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 10px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }
        .btn-basket-custom { background-color: #2ecc71; color: white !important; }
        .btn-basket-custom:hover { background-color: #27ae60; box-shadow: 0 4px 12px rgba(46,204,113,0.2); }
        
        .btn-add-custom { background-color: rgba(255,255,255,0.1); color: white !important; border: 1px solid rgba(255,255,255,0.2); }
        .btn-add-custom:hover { background-color: rgba(255,255,255,0.2); }
        
        .btn-urgent-custom { background-color: #ff4757; color: white !important; }
        .btn-urgent-custom:hover { background-color: #ee3848; box-shadow: 0 4px 12px rgba(255,71,87,0.2); }
        
        /* Yeni Eklenen Ev Arkadaşı Buton Stili */
        .btn-roommate-custom { background-color: #f39c12; color: white !important; }
        .btn-roommate-custom:hover { background-color: #e67e22; box-shadow: 0 4px 12px rgba(243,156,18,0.2); }

        .btn-profile-custom { background-color: #4361ee; color: white !important; }
        .btn-profile-custom:hover { background-color: #354fd1; box-shadow: 0 4px 12px rgba(67,97,238,0.2); }
        
        .btn-logout-custom { background-color: transparent; color: #ff7675 !important; border: 1px solid rgba(255,118,117,0.3); }
        .btn-logout-custom:hover { background-color: rgba(255,118,117,0.1); }
        
        .nav-fav-icon { color: rgba(255,255,255,0.7); font-size: 1.4rem; transition: 0.2s; text-decoration: none; display: inline-flex; align-items: center; }
        .nav-fav-icon:hover { color: #ff4757; transform: scale(1.1); }

        /* Vitrin Kart Tasarımları */
        .card { border: none; border-radius: 20px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.03); transition: 0.3s; background: white; }
        .card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }
        .card-img-top { height: 200px; object-fit: contain; background-color: #f8f9fa; padding: 15px; }
        
        .edit-badge { position: absolute; top: 12px; right: 12px; z-index: 10; background: #ffc107; color: #000; padding: 5px 12px; border-radius: 20px; text-decoration: none; font-size: 0.75rem; font-weight: bold; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .edit-badge:hover { background: #e5ac00; color: #000; }

        .btn-fav { color: #ff4757; border: 1px solid #edf2f7; background: #f8fafc; border-radius: 10px; }
        .btn-fav.active { background: #ff4757; color: white; border-color: #ff4757; }
        
        .urgent-card { cursor: pointer; transition: 0.2s; border-left: 5px solid #ff4757 !important; border-radius: 14px; }
        .urgent-card:hover { background-color: #fff5f5; transform: translateX(4px); }

        /* Yeni Eklenen Ev Arkadaşı Kart Stili */
        .roommate-card { border-left: 5px solid #f39c12 !important; border-radius: 14px; }
        .roommate-badge { background-color: #fdf2e2; color: #e67e22; font-weight: 600; font-size: 0.8rem; padding: 4px 10px; border-radius: 6px; }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
   <div class="container d-flex justify-content-between align-items-center">
        
        <a href="index.php" class="brand-container">
            <img src="logo.png" alt="Logo" class="brand-logo-img" onerror="this.style.display='none'">
            <h2 class="brand-text-h2">
                KAMPU<span>$</span>HOP
            </h2>
        </a>
        
        <div class="d-flex align-items-center gap-2">
            <a href="cart.php" class="nav-btn-custom btn-basket-custom">🛒 Sepetim</a>
            <a href="create-listing.php" class="nav-btn-custom btn-add-custom">🛍️ İlan Ver</a>
            <a href="create-urgent.php" class="nav-btn-custom btn-urgent-custom">🚨 Acil İhtiyaç</a>
            
            <a href="create-roommate.php" class="nav-btn-custom btn-roommate-custom">🏠 Ev Arkadaşı</a>
            
            <a href="favorilerim.php" class="nav-fav-icon mx-2"><i class="fa-solid fa-heart"></i></a>

            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profil.php" class="nav-btn-custom btn-profile-custom">👤 Profilim</a>
                <a href="auth-logout.php" class="nav-btn-custom btn-logout-custom">Çıkış</a>
            <?php else: ?>
                <a href="auth-login.php" class="nav-btn-custom btn-profile-custom px-4">Giriş</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if(isset($_GET['durum'])): ?>
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius: 12px;">
            <?php 
                if($_GET['durum'] == 'ok') echo "✅ İlan başarıyla güncellendi!";
                if($_GET['durum'] == 'silindi') echo "🗑️ İlan başarıyla kaldırıldı!";
                if($_GET['durum'] == 'ev_ok') echo "🏠 Ev arkadaşı ilanınız başarıyla yayınlandı!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-4 mb-4 border-0 shadow-sm" style="border-radius: 20px;">
        <form method="GET" action="index.php" class="row g-2">
            <div class="col-md-6">
                <input type="text" name="ara" class="form-control" placeholder="Ürün ara..." value="<?php echo htmlspecialchars($ara); ?>" style="border-radius: 10px; padding: 10px 15px;">
            </div>
            <div class="col-md-4">
                <select name="kategori" class="form-select" style="border-radius: 10px; padding: 10px 15px;">
                    <option value="">Tüm Kategoriler</option>
                    <option value="kitap" <?php if($kat=='kitap') echo 'selected'; ?>>Kitap</option>
                    <option value="elektronik" <?php if($kat=='elektronik') echo 'selected'; ?>>Elektronik</option>
                    <option value="esya" <?php if($kat=='esya') echo 'selected'; ?>>Eşya</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold" style="border-radius: 10px; padding: 10px;">Filtrele</button>
            </div>
        </form>
    </div>

    <div class="row">
        <?php while($row = $query->fetch(PDO::FETCH_ASSOC)): 
            $img = !empty($row['resim']) ? $row['resim'] : 'default.jpg';
            $is_fav = in_array($row['id'], $user_favs);
        ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card h-100 position-relative">
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                        <a href="edit-listing.php?id=<?php echo $row['id']; ?>" class="edit-badge" title="Düzenle">
                            <i class="fa-solid fa-pen"></i> Düzenle
                        </a>
                    <?php endif; ?>

                    <img src="uploads/<?php echo $img; ?>" class="card-img-top">
                    <div class="card-body d-flex flex-column p-3">
                        <h6 class="fw-bold text-dark text-truncate mb-2"><?php echo htmlspecialchars($row['baslik']); ?></h6>
                        <p class="text-muted small flex-grow-1 mb-3"><?php echo mb_strimwidth(htmlspecialchars($row['aciklama']), 0, 60, "..."); ?></p>
                        
                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top">
                            <b class="text-success fs-5"><?php echo number_format($row['fiyat'], 2, ',', '.'); ?> TL</b>
                        </div>
                        
                        <div class="d-flex gap-2 mt-2">
                            <a href="add-to-cart.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning fw-bold flex-grow-1 py-2" style="border-radius: 8px;">
                                Sepete Ekle
                            </a>
                            <button class="btn btn-sm btn-fav favorite-btn <?php echo $is_fav ? 'active' : ''; ?>" data-id="<?php echo $row['id']; ?>" style="width: 40px;">
                                <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                            </button>
                            <a href="listing-details.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary py-2 px-3" style="border-radius: 8px;">İncele</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <h4 class="mt-4 mb-3 fw-bold text-dark"><i class="fa-solid fa-triangle-exclamation text-danger me-1"></i> Acil İhtiyaçlar</h4>
    <div class="row mb-4">
        <?php while($row = $urgent_query->fetch(PDO::FETCH_ASSOC)): 
             $is_fav = in_array($row['id'], $user_favs);
        ?>
            <div class="col-12 mb-2">
                <div class="card p-3 urgent-card shadow-sm" onclick="location.href='listing-details.php?id=<?php echo $row['id']; ?>'">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div style="width:45px;height:45px;background:#fff5f5;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-right:15px;border: 1px solid rgba(255,71,87,0.1);">
                                <i class="fa-solid fa-bullhorn text-danger"></i>
                            </div>
                            <div>
                                <b class="d-block text-dark"><?php echo htmlspecialchars($row['baslik']); ?></b>
                                <span class="small text-muted"><?php echo htmlspecialchars($row['aciklama']); ?></span>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center gap-2">
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                                <a href="edit-listing.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" onclick="event.stopPropagation();" style="border-radius: 8px;">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-fav btn-sm favorite-btn <?php echo $is_fav ? 'active' : ''; ?>" data-id="<?php echo $row['id']; ?>" onclick="event.stopPropagation();" style="height: 31px; width: 35px; display:flex; align-items:center; justify-content:center;">
                                <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <h4 class="mt-4 mb-3 fw-bold text-dark"><i class="fa-solid factor fa-house-user text-warning me-1"></i> 🏠 Ev Arkadaşı Arayanlar</h4>
    <div class="row mb-5">
        <?php if($roommate_query->rowCount() == 0): ?>
            <div class="col-12">
                <div class="alert alert-light text-muted border shadow-sm p-4 text-center" style="border-radius: 14px;">
                    Henüz ev arkadaşı ilanı verilmemiş. İlk ilanı sen ver!
                </div>
            </div>
        <?php else: ?>
            <?php while($room = $roommate_query->fetch(PDO::FETCH_ASSOC)): ?>
                <div class="col-12 mb-2">
                    <div class="card p-3 roommate-card shadow-sm">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center">
                                <div style="width:45px;height:45px;background:#fff9f0;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-right:15px;border: 1px solid rgba(243,156,18,0.1);">
                                    <i class="fa-solid fa-home text-warning"></i>
                                </div>
                                <div>
                                    <b class="d-block text-dark"><?php echo htmlspecialchars($room['title']); ?></b>
                                    <span class="small text-muted d-block mb-1"><?php echo htmlspecialchars($room['description']); ?></span>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="roommate-badge">📍 <?php echo htmlspecialchars($room['location']); ?></span>
                                        <span class="roommate-badge">💰 Bütçe: <?php echo number_format($room['budget'], 0, ',', '.'); ?> TL</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <a href="mailto:?subject=Ev Arkadaşlığı Hakkında" class="btn btn-sm btn-outline-warning fw-bold px-3" style="border-radius: 8px;">
                                    📨 İletişime Geç
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Favori sistemi
document.querySelectorAll('.favorite-btn').forEach(button => {
    button.addEventListener('click', function (e) {
        e.preventDefault();
        const btn = this;
        const icon = btn.querySelector('i');
        
        <?php if(!isset($_SESSION['user_id'])): ?>
            alert('Favorilere eklemek için önce giriş yapmalısınız!');
            return;
        <?php endif; ?>

        fetch('toggle-favorite.php?id=' + btn.dataset.id)
        .then(res => res.text())
        .then(data => {
            if (data.trim() === "added") {
                btn.classList.add('active');
                icon.className = 'fa-solid fa-heart';
            } else {
                btn.classList.remove('active');
                icon.className = 'fa-regular fa-heart';
            }
        });
    });
});
</script>
</body>
</html>