<?php
// HATA RAPORLAMAYI AÇTIK (Neden gitmediğini ekranda görebilmek için)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}
include 'config.php';
$user_id = $_SESSION['user_id']; // Giriş yapan kullanıcının ID'si

// URL'den gelen ilan ve karşı tarafın ID'si
$aktif_ilan_id = isset($_GET['ilan_id']) ? intval($_GET['ilan_id']) : null;
$karsi_taraf_id = isset($_GET['alici_id']) ? intval($_GET['alici_id']) : null;

// SİSTEMİN ÇÖZÜMÜ: Eğer url boşsa ama listeden bir sohbet seçildiyse onu aktif yap
if (!$aktif_ilan_id && isset($_GET['chat_ilan_id'])) {
    $aktif_ilan_id = intval($_GET['chat_ilan_id']);
    $karsi_taraf_id = intval($_GET['chat_user_id']);
}

// FORM GÖNDERİLDİĞİNDE (Mesaj Atıldığında)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['mesaj_gonder'])) {
    $ilan_id = intval($_POST['ilan_id']);
    $alici_id = intval($_POST['alici_id']); 
    $mesaj_icerigi = trim($_POST['mesaj_icerigi']);

    if (!empty($mesaj_icerigi) && $alici_id > 0) {
        // Mesajı gönderen her zaman giriş yapan $user_id'dir. Alıcı ise karşı taraftır.
        $insert = $conn->prepare("INSERT INTO mesajlar (ilan_id, gonderen_id, alici_id, mesaj_icerigi) VALUES (?, ?, ?, ?)");
        $insert->execute([$ilan_id, $user_id, $alici_id, $mesaj_icerigi]);
        
        // Doğru yönlendirme: Sayfa yenilenirken konuşulan kişi kimse (alici_id) ona yönlenmeli
        header("Location: messages.php?ilan_id=$ilan_id&alici_id=$alici_id");
        exit();
    }
}

