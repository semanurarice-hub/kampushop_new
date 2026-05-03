<?php
session_start();
include 'config.php';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>KAMPU$HOP | Kampüs Vitrini</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body { 
        background-color: #f4f7f6; 
        font-family: 'Segoe UI', sans-serif; 
    }

    .navbar { 
        background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
        padding: 15px 0;
        box-shadow: 0 4px 15px rgba(13, 110, 253, 0.2);
    }

    .navbar-brand { 
        font-weight: 800; 
        font-size: 1.8rem; 
        color: white !important; 
    }

    .btn-post { 
        background-color: white; 
        color: #0d6efd; 
        border: none; 
        font-weight: 700;
    }

    .btn-urgent { 
        background-color: #ff4757; 
        color: white; 
        border: none;
    }

    .card {
        border: none;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        transition: 0.3s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-img-top {
        height: 220px;
        object-fit: cover;
    }
    </style>
</head>

<body>

<nav class="navbar sticky-top">
    <div class="container">

        <a class="navbar-brand" href="index.php">
            KAMPU$HOP
        </a>

        <div class="ms-auto d-flex align-items-center">

            <a href="create-listing.php" class="btn btn-post me-2">
                🛍️ İlan Ver
            </a>

            <a href="create-urgent.php" class="btn btn-urgent me-3">
                🚨 Acil İhtiyaç
            </a>

            <?php if(isset($_SESSION['user_id'])): ?>

                <span class="text-white me-2">
                    👋 <?php echo $_SESSION['user_name']; ?>
                </span>

                <a href="auth-logout.php" class="btn btn-outline-light btn-sm">
                    Çıkış
                </a>

            <?php else: ?>

                <a href="auth-login.php" class="btn btn-outline-light btn-sm">
                    Giriş
                </a>

            <?php endif; ?>

        </div>
    </div>
</nav>

<div class="container mt-5">

    <!-- NORMAL İLANLAR -->
    <div class="row">

        <?php
        $query = $conn->query("SELECT * FROM ilanlar WHERE ilan_tipi = 0 ORDER BY id DESC");

        while($row = $query->fetch(PDO::FETCH_ASSOC)):

            $img = !empty($row['resim']) ? $row['resim'] : 'default.jpg';
        ?>

        <div class="col-md-3 mb-4">

            <div class="card h-100">

                <img src="uploads/<?php echo $img; ?>" class="card-img-top">

                <div class="card-body d-flex flex-column">

                    <h6 class="fw-bold">
                        <?php echo htmlspecialchars($row['baslik']); ?>
                    </h6>

                    <p class="text-muted small flex-grow-1">
                        <?php echo htmlspecialchars($row['aciklama']); ?>
                    </p>

                    <div class="d-flex justify-content-between align-items-center">

                        <b class="text-success">
                            <?php echo $row['fiyat']; ?> TL
                        </b>

                        <a href="listing-details.php?id=<?php echo $row['id']; ?>" 
                           class="btn btn-sm btn-primary">
                            İncele
                        </a>

                    </div>

                </div>

            </div>

        </div>

        <?php endwhile; ?>

    </div>

    <!-- ACİL İHTİYAÇLAR -->
    <h4 class="mt-5">🚨 Acil İhtiyaçlar</h4>

    <?php
    $urgent = $conn->query("SELECT * FROM ilanlar WHERE ilan_tipi = 1 ORDER BY id DESC");

    while($row = $urgent->fetch(PDO::FETCH_ASSOC)):

        $img = !empty($row['resim']) ? $row['resim'] : 'default.jpg';
    ?>

    <div class="card p-3 mb-2 border-danger">

        <div class="d-flex align-items-center">

            <img src="uploads/<?php echo $img; ?>" 
                 style="width:80px;height:80px;object-fit:cover;border-radius:10px;margin-right:10px;">

            <div>
                <b><?php echo htmlspecialchars($row['baslik']); ?></b>
                <p class="mb-0"><?php echo htmlspecialchars($row['aciklama']); ?></p>
            </div>

        </div>

    </div>

    <?php endwhile; ?>

</div>

</body>
</html>