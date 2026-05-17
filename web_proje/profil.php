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


// --- SATIŞTAKİ ÜRÜN SAYISINI DÜZELTEN SORGUPARTİSİ ---
$total_my_ads = 0;
try {
    // Önce ilanlar tablosunda hangi sütunun olduğunu anlamak için örnek bir satır çekiyoruz
    $check_column = $conn->query("SELECT * FROM ilanlar LIMIT 1")->fetch(PDO::FETCH_ASSOC);
    
    $column_name = 'ekleyen_id'; // Varsayılan
    if ($check_column) {
        if (array_key_exists('user_id', $check_column)) {
            $column_name = 'user_id';
        } elseif (array_key_exists('kullanici_id', $check_column)) {
            $column_name = 'kullanici_id';
        }
    }

    // Doğru sütun ismiyle ilan sayısını dinamik olarak saydırıyoruz
    $user_ads_count = $conn->prepare("SELECT COUNT(*) FROM ilanlar WHERE {$column_name} = ?");
    $user_ads_count->execute([$user_id]);
    $total_my_ads = $user_ads_count->fetchColumn();
} catch (Exception $e) { 
    $total_my_ads = 0; 
}


// --- SON MESAJI ÇEKME ---
try {
    $last_msg_stmt = $conn->prepare("
        SELECT m.*, u.ad_soyad AS gonderen_adi 
        FROM mesajlar m 
        JOIN users u ON m.gonderen_id = u.id 
        WHERE m.alici_id = ? 
        ORDER BY m.gonderilme_tarihi DESC 
        LIMIT 1
    ");
    $last_msg_stmt->execute([$user_id]);
    $son_mesaj = $last_msg_stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $son_mesaj = null;
}
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
            --sidebar-bg: linear-gradient(135deg, #1e2530 0%, #283344 100%);
            --main-bg: #f4f7f6;
            --accent-blue: #4361ee;
        }
        body { background-color: var(--main-bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; }
        
        .sidebar {
            width: 280px;
            height: 100vh;
            background: var(--sidebar-bg);
            color: white;
            position: fixed;
            padding: 30px 20px;
            z-index: 100;
            display: flex;
            flex-direction: column;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }
        
        .sidebar .brand-wrapper {
            text-align: center;
            width: 100%;
            margin-bottom: 40px;
        }
        
        .sidebar .brand-link {
            text-decoration: none;
            display: inline-block;
        }
        
        .sidebar .brand-logo {
            height: 85px;
            width: auto;
            display: block;
            margin: 0 auto 10px auto;
        }
        
        .sidebar .brand-text {
            margin: 0;
            font-weight: 800;
            font-size: 1.6rem;
            color: #ffffff;
            letter-spacing: -0.5px;
        }
        
        .sidebar .brand-text span { color: #2ecc71; }

        .sidebar nav {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-grow: 1;
        }
        
        .nav-link-custom {
            padding: 14px 18px;
            color: rgba(255, 255, 255, 0.65);
            text-decoration: none;
            display: flex;
            align-items: center;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .nav-link-custom i { 
            margin-right: 14px; 
            width: 22px; 
            text-align: center; 
            font-size: 1.1rem;
        }
        
        .nav-link-custom.active { 
            background-color: var(--accent-blue); 
            color: white; 
            box-shadow: 0 4px 15px rgba(67, 97, 238, 0.25);
        }
        
        .nav-link-custom:hover:not(.active) { 
            background-color: rgba(255, 255, 255, 0.07); 
            color: white; 
            padding-left: 22px;
        }
        
        .sidebar .logout-wrapper {
            margin-top: auto;
            padding-top: 20px;
        }
        
        .nav-link-custom.logout-link {
            color: #ff7675;
            background-color: rgba(255, 118, 117, 0.05);
            border: 1px solid rgba(255, 118, 117, 0.1);
        }
        
        .nav-link-custom.logout-link:hover {
            background-color: rgba(255, 118, 117, 0.15);
            color: #ff7675;
        }
        
        .main-content { margin-left: 280px; padding: 40px; }
        
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
        <div class="brand-wrapper">
            <a href="index.php" class="brand-link">
                <img src="logo.png" alt="Logo" class="brand-logo" onerror="this.style.display='none'">
                <h2 class="brand-text">KAMPU<span>$</span>HOP</h2>
            </a>
        </div> 
        
        <nav>
            <a href="profil.php" class="nav-link-custom active"><i class="fa-solid fa-user"></i> Profilim</a>
            <a href="create-listing.php" class="nav-link-custom"><i class="fa-solid fa-tag"></i> Ürün Sat</a>
            <a href="favorilerim.php" class="nav-link-custom"><i class="fa-solid fa-heart"></i> Favorilerim</a>
            <a href="orders.php" class="nav-link-custom"><i class="fa-solid fa-cart-shopping"></i> Siparişlerim</a>            <a href="messages.php" class="nav-link-custom"><i class="fa-solid fa-comment-dots"></i> Mesajlarım</a>
        </nav>

        <div class="logout-wrapper">
            <a href="auth-logout.php" class="nav-link-custom logout-link"><i class="fa-solid fa-power-off"></i> Çıkış Yap</a>
        </div>
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
                        <h6 class="fw-bold mb-0 text-muted">SON GELEN MESAJ</h6>
                        <span class="badge rounded-pill bg-primary text-white fw-bold" style="font-size: 0.7rem; padding: 5px 10px;">
                            <i class="fa-solid fa-envelope me-1"></i> Canlı Kutu
                        </span>
                    </div>

                    <?php if ($son_mesaj): ?>
                        <div class="p-3 mb-3 bg-light rounded-3 d-flex align-items-start">
                            <div class="bg-white p-2 rounded-circle shadow-sm me-3 text-center" style="width: 40px; height: 40px;">
                                <i class="fa-solid fa-comment text-primary" style="line-height: 24px;"></i>
                            </div>
                            <div style="flex-grow: 1;">
                                <span class="d-block text-dark fw-bold small"><?= htmlspecialchars($son_mesaj['gonderen_adi']) ?></span>
                                <p class="text-muted mb-0 small text-truncate" style="max-width: 220px;">
                                    <?= htmlspecialchars($son_mesaj['mesaj_icerigi']) ?>
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-4 mb-3 bg-light rounded-3 text-center text-muted">
                            <i class="fa-solid fa-comments fa-2x mb-2 opacity-50"></i>
                            <p class="small mb-0 fw-bold">Henüz gelen bir mesajınız yok.</p>
                        </div>
                    <?php endif; ?>

                    <div class="p-3 bg-light rounded-3 d-flex align-items-center mt-2">
                        <div class="bg-white p-2 rounded-circle shadow-sm me-3">
                            <i class="fa-solid fa-calendar-check text-success"></i>
                        </div>
                        <div>
                            <span class="d-block text-muted small fw-bold" style="font-size: 0.7rem;">KAMPÜS KATILIMIN</span>
                            <span class="fw-bold small text-dark"><?= $kayit_tarihi_goster ?></span>
                        </div>
                    </div>

                    <hr class="opacity-50">

                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <span class="small fw-bold text-success"><i class="fa-solid fa-circle-check me-1"></i> Hesap Doğrulanmış</span>
                        <a href="messages.php" class="text-decoration-none small fw-bold text-primary">Tüm Mesajlar <i class="fa-solid fa-arrow-right ms-1"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>