<?php
include '../config.php';
include 'includes/auth.php';
include 'includes/header.php';

$query = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<h3>Kullanıcılar</h3>

<table border="1" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Ad Soyad</th>
        <th>Email</th>
        <th>Rol</th>
    </tr>

    <?php while($row = $query->fetch(PDO::FETCH_ASSOC)): ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo $row['ad_soyad']; ?></td>
        <td><?php echo $row['eposta']; ?></td>
        <td><?php echo $row['rol']; ?></td>
    </tr>
    <?php endwhile; ?>

</table>

<?php include 'includes/footer.php'; ?>