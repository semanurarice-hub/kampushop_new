<?php
$conn = new mysqli("localhost", "root", "", "kampushop");

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

echo "BAĞLANTI TAMAM";
?>