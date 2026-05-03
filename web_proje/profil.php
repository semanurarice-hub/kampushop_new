<?php
session_start();
include 'config.php';

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM kullanicilar WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if(!$user){
    die("Kullanıcı bulunamadı!");
}

$orders = $orders ?? [];
$products = $products ?? [];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kullanıcı Paneli | KampüShop</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --sidebar-bg: #1e293b;
        --accent-color: #6366f1;
        --bg-light: #f8fafc;
    }

    body {
        background: var(--bg-light);
        font-family: 'Inter', sans-serif;
        color: #334155;
    }

    /* SIDEBAR */
    .sidebar {
        width: 260px;
        height: 100vh;
        background: var(--sidebar-bg);
        color: white;
        position: fixed;
        padding: 24px;
        box-shadow: 4px 0 10px rgba(0,0,0,0.05);
    }

    .sidebar .brand {
        font-size: 22px;
        font-weight: 700;
        margin-bottom: 40px;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar a {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #94a3b8;
        text-decoration: none;
        padding: 12px 15px;
        border-radius: 10px;
        margin-bottom: 8px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .sidebar a:hover, .sidebar a.active {
        background: rgba(255,255,255,0.1);
        color: white;
        transform: translateX(5px);
    }

    .sidebar a.active {
        background: var(--accent-color);
        color: white;
    }

    /* CONTENT AREA */
    .content {
        margin-left: 260px;
        padding: 40px;
    }

    /* MODERN CARDS */
    .card-box {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        margin-bottom: 24px;
    }

    .section-title {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* FORM STYLES */
    .form-control {
        border-radius: 10px;
        padding: 12px;
        border: 1px solid #e2e8f0;
        background-color: #fcfcfd;
    }

    .form-control:focus {
        border-color: var(--accent-color);
        box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
    }

    .btn-update {
        background: var(--accent-color);
        border: none;
        padding: 12px;
        border-radius: 10px;
        font-weight: 600;
        transition: 0.3s;
    }

    .btn-update:hover {
        background: #4f46e5;
        transform: translateY(-2px);
    }

    /* LIST ITEMS */
    .item-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background: #f8fafc;
        border-radius: 12px;
        border: 1px solid transparent;
        transition: 0.2s;
    }

    .item:hover {
        border-color: #cbd5e1;
        background: #fff;
    }

    .price-tag {
        font-weight: 700;
        color: var(--accent-color);
    }

    @media (max-width: 768px) {
        .sidebar { width: 100%; height: auto; position: relative; }
        .content { margin-left: 0; padding: 20px; }
    }
</style>
</head>
<body>

<div class="sidebar">
    <div class="brand">
    <i class="fa-solid fa-store"></i> KAMPÜSHOP
</div>

    <a href="profil.php" class="active"><i class="fa-solid fa-user"></i> Profil</a>
    <a href="add_product.php"><i class="fa-solid fa-plus-circle"></i> Ürün Sat</a>
    <a href="my_orders.php"><i class="fa-solid fa-receipt"></i> Siparişlerim</a>
    <hr style="opacity: 0.1">
    <a href="logout.php" style="color: #f87171;"><i class="fa-solid fa-right-from-bracket"></i> Çıkış Yap</a>
</div>

<div class="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Selam, <?= htmlspecialchars($user['ad_soyad']) ?> 👋</h2>
            <p class="text-muted">Hesap ayarlarını ve işlemlerini buradan yönetebilirsin.</p>
        </div>
        <div class="text-end">
            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2 border border-success-subtle">
                <i class="fa-solid fa-circle-check me-1"></i> Aktif Hesap
            </span>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5">
            <div class="card-box">
                <h5 class="section-title"><i class="fa-solid fa-id-card"></i> Profil Bilgileri</h5>
                <form action="update_profile.php" method="POST">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ad Soyad</label>
                        <input class="form-control" name="ad_soyad" value="<?= htmlspecialchars($user['ad_soyad']) ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">E-posta Adresi</label>
                        <input class="form-control" name="eposta" value="<?= htmlspecialchars($user['eposta']) ?>">
                    </div>
                    <button class="btn btn-primary btn-update w-100 mt-2">Bilgileri Güncelle</button>
                </form>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card-box">
                <h5 class="section-title"><i class="fa-solid fa-basket-shopping"></i> Son Siparişlerim</h5>
                <div class="item-list">
                    <?php if(!empty($orders)): foreach($orders as $o): ?>
                        <div class="item">
                            <span><?= htmlspecialchars($o['product_name']) ?></span>
                            <span class="badge bg-light text-dark border">Teslim Edildi</span>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="text-center py-3">
                            <i class="fa-solid fa-box-open d-block mb-2 opacity-25" style="font-size: 2rem;"></i>
                            <p class="text-muted small">Henüz bir siparişin bulunmuyor.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card-box">
                <h5 class="section-title"><i class="fa-solid fa-tags"></i> Satışta Olan Ürünlerim</h5>
                <div class="item-list">
                    <?php if(!empty($products)): foreach($products as $p): ?>
                        <div class="item">
                            <span class="fw-medium"><?= htmlspecialchars($p['name']) ?></span>
                            <span class="price-tag"><?= number_format($p['price'], 2) ?> ₺</span>
                        </div>
                    <?php endforeach; else: ?>
                        <div class="text-center py-3">
                            <i class="fa-solid fa-store-slash d-block mb-2 opacity-25" style="font-size: 2rem;"></i>
                            <p class="text-muted small">Henüz ürün eklememişsin.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>
