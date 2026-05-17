<?php
include 'includes/auth.php';
include '../config.php';

/* TYPE & FILTER */
$type = $_GET['type'] ?? 'users';
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

/* STATS */
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();

$total_ads = $conn->query("
    SELECT COUNT(*) FROM ilanlar 
    WHERE ilan_tipi=0 AND durum='aktif'
")->fetchColumn();

$total_admins = $conn->query("SELECT COUNT(*) FROM users WHERE rol='admin'")->fetchColumn();

$total_urgent = $conn->query("
    SELECT COUNT(*) FROM ilanlar 
    WHERE ilan_tipi=1 AND durum='aktif'
")->fetchColumn();

// Sayacı sizin özel roommate_listings tablonuzdan çekiyor
$total_roommates = $conn->query("SELECT COUNT(*) FROM roommate_listings")->fetchColumn();

$total_sold = $conn->query("
    SELECT COUNT(*) FROM ilanlar 
    WHERE durum='satildi'
    AND ilan_tipi=0
")->fetchColumn();

/* QUERY */
if ($type == 'users') {

    $sql = "SELECT * FROM users WHERE 1";
    if ($search != '') {
        $sql .= " AND (ad_soyad LIKE '%$search%' OR eposta LIKE '%$search%')";
    }
    if ($filter == 'admin') $sql .= " AND rol='admin'";
    elseif ($filter == 'user') $sql .= " AND rol='user'";
    $sql .= " ORDER BY id DESC";

} elseif ($type == 'ads') {

    $sql = "SELECT * FROM ilanlar WHERE ilan_tipi=0 AND durum='aktif'";
    if ($search != '') $sql .= " AND baslik LIKE '%$search%'";
    $sql .= ($filter == 'old') ? " ORDER BY id ASC" : " ORDER BY id DESC";

} elseif ($type == 'urgent') {

    $sql = "SELECT * FROM ilanlar WHERE ilan_tipi=1 AND durum='aktif'";
    if ($search != '') $sql .= " AND baslik LIKE '%$search%'";
    $sql .= ($filter == 'old') ? " ORDER BY id ASC" : " ORDER BY id DESC";

} elseif ($type == 'roommates') {

    // Sihirli Değişiklik: Sizin bağımsız tablonuzdan verileri çekip takma ad (AS) veriyor
    $sql = "SELECT id, title AS baslik, budget AS fiyat, 'aktif' AS durum FROM roommate_listings WHERE 1";
    if ($search != '') {
        $sql .= " AND title LIKE '%$search%'";
    }
    $sql .= ($filter == 'old') ? " ORDER BY id ASC" : " ORDER BY id DESC";

} elseif ($type == 'sold') {

    $sql = "SELECT * FROM ilanlar WHERE durum='satildi' AND ilan_tipi=0";
    if ($search != '') $sql .= " AND baslik LIKE '%$search%'";
    $sql .= ($filter == 'old') ? " ORDER BY id ASC" : " ORDER BY id DESC";

} elseif ($type == 'admins') {

    $sql = "SELECT * FROM users WHERE rol='admin'";
    if ($search != '') $sql .= " AND ad_soyad LIKE '%$search%'";
    $sql .= " ORDER BY id DESC";
}

$query = $conn->query($sql);
$ilan_tipleri = ['ads', 'urgent', 'roommates', 'sold'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body{ background:#f5f6fa; }
a{ text-decoration:none; }
.card{ border:none; border-radius:14px; }
.stat-card{ transition:.2s; }
.stat-card:hover{ transform:translateY(-3px); }
.active-card{ border:2px solid #fff !important; box-shadow:0 8px 18px rgba(0,0,0,.12); }
.stat-title{ font-size:12px; opacity:.9; }
.stat-number{ font-size:22px; font-weight:700; }
.table td, .table th{ vertical-align:middle; }
</style>
</head>

<body>

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>Admin Panel</h3>
        <a href="../auth-logout.php" class="btn btn-danger btn-sm">Çıkış</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <a href="?type=users">
                <div class="card stat-card p-3 bg-primary text-white text-center <?= $type=='users'?'active-card':'' ?>">
                    <div class="stat-title">Kullanıcı</div>
                    <div class="stat-number"><?= $total_users ?></div>
                </div>
            </a>
        </div>

        <div class="col-md-2">
            <a href="?type=ads">
                <div class="card stat-card p-3 bg-success text-white text-center <?= $type=='ads'?'active-card':'' ?>">
                    <div class="stat-title">İlanlar</div>
                    <div class="stat-number"><?= $total_ads ?></div>
                </div>
            </a>
        </div>

        <div class="col-md-2">
            <a href="?type=urgent">
                <div class="card stat-card p-3 bg-danger text-white text-center <?= $type=='urgent'?'active-card':'' ?>">
                    <div class="stat-title">Acil</div>
                    <div class="stat-number"><?= $total_urgent ?></div>
                </div>
            </a>
        </div>

        <div class="col-md-2">
            <a href="?type=roommates">
                <div class="card stat-card p-3 bg-info text-white text-center <?= $type=='roommates'?'active-card':'' ?>">
                    <div class="stat-title">Ev Ark.</div>
                    <div class="stat-number"><?= $total_roommates ?></div>
                </div>
            </a>
        </div>

        <div class="col-md-2">
            <a href="?type=sold">
                <div class="card stat-card p-3 bg-secondary text-white text-center <?= $type=='sold'?'active-card':'' ?>">
                    <div class="stat-title">Satılan</div>
                    <div class="stat-number"><?= $total_sold ?></div>
                </div>
            </a>
        </div>

        <div class="col-md-2">
            <a href="?type=admins">
                <div class="card stat-card p-3 bg-dark text-white text-center <?= $type=='admins'?'active-card':'' ?>">
                    <div class="stat-title">Admin</div>
                    <div class="stat-number"><?= $total_admins ?></div>
                </div>
            </a>
        </div>
    </div>

    <div class="card p-3 mb-3">
        <form class="d-flex gap-2">
            <input type="hidden" name="type" value="<?= $type ?>">
            <input type="text" name="search" class="form-control" placeholder="Ara..." value="<?= htmlspecialchars($search) ?>">
            <select name="filter" class="form-select" style="max-width:150px;">
                <option value="">Filtre</option>
                <option value="new" <?= $filter=='new'?'selected':'' ?>>Yeni</option>
                <option value="old" <?= $filter=='old'?'selected':'' ?>>Eski</option>
            </select>
            <button class="btn btn-primary">Ara</button>
        </form>
    </div>

    <div class="card p-3">
        <table class="table table-hover">
            <thead>
                <tr>
                    <?php if (in_array($type, $ilan_tipleri)): ?>
                        <th>ID</th>
                        <th>Başlık</th>
                        <th>Bütçe / Fiyat</th>
                        <th>Durum</th>
                        <th>İşlem</th>
                    <?php else: ?>
                        <th>ID</th>
                        <th>Ad</th>
                        <th>E-posta</th>
                        <th>Rol</th>
                        <th>İşlem</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php while($row = $query->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>
                <?php if (in_array($type, $ilan_tipleri)): ?>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['baslik']) ?></td>
                    <td><?= number_format($row['fiyat'], 2) ?> ₺</td>
                    <td>
                        <span class="badge bg-success">aktif</span>
                    </td>
                    <td>
                        <?php if($type == 'roommates'): ?>
                            <a href="delete-roommate.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu ev arkadaşı ilanını silmek istediğinize emin misiniz?')">Sil</a>
                        <?php else: ?>
                            <a href="edit-ad.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Düzenle</a>
                            <a href="delete-ad.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu ilanı silmek istediğinize emin misiniz?')">Sil</a>
                        <?php endif; ?>
                    </td>
                <?php else: ?>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['ad_soyad'] ?? '') ?></td>
                    <td><?= htmlspecialchars($row['eposta'] ?? '') ?></td>
                    <td><span class="badge bg-dark"><?= htmlspecialchars($row['rol'] ?? '') ?></span></td>
                    <td>
                        <a href="edit-user.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Düzenle</a>
                        <a href="delete-user.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Bu kullanıcıyı silmek istediğinize emin misiniz?')">Sil</a>
                    </td>
                <?php endif; ?>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>