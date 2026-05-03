<?php
include "config.php";

$result = $conn->query("SELECT * FROM listings ORDER BY id DESC");

while ($row = $result->fetch_assoc()) {
?>

<div style="border:1px solid #ccc; padding:10px; margin:10px;">

    <h3><?php echo $row['title']; ?></h3>

    <img src="uploads/<?php echo $row['image']; ?>" width="200"><br><br>

    <p><?php echo $row['description']; ?></p>

    <b><?php echo $row['price']; ?> TL</b>

</div>

<?php } ?>