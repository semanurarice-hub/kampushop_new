<?php
include 'includes/auth.php';
include '../config.php';

/* hangi ekran */
$type = $_GET['type'] ?? 'users';
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? '';

/* İSTATİSTİKLER */
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_ads = $conn->query("SELECT COUNT(*) FROM ilanlar")->fetchColumn();
$total_admins = $conn->query("SELECT COUNT(*) FROM users WHERE rol='admin'")->fetchColumn();

/* SQL */
if ($type == 'users') {

    $sql = "SELECT * FROM users WHERE 1";

    if ($search != '') {
        $sql .= " AND (ad_soyad LIKE '%$search%' OR eposta LIKE '%$search%')";
    }

    if ($filter == 'admin') {
        $sql .= " AND rol='admin'";
    }

    if ($filter == 'user') {
        $sql .= " AND rol='user'";
    }

    $sql .= " ORDER BY id DESC";

    $query = $conn->query($sql);

}

elseif ($type == 'admins') {

    $sql = "SELECT * FROM users WHERE rol='admin'";

    if ($search != '') {
        $sql .= " AND ad_soyad LIKE '%$search%'";
    }

    $sql .= " ORDER BY id DESC";

    $query = $conn->query($sql);
}

elseif ($type == 'ads') {

    $sql = "SELECT * FROM ilanlar WHERE 1";

    if ($search != '') {
        $sql .= " AND baslik LIKE '%$search%'";
    }

    if ($filter == 'new') {
        $sql .= " ORDER BY id DESC";
    }
    elseif ($filter == 'old') {
        $sql .= " ORDER BY id ASC";
    }
    else {
        $sql .= " ORDER BY id DESC";
    }

    $query = $conn->query($sql);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between mb-4">
        <h2>Sistem Yöneticisi</h2>
        <a href="../auth-logout.php" class="btn btn-danger btn-sm">Çıkış</a>
    </div>

    <!-- KARTLAR -->
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <a href="dashboard.php?type=users" class="text-decoration-none">
                <div class="card p-3 bg-primary text-white text-center">
                    <h5>Kullanıcı</h5>
                    <h3><?= $total_users ?></h3>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="dashboard.php?type=ads" class="text-decoration-none">
                <div class="card p-3 bg-success text-white text-center">
                    <h5>İlan</h5>
                    <h3><?= $total_ads ?></h3>
                </div>
            </a>
        </div>

        <div class="col-md-4">
            <a href="dashboard.php?type=admins" class="text-decoration-none">
                <div class="card p-3 bg-dark text-white text-center">
                    <h5>Admin</h5>
                    <h3><?= $total_admins ?></h3>
                </div>
            </a>
        </div>

    </div>

    <!-- ARAMA + FİLTRE -->
    <form method="GET" class="mb-3 d-flex gap-2">

        <input type="hidden" name="type" value="<?= $type ?>">

        <input type="text"
               name="search"
               class="form-control w-25"
               placeholder="Ara..."
               value="<?= $search ?>">

        <select name="filter" class="form-select w-25">

            <option value="">Filtre Yok</option>

            <?php if ($type == 'users'): ?>
                <option value="admin">Sadece Admin</option>
                <option value="user">Sadece User</option>
            <?php endif; ?>

            <?php if ($type == 'ads'): ?>
                <option value="new">En Yeni</option>
                <option value="old">En Eski</option>
            <?php endif; ?>

        </select>

        <button class="btn btn-primary">Uygula</button>

    </form>

    <!-- TABLO -->
    <div class="card p-4">

        <table class="table table-striped">

            <thead>
                <tr>

                <?php if ($type == 'ads'): ?>

                    <th>ID</th>
                    <th>Başlık</th>
                    <th>Fiyat</th>
                    <th>İşlem</th>

                <?php else: ?>

                    <th>ID</th>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>Rol</th>
                    <th>İşlem</th>

                <?php endif; ?>

                </tr>
            </thead>

            <tbody>

            <?php while ($row = $query->fetch(PDO::FETCH_ASSOC)): ?>
                <tr>

                <?php if ($type == 'ads'): ?>

                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['baslik']) ?></td>
                    <td><?= $row['fiyat'] ?> ₺</td>

                    <td>
                        <a href="edit-ad.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Düzenle</a>
                        <a href="delete-ad.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm">Sil</a>
                    </td>

                <?php else: ?>

                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['ad_soyad']) ?></td>
                    <td><?= htmlspecialchars($row['eposta']) ?></td>
                    <td><?= $row['rol'] ?></td>

                    <td>
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