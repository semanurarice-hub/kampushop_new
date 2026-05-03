<?php
include "config.php";

$id = $_GET['id'];

$result = $conn->query("SELECT * FROM listings WHERE id=$id");
$listing = $result->fetch_assoc();
?>

<h2><?php echo $listing['title']; ?></h2>

<img src="uploads/<?php echo $listing['image']; ?>" width="400"><br><br>

<p><?php echo $listing['description']; ?></p>

<h3><?php echo $listing['price']; ?> TL</h3>