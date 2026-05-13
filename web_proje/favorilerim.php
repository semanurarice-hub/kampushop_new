<?php
session_start();
include 'config.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = $conn->prepare("
    SELECT ilanlar.* FROM ilanlar 
    INNER JOIN favoriler ON ilanlar.id = favoriler.ilan_id 
    WHERE favoriler.user_id = ? 
    ORDER BY favoriler.id DESC
");

$query->execute([$user_id]);

$favori_ilanlar = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Favorilerim | KAMPU$HOP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap"
          rel="stylesheet">

    <style>

        body {
            background-color: #f4f7f6;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .navbar {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            padding: 12px 0;
        }

        .navbar-brand {
            font-weight: 800;
            color: white !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .card-ilan {
            transition: 0.3s;
            border-radius: 16px !important;
            border: none !important;
            position: relative;
        }

        .card-ilan:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        }

        .fav-remove-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            z-index: 10;
            background: white;
            width: 35px;
            height: 35px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            color: #ff4757;
            border: none;
        }

        .empty-state {
            text-align: center;
            padding: 100px 0;
        }

        .empty-state i {
            font-size: 80px;
            color: #dee2e6;
            margin-bottom: 20px;
        }

    </style>
</head>

<body>

<nav class="navbar sticky-top">

    <div class="container">

        <a class="navbar-brand" href="index.php">
            <span>KAMPU<span style="color: #2ecc71;">$</span>HOP</span>
        </a>

        <div class="ms-auto d-flex align-items-center">

            <a href="profil.php"
               class="text-white text-decoration-none fw-bold me-3">

                <i class="fa-solid fa-circle-user me-1"></i>
                Profilim

            </a>

            <a href="index.php"
               class="btn btn-outline-light btn-sm px-4 rounded-pill">

               Vitrini Gez

            </a>

        </div>

    </div>

</nav>

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">

        <h4 class="fw-bold mb-0">
            <i class="fa-solid fa-heart text-danger me-2"></i>
            Favori İlanlarım
        </h4>

        <span class="badge bg-primary rounded-pill">
            <?php echo count($favori_ilanlar); ?> İlan
        </span>

    </div>

    <div class="row">

        <?php if(count($favori_ilanlar) > 0): ?>

            <?php foreach($favori_ilanlar as $row): ?>

                <div class="col-md-3 mb-4">

                    <div class="card h-100 shadow-sm card-ilan">

                        <!-- FAVORİDEN ÇIKAR -->
                        <button
                            class="fav-remove-btn shadow-sm remove-favorite"
                            data-id="<?php echo $row['id']; ?>"
                            type="button"
                        >
                            <i class="fa-solid fa-heart"></i>
                        </button>

                        <div class="card-body d-flex flex-column p-4">

                            <h6 class="fw-bold text-dark">
                                <?php echo htmlspecialchars($row['baslik']); ?>
                            </h6>

                            <p class="text-muted small">

                                <?php
                                echo mb_strimwidth(
                                    htmlspecialchars($row['aciklama'] ?? ''),
                                    0,
                                    80,
                                    "..."
                                );
                                ?>

                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-auto border-top pt-3">

                                <b class="text-primary fs-5">

                                    <?php
                                    echo number_format($row['fiyat'] ?? 0, 0, ',', '.');
                                    ?>

                                    TL

                                </b>

                                <a href="listing-details.php?id=<?php echo $row['id']; ?>"
                                   class="btn btn-sm btn-light border px-3 rounded-pill">

                                   Detay

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="empty-state">

                <i class="fa-regular fa-heart"></i>

                <h3>Henüz favori ilanınız yok.</h3>

                <p class="text-muted">
                    İlgini çeken ilanları kalbe basarak buraya ekleyebilirsin.
                </p>

                <a href="index.php"
                   class="btn btn-primary px-4 rounded-pill mt-3">

                   İlanları Keşfet

                </a>

            </div>

        <?php endif; ?>

    </div>

</div>

<!-- AJAX FAVORİ SİL -->
<script>

document.querySelectorAll('.remove-favorite').forEach(button => {

    button.addEventListener('click', function () {

        const btn = this;
        const ilanId = btn.dataset.id;

        fetch('toggle-favorite.php?id=' + ilanId)

        .then(response => response.text())

        .then(data => {

            if (data.trim() === "removed") {

                // Kartı kaldır
                btn.closest('.col-md-3').remove();

            }

        })

        .catch(error => {

            console.log(error);
            alert("Bir hata oluştu.");

        });

    });

});

</script>

</body>
</html>