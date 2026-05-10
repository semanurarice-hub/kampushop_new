<?php
session_start();
include 'config.php';

// Filtreleme Değişkenleri
$ara = isset($_GET['ara']) ? trim($_GET['ara']) : '';
$kat = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// --- İLANLARI GETİR ---
$sql = "SELECT * FROM ilanlar WHERE ilan_tipi = 0";
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
    <title>KAMPU$HOP | Kampüs Vitrini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .navbar { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); padding: 15px 0; }
        .navbar-brand { font-weight: 800; font-size: 1.8rem; color: white !important; }
        .navbar-brand span { color: #2ecc71; }
        
        .card { border: none; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: 0.3s; }
        .card-img-top { height: 200px; object-fit: contain; background-color: #f8f9fa; padding: 10px; }
        
        .edit-badge { position: absolute; top: 10px; right: 10px; z-index: 10; background: #ffc107; color: #000; padding: 5px 10px; border-radius: 20px; text-decoration: none; font-size: 0.75rem; font-weight: bold; }
        .edit-badge:hover { background: #e5ac00; color: #000; }

        .btn-fav { color: #ff4757; border: 1px solid #ff4757; background: transparent; }
        .btn-fav.active { background: #ff4757; color: white; }
        
        .urgent-card { cursor: pointer; transition: 0.2s; border-left: 5px solid #ff4757 !important; }
        .urgent-card:hover { background-color: #fff5f5; }
    </style>
</head>
<body>

<nav class="navbar sticky-top">
    <div class="container d-flex justify-content-between align-items-center">
        <a class="navbar-brand" href="index.php">KAMPU<span>$</span>HOP</a>
        <div class="d-flex align-items-center">
            <a href="create-listing.php" class="btn btn-light text-primary fw-bold me-2">🛍️ İlan Ver</a>
            <a href="create-urgent.php" class="btn btn-danger me-3">🚨 Acil İhtiyaç</a>
            
            <a href="favorilerim.php" class="text-white me-4 fs-4"><i class="fa-solid fa-heart"></i></a>

            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profil.php" class="btn btn-outline-light btn-sm me-3">👤 Profilim</a>
                <a href="auth-logout.php" class="btn btn-danger btn-sm">Çıkış</a>
            <?php else: ?>
                <a href="auth-login.php" class="btn btn-outline-light btn-sm px-4">Giriş</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container mt-3">
    <?php if(isset($_GET['durum'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
                if($_GET['durum'] == 'ok') echo "✅ İlan başarıyla güncellendi!";
                if($_GET['durum'] == 'silindi') echo "🗑️ İlan başarıyla kaldırıldı!";
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card p-3 mb-4 border-0 shadow-sm">
        <form method="GET" action="index.php" class="row g-2">
            <div class="col-md-6"><input type="text" name="ara" class="form-control" placeholder="Ürün ara..." value="<?php echo htmlspecialchars($ara); ?>"></div>
            <div class="col-md-4">
                <select name="kategori" class="form-select">
                    <option value="">Tüm Kategoriler</option>
                    <option value="kitap" <?php if($kat=='kitap') echo 'selected'; ?>>Kitap</option>
                    <option value="elektronik" <?php if($kat=='elektronik') echo 'selected'; ?>>Elektronik</option>
                    <option value="esya" <?php if($kat=='esya') echo 'selected'; ?>>Eşya</option>
                </select>
            </div>
            <div class="col-md-2"><button type="submit" class="btn btn-primary w-100 fw-bold">Filtrele</button></div>
        </form>
    </div>

    <div class="row">
        <?php while($row = $query->fetch(PDO::FETCH_ASSOC)): 
            $img = !empty($row['resim']) ? $row['resim'] : 'default.jpg';
            $is_fav = in_array($row['id'], $user_favs);
        ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100 position-relative">
                    <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                        <a href="edit-listing.php?id=<?php echo $row['id']; ?>" class="edit-badge" title="Düzenle">
                            <i class="fa-solid fa-pen"></i> Düzenle
                        </a>
                    <?php endif; ?>

                    <img src="uploads/<?php echo $img; ?>" class="card-img-top">
                    <div class="card-body d-flex flex-column">
                        <h6 class="fw-bold"><?php echo htmlspecialchars($row['baslik']); ?></h6>
                        <p class="text-muted small flex-grow-1"><?php echo mb_strimwidth(htmlspecialchars($row['aciklama']), 0, 60, "..."); ?></p>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <b class="text-success"><?php echo $row['fiyat']; ?> TL</b>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-fav favorite-btn <?php echo $is_fav ? 'active' : ''; ?>" data-id="<?php echo $row['id']; ?>">
                                    <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                                </button>
                                <a href="listing-details.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">İncele</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <h4 class="mt-4 mb-3">🚨 Acil İhtiyaçlar</h4>
    <div class="row">
        <?php while($row = $urgent_query->fetch(PDO::FETCH_ASSOC)): 
             $is_fav = in_array($row['id'], $user_favs);
        ?>
            <div class="col-12 mb-2">
                <div class="card p-3 urgent-card shadow-sm" onclick="location.href='listing-details.php?id=<?php echo $row['id']; ?>'">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div style="width:50px;height:50px;background:#eee;border-radius:10px;display:flex;align-items:center;justify-content:center;margin-right:15px;">
                                <i class="fa-solid fa-bullhorn text-danger"></i>
                            </div>
                            <div>
                                <b class="d-block"><?php echo htmlspecialchars($row['baslik']); ?></b>
                                <span class="small text-muted"><?php echo htmlspecialchars($row['aciklama']); ?></span>
                            </div>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <?php if(isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                                <a href="edit-listing.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning me-2" onclick="event.stopPropagation();">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            <?php endif; ?>
                            <button class="btn btn-fav btn-sm favorite-btn <?php echo $is_fav ? 'active' : ''; ?>" data-id="<?php echo $row['id']; ?>" onclick="event.stopPropagation();">
                                <i class="<?php echo $is_fav ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
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