// SOL TARAFTAKİ SOHBET LİSTESİ (Çapraz Sorgu)
$sohbetler_stmt = $conn->prepare("
    SELECT DISTINCT 
        m.ilan_id,
        i.baslik AS ilan_baslik,
        IF(m.gonderen_id = ?, m.alici_id, m.gonderen_id) AS sohbet_edilen_id,
        u.ad_soyad AS sohbet_edilen_isim
    FROM mesajlar m
    JOIN ilanlar i ON m.ilan_id = i.id
    JOIN users u ON u.id = IF(m.gonderen_id = ?, m.alici_id, m.gonderen_id)
    WHERE m.gonderen_id = ? OR m.alici_id = ?
    ORDER BY m.id DESC
");
$sohbetler_stmt->execute([$user_id, $user_id, $user_id, $user_id]);
$sohbet_listesi = $sohbetler_stmt->fetchAll(PDO::FETCH_ASSOC);

// KARŞILIKLI MESAJ GEÇMİŞİNİ ÇEKME
$mesajlar = [];
$aktif_sohbet_isim = "";
$aktif_ilan_baslik = "";

if ($aktif_ilan_id && $karsi_taraf_id) {
    // Karşı tarafın ismini çek
    $u_stmt = $conn->prepare("SELECT ad_soyad FROM users WHERE id = ?");
    $u_stmt->execute([$karsi_taraf_id]);
    $aktif_sohbet_isim = $u_stmt->fetchColumn();

    // İlan başlığını çek
    $i_stmt = $conn->prepare("SELECT baslik FROM ilanlar WHERE id = ?");
    $i_stmt->execute([$aktif_ilan_id]);
    $aktif_ilan_baslik = $i_stmt->fetchColumn();

    // KESİN ÇÖZÜM SORGUSU: Çaprazlama kontrol
    $m_stmt = $conn->prepare("
        SELECT * FROM mesajlar 
        WHERE (ilan_id = ? AND gonderen_id = ? AND alici_id = ?) 
           OR (ilan_id = ? AND gonderen_id = ? AND alici_id = ?)
        ORDER BY id ASC
    ");
    $m_stmt->execute([$aktif_ilan_id, $user_id, $karsi_taraf_id, $aktif_ilan_id, $karsi_taraf_id, $user_id]);
    $mesajlar = $m_stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mesaj Kutusu | KAMPU$HOP</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root { 
            --sidebar-bg: linear-gradient(135deg, #1e2530 0%, #283344 100%); 
            --main-bg: #f4f7f6; 
            --accent-blue: #4361ee; 
            --chat-bubble-sent: #4361ee;
            --chat-bubble-received: #ffffff;
        }
        body { background-color: var(--main-bg); font-family: 'Plus Jakarta Sans', sans-serif; margin: 0; overflow: hidden; }
        .sidebar { width: 280px; height: 100vh; background: var(--sidebar-bg); color: white; position: fixed; padding: 30px 20px; display: flex; flex-direction: column; z-index: 10; }
        .nav-link-custom { padding: 14px 18px; color: rgba(255, 255, 255, 0.65); text-decoration: none; display: flex; align-items: center; border-radius: 12px; transition: 0.3s; font-weight: 600; font-size: 0.95rem; }
        .nav-link-custom i { margin-right: 14px; width: 22px; text-align: center; }
        .nav-link-custom.active { background-color: var(--accent-blue); color: white; box-shadow: 0 4px 15px rgba(67, 97, 238, 0.25); }
        .main-content { margin-left: 280px; padding: 30px; height: 100vh; display: flex; flex-direction: column; }
        .chat-container { background: white; border-radius: 24px; box-shadow: 0 10px 40px rgba(0,0,0,0.04); flex-grow: 1; display: flex; overflow: hidden; height: calc(100vh - 120px); border: 1px solid #edf2f7; }
        .chat-sidebar { width: 340px; border-right: 1px solid #edf2f7; display: flex; flex-direction: column; background: #ffffff; }
        .chat-sidebar-header { padding: 20px; border-bottom: 1px solid #edf2f7; font-weight: 800; color: #2d3436; font-size: 0.9rem; letter-spacing: 0.5px; background: #f8fafc; }
        .chat-list-wrapper { overflow-y: auto; flex-grow: 1; }
        .chat-list-item { padding: 18px 20px; border-bottom: 1px solid #f8fafc; text-decoration: none; color: inherit; display: flex; align-items: center; gap: 14px; transition: all 0.2s ease; border-left: 4px solid transparent; }
        .chat-list-item:hover { background: #f8fafc; }
        .chat-list-item.active { background: #f0f3ff; border-left-color: var(--accent-blue); }
        .avatar-circle { width: 44px; height: 44px; background: linear-gradient(135deg, #e0e6ed 0%, #b8c2cc 100%); color: #4b5563; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.95rem; }
        .chat-list-item.active .avatar-circle { background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%); color: white; }
        .chat-main { flex-grow: 1; display: flex; flex-direction: column; background: #f4f7f6; position: relative; }
        .chat-header { padding: 18px 25px; border-bottom: 1px solid #edf2f7; background: white; display: flex; align-items: center; gap: 12px; }
        .chat-header h6 { font-weight: 700; color: #2d3436; margin-bottom: 2px; }
        .chat-body { flex-grow: 1; padding: 30px; overflow-y: auto; display: flex; flex-direction: column; gap: 16px; background: #f8fafc; }
        .msg-row { display: flex; width: 100%; }
        .msg-row.sent { justify-content: flex-end; }
        .msg-row.received { justify-content: flex-start; }
        .msg-bubble { max-width: 60%; padding: 12px 18px; font-weight: 500; font-size: 0.92rem; line-height: 1.5; }
        .sent .msg-bubble { background: var(--chat-bubble-sent); color: white; border-radius: 18px 18px 4px 18px; box-shadow: 0 4px 12px rgba(67, 97, 238, 0.15); }
        .received .msg-bubble { background: var(--chat-bubble-received); color: #2d3436; border-radius: 18px 18px 18px 4px; border: 1px solid #edf2f7; }
        .chat-footer { padding: 20px 25px; background: white; border-top: 1px solid #edf2f7; }
        .chat-input-box { border-radius: 30px; padding: 14px 22px; border: 2px solid #edf2f7; background: #f8fafc; font-weight: 600; font-size: 0.95rem; }
        .btn-send { width: 50px; height: 50px; border-radius: 50%; background: var(--accent-blue); color: white; border: none; display: flex; align-items: center; justify-content: center; transition: 0.2s; }
        .empty-chat-state { text-align: center; margin: auto; max-width: 400px; color: #a0aec0; }
        .empty-chat-state i { background: white; padding: 25px; border-radius: 50%; box-shadow: 0 10px 30px rgba(0,0,0,0.03); color: var(--accent-blue); margin-bottom: 20px; }
    </style>
</head>
<body>

    <div class="sidebar">
        <div class="brand-wrapper text-center mb-4">
            <h2 class="fw-bold text-white" style="font-size: 1.6rem; letter-spacing: -0.5px; margin-top:15px;">KAMPU<span class="text-success">$</span>HOP</h2>
        </div> 
        <nav class="d-flex flex-column gap-2">
            <a href="profil.php" class="nav-link-custom"><i class="fa-solid fa-user"></i> Profilim</a>
            <a href="create-listing.php" class="nav-link-custom"><i class="fa-solid fa-tag"></i> Ürün Sat</a>
            <a href="favorilerim.php" class="nav-link-custom"><i class="fa-solid fa-heart"></i> Favorilerim</a>
            <a href="orders.php" class="nav-link-custom"><i class="fa-solid fa-cart-shopping"></i> Siparişlerim</a>
            <a href="messages.php" class="nav-link-custom active"><i class="fa-solid fa-comment-dots"></i> Mesajlarım</a>
        </nav>
    </div>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark mb-0">Mesajlaşma Paneli</h3>
        </div>
        
        <div class="chat-container">
            <div class="chat-sidebar">
                <div class="chat-sidebar-header"><i class="fa-solid fa-comments me-2 text-primary"></i>AKTİF SOHBETLERİNİZ</div>
                <div class="chat-list-wrapper">
                    <?php if (empty($sohbet_listesi) && !$karsi_taraf_id): ?>
                        <div class="p-5 text-center text-muted small">Henüz bir sohbet geçmişiniz bulunmuyor.</div>
                    <?php else: ?>
                        <?php foreach ($sohbet_listesi as $sohbet): 
                            $is_active = ($sohbet['ilan_id'] == $aktif_ilan_id && $sohbet['sohbet_edilen_id'] == $karsi_taraf_id);
                            $harf = mb_substr($sohbet['sohbet_edilen_isim'], 0, 1, 'UTF-8');
                        ?>
                            <a href="messages.php?chat_ilan_id=<?= $sohbet['ilan_id'] ?>&chat_user_id=<?= $sohbet['sohbet_edilen_id'] ?>" class="chat-list-item <?= $is_active ? 'active' : '' ?>">
                                <div class="avatar-circle"><?= strtoupper($harf) ?></div>
                                <div style="flex-grow: 1; min-width: 0;">
                                    <div class="fw-bold text-dark small text-truncate"><?= htmlspecialchars($sohbet['sohbet_edilen_isim']) ?></div>
                                    <span class="badge bg-light text-secondary border mt-1 extra-small text-truncate d-inline-block" style="font-size:0.7rem;"><i class="fa-solid fa-tag me-1"></i><?= htmlspecialchars($sohbet['ilan_baslik']) ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="chat-main">
                <?php if ($aktif_ilan_id && $karsi_taraf_id): 
                    $aktif_harf = mb_substr($aktif_sohbet_isim, 0, 1, 'UTF-8');
                ?>
                    <div class="chat-header">
                        <div class="avatar-circle bg-primary text-white" style="width: 40px; height: 40px; font-size: 0.85rem;"><?= strtoupper($aktif_harf) ?></div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($aktif_sohbet_isim) ?></h6>
                            <small class="text-muted" style="font-size: 0.75rem;"><i class="fa-solid fa-bag-shopping text-primary"></i> İlan: <?= htmlspecialchars($aktif_ilan_baslik) ?></small>
                        </div>
                    </div>

                    <div class="chat-body">
                        <?php if (empty($mesajlar)): ?>
                            <div class="text-center text-muted my-auto small bg-white p-3 rounded-4 shadow-sm mx-auto">İlk mesajı yazarak sohbeti hemen başlatın!</div>
                        <?php else: ?>
                            <?php foreach ($mesajlar as $msg): 
                                $is_my_msg = ($msg['gonderen_id'] == $user_id);
                            ?>
                                <div class="msg-row <?= $is_my_msg ? 'sent' : 'received' ?>">
                                    <div class="msg-bubble shadow-sm">
                                        <?= htmlspecialchars($msg['mesaj_icerigi']) ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <div class="chat-footer">
                        <form action="messages.php?ilan_id=<?= $aktif_ilan_id ?>&alici_id=<?= $karsi_taraf_id ?>" method="POST" class="d-flex gap-3 align-items-center">
                            <input type="hidden" name="ilan_id" value="<?= $aktif_ilan_id ?>">
                            <input type="hidden" name="alici_id" value="<?= $karsi_taraf_id ?>">
                            <input type="text" name="mesaj_icerigi" class="form-control chat-input-box" placeholder="Mesajınızı buraya yazın..." required autocomplete="off">
                            <button type="submit" name="mesaj_gonder" class="btn-send"><i class="fa-solid fa-paper-plane"></i></button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="empty-chat-state">
                        <i class="fa-solid fa-comments fa-3x mb-3"></i>
                        <h5 class="fw-bold text-dark">Sohbet Seçimi Yapın</h5>
                        <p class="small text-muted">Konuşmak istediğiniz kişiyi seçerek anlık mesajlaşmaya başlayabilirsiniz.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>
</html>