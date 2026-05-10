<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}
include 'config.php';

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini çekme
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// --- TÜRKÇE TARİH FONKSİYONU ---
function tarihFormatla($tarih) {
    if(!$tarih || $tarih == '0000-00-00 00:00:00') return "Yeni Üye";
    $aylar = [
        "January" => "Ocak", "February" => "Şubat", "March" => "Mart",
        "April" => "Nisan", "May" => "Mayıs", "June" => "Haziran",
        "July" => "Temmuz", "August" => "Ağustos", "September" => "Eylül",
        "October" => "Ekim", "November" => "Kasım", "December" => "Aralık"
    ];
    $tarih_format = date("j F Y", strtotime($tarih));
    return strtr($tarih_format, $aylar);
}
$kayit_tarihi_goster = tarihFormatla($user['kayit_tarihi'] ?? null);

// İstatistik çekme
try {
    $user_ads_count = $conn->prepare("SELECT COUNT(*) FROM ilanlar WHERE ekleyen_id = ?");
    $user_ads_count->execute([$user_id]);
    $total_my_ads = $user_ads_count->fetchColumn();
} catch (Exception $e) { $total_my_ads = 0; }
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Paneli | KAMPU$HOP</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #2d3436;
            --main-bg: #f4f7f6;
            --accent-blue: #4361ee;
        }
        body { background-color: var(--main-bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        
        /* Sidebar Tasarımı */
        .sidebar {
            width: 260px;
            height: 100vh;
            background-color: var(--sidebar-bg);
            color: white;
            position: fixed;
            padding: 30px 20px;
            z-index: 100;
        }
        .sidebar .brand { 
            font-weight: 800; 
            font-size: 1.4rem; 
            margin-bottom: 50px; 
            display: flex; 
            align-items: center; 
            gap: 10px;
            text-decoration: none; 
            color: white; 
        }
        .sidebar .brand img {
            height: 35px;
            width: auto;
            border-radius: 6px;
        }
        .nav-link-custom {
            padding: 12px 15px;
            color: #b2bec3;
            text-decoration: none;
            display: flex;
            align-items: center;
            border-radius: 10px;
            margin-bottom: 10px;
            transition: 0.3s;
            font-weight: 600;
        }
        .nav-link-custom i { margin-right: 12px; width: 20px; text-align: center; }
        .nav-link-custom.active { background-color: var(--accent-blue); color: white; }
        .nav-link-custom:hover:not(.active) { background-color: rgba(255,255,255,0.05); color: white; }
        
        /* İçerik Alanı */
        .main-content { margin-left: 260px; padding: 40px; }
        
        /* Kart Stilleri */
        .card-custom {
            background: white;
            border-radius: 20px;
            border: none;
            box-shadow: 0 4px 25px rgba(0,0,0,0.03);
            padding: 30px;
        }
        .stat-box {
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            color: white;
            padding: 25px;
            border-radius: 20px;
            margin-bottom: 25px;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.15);
        }
        
        /* Form Elemanları */
        .form-label { font-weight: 700; font-size: 0.8rem; color: #636e72; text-transform: uppercase; }
        .form-control {
            border-radius: 12px;
            padding: 14px;
            border: 2px solid #edf2f7;
            background: #f8fafc;
            font-weight: 600;
            transition: 0.3s;
        }
        .form-control:focus {
            background: white;
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.05);
        }
        .btn-update {
            background-color: var(--accent-blue);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 700;
            width: 100%;
            margin-top: 10px;
            transition: 0.3s;
        }
        .btn-update:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(67, 97, 238, 0.2); }

        /* Butonlar ve Rozetler */
        .btn-home {
            background: white;
            color: var(--accent-blue);
            border: 2px solid var(--accent-blue);
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
        }
        .btn-home:hover { background: var(--accent-blue); color: white; }
        .badge-status { background: #e8f5e9; color: #2e7d32; padding: 8px 16px; border-radius: 10px; font-weight: 700; font-size: 0.8rem; }
    </style>
</head>
<body>

    <div class="sidebar">
        <!-- Logo Entegrasyonu ve Marka -->
        <a href="index.php" class="brand">
            <img src="logo.jpeg" alt="Logo" onerror="this.style.display='none'">
            <span>KAMPÜ$HOP</span>
        </a>
        
        <nav>
            <a href="profil.php" class="nav-link-custom active"><i class="fa-solid fa-user"></i> Profilim</a>
            
            <!-- YENİ: Favorilerim Menü Öğesi -->
            <a href="favorilerim.php" class="nav-link-custom"><i class="fa-solid fa-heart text-danger"></i> Favorilerim</a>
            
            <a href="create-listing.php" class="nav-link-custom"><i class="fa-solid fa-tag"></i> Ürün Sat</a>
            <a href="#" class="nav-link-custom"><i class="fa-solid fa-cart-shopping"></i> Siparişlerim</a>
            
            <div style="margin-top: 50px;">
                <a href="auth-logout.php" class="nav-link-custom text-danger"><i class="fa-solid fa-power-off"></i> Çıkış Yap</a>
            </div>
        </nav>
    </div>

    <div class="main-content">
        <div class="header-section d-flex justify-content-between align-items-start mb-4">
            <div>
                <h2 class="fw-bold mb-1">Selam, <?= htmlspecialchars($user['ad_soyad']) ?> 👋</h2>
                <p class="text-muted">Hesap ayarlarını ve e-ticaret performansını buradan yönetebilirsin.</p>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="index.php" class="btn-home"><i class="fa-solid fa-house me-1"></i> Vitrine Dön</a>
                <span class="badge-status">● Aktif Hesap</span>
            </div>
        </div>

        <?php if(isset($_GET['status']) && $_GET['status'] == 'ok'): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4" style="border-radius: 15px;">
                <i class="fa-solid fa-check-circle me-2"></i> Bilgilerin başarıyla güncellendi!
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-7">
                <div class="card-custom">
                    <h5 class="fw-bold mb-4"><i class="fa-solid fa-address-card text-primary me-2"></i>Profil Bilgileri</h5>
                    <form action="/web_proje/update-profile.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">AD SOYAD</label>
                            <input type="text" name="ad_soyad" class="form-control" value="<?= htmlspecialchars($user['ad_soyad']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">E-POSTA ADRESİ</label>
                            <input type="email" name="eposta" class="form-control" value="<?= htmlspecialchars($user['eposta']) ?>" required>
                        </div>
                        <button type="submit" class="btn-update">Bilgileri Güncelle</button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="stat-box shadow-sm">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="mb-1 fw-bold opacity-75 small">SATIŞTAKİ ÜRÜNLERİM</p>
                            <h1 class="fw-bold mb-0"><?= $total_my_ads ?></h1>
                        </div>
                        <i class="fa-solid fa-store fa-3x opacity-25"></i>
                    </div>
                </div>

                <div class="card-custom">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold mb-0 text-muted">MAĞAZA PERFORMANSI</h6>
                        <span class="badge rounded-pill bg-warning text-dark fw-bold" style="font-size: 0.7rem;">
                            <i class="fa-solid fa-star me-1"></i> 4.8 / 5.0
                        </span>
                    </div>

                    <div class="p-3 mb-3 bg-light rounded-3 d-flex align-items-center">
                        <div class="bg-white p-2 rounded-circle shadow-sm me-3">
                            <i class="fa-solid fa-calendar-check text-primary"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted small fw-bold" style="font-size: 0.7rem;">KATILIM TARİHİ</span>
                            <span class="fw-bold small text-dark"><?= $kayit_tarihi_goster ?></span>
                        </div>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-6">
                            <div class="border rounded-3 p-2 text-center bg-white shadow-sm">
                                <span class="text-muted d-block" style="font-size: 0.65rem; font-weight: 800;">TAMAMLANAN</span>
                                <span class="fw-bold text-success">12 Satış</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="border rounded-3 p-2 text-center bg-white shadow-sm">
                                <span class="text-muted d-block" style="font-size: 0.65rem; font-weight: 800;">İLGİ GÖREN</span>
                                <span class="fw-bold text-info">45 Favori</span>
                            </div>
                        </div>
                    </div>

                    <hr class="opacity-50">

                    <div class="d-flex align-items-center text-primary mt-2">
                        <i class="fa-solid fa-shield-halved me-2"></i>
                        <span class="small fw-bold">Kampüs Onaylı Satıcı Profili</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>