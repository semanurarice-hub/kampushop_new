<?php
try {
    $conn = new PDO(
        "mysql:host=localhost;dbname=kampushop;charset=utf8",
        "root",
        ""
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("DB Hatası: " . $e->getMessage());
}
?>

