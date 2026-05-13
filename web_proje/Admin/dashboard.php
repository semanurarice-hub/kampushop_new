<?php
include 'includes/auth.php';
include '../config.php';

/* Hangi ekran ve hangi filtreler? */
$type = $_GET['type'] ?? 'users';
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

/* İSTATİSTİKLER */
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_ads = $conn->query("SELECT COUNT(*) FROM ilanlar")->fetchColumn();
$total_admins = $conn->query("SELECT COUNT(*) FROM users WHERE rol='admin'")->fetchColumn();
$total_urgent = $conn->query("SELECT COUNT(*) FROM ilanlar WHERE ilan_tipi = 1")->fetchColumn();

/* SORGU MANTIĞI (Filtrelerin Kaybolmaması İçin) */
if ($type == 'users') {
    $sql = "SELECT * FROM users WHERE 1";
    if ($search != '') $sql .= " AND (ad_soyad LIKE '%$search%' OR eposta LIKE '%$search%')";
    if ($filter == 'admin') $sql .= " AND rol='admin'";
    if ($filter == 'user') $sql .= " AND rol='user'";
    $sql .= " ORDER BY id DESC";
} 
elseif ($type == 'admins') {
    $sql = "SELECT * FROM users WHERE rol='admin'";
    if ($search != '') $sql .= " AND ad_soyad LIKE '%$search%'";
    $sql .= " ORDER BY id DESC";
} 
elseif ($type == 'ads' || $type == 'urgent') {
    // Eğer 'urgent' ise sadece acil olanları, 'ads' ise hepsini getir
    $sql = ($type == 'urgent') ? "SELECT * FROM ilanlar WHERE ilan_tipi = 1" : "SELECT * FROM ilanlar WHERE 1";
    
    if ($search != '') $sql .= " AND baslik LIKE '%$search%'";
    
    // İlan filtreleri: En yeni / En eski
    if ($filter == 'new') $sql .= " ORDER BY id DESC";
    elseif ($filter == 'old') $sql .= " ORDER BY id ASC";
    else $sql .= " ORDER BY id DESC";
}

$query = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Sistem Yöneticisi | KAMPU$HOP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { border: none; border-radius: 15px; transition: transform 0.2s; }
        .stat-card h5 { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; opacity: 0.9; }
        .stat-card h3 { font-weight: 800; }
        .active-card { transform: scale(1.05); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; border: 2px solid #fff !important; }
    </style>
</head>
<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Sistem Yöneticisi</h2>
        <a href="../auth-logout.php" class="btn btn-danger px-4" style="border-radius:10px;">Çıkış</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <a href="dashboard.php?type=users" class="text-decoration-none">
                <div class="card stat-card p-4 bg-primary text-white text-center shadow-sm <?= $type == 'users' ? 'active-card' : '' ?>">
                    <h5>Kullanıcı</h5>
                    <h3><?= $total_users ?></h3>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="dashboard.php?type=ads" class="text-decoration-none">
                <div class="card stat-card p-4 bg-success text-white text-center shadow-sm <?= $type == 'ads' ? 'active-card' : '' ?>">
                    <h5>İlanlar</h5>
                    <h3><?= $total_ads ?></h3>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="dashboard.php?type=urgent" class="text-decoration-none">
                <div class="card stat-card p-4 bg-danger text-white text-center shadow-sm <?= $type == 'urgent' ? 'active-card' : '' ?>">
                    <h5>🔥 Acil</h5>
                    <h3><?= $total_urgent ?></h3>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="dashboard.php?type=admins" class="text-decoration-none">
                <div class="card stat-card p-4 bg-dark text-white text-center shadow-sm <?= $type == 'admins' ? 'active-card' : '' ?>">
                    <h5>Admin</h5>
                    <h3><?= $total_admins ?></h3>
                </div>
            </a>
        </div>
    </div>

    <div class="card p-3 mb-3 border-0 shadow-sm" style="border-radius:15px;">
        <form method="GET" class="d-flex gap-2">
            <input type="hidden" name="type" value="<?= $type ?>">
            <input type="text" name="search" class="form-control" style="border-radius:10px;" placeholder="Ara..." value="<?= htmlspecialchars($search) ?>">
            
            <select name="filter" class="form-select" style="border-radius:10px;">
                <option value="">Filtre Yok</option>
                
                <?php if ($type == 'users'): ?>
                    <option value="admin" <?= $filter == 'admin' ? 'selected' : '' ?>>Sadece Admin</option>
                    <option value="user" <?= $filter == 'user' ? 'selected' : '' ?>>Sadece User</option>
                <?php endif; ?>

                <?php if ($type == 'ads' || $type == 'urgent'): ?>
                    <option value="new" <?= $filter == 'new' ? 'selected' : '' ?>>En Yeni</option>
                    <option value="old" <?= $filter == 'old' ? 'selected' : '' ?>>En Eski</option>
                <?php endif; ?>
            </select>

            <button class="btn btn-primary px-4" style="border-radius:10px;">Uygula</button>
        </form>
    </div>

    <div class="card border-0 shadow-sm p-4" style="border-radius:15px;">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <?php if ($type == 'ads' || $type == 'urgent'): ?>
                        <th>ID</th><th>Başlık</th><th>Fiyat</th><th class="text-end">İşlem</th>
                    <?php else: ?>
                        <th>ID</th><th>Ad Soyad</th><th>E-posta</th><th>Rol</th><th class="text-end">İşlem</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $query->fetch(PDO::FETCH_ASSOC)): ?>
                    <tr>
                        <?php if ($type == 'ads' || $type == 'urgent'): ?>
                            <td><?= $row['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['baslik']) ?></td>
                            <td><?= number_format($row['fiyat'], 2) ?> ₺</td>
                            <td class="text-end">
                                <a href="edit-ad.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Düzenle</a>
                                <a href="delete-ad.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Sil</a>
                            </td>
                        <?php else: ?>
                            <td><?= $row['id'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($row['ad_soyad']) ?></td>
                            <td><?= htmlspecialchars($row['eposta']) ?></td>
                            <td><span class="badge <?= $row['rol'] == 'admin' ? 'bg-dark' : 'bg-secondary' ?>"><?= $row['rol'] ?></span></td>
                            <td class="text-end">
                                <a href="edit-user.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Düzenle</a>
                                <a href="delete-user.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Sil</a>
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