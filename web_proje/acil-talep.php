<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: auth-login.php");
    exit();
}
?>

<h2>Acil Talep Gönder</h2>

<form action="talep-ekle.php" method="POST">

    <input type="text" name="baslik" placeholder="Başlık" required>
    <br><br>

    <textarea name="aciklama" placeholder="Açıklama" required></textarea>
    <br><br>

    <select name="oncelik">
        <option value="normal">Normal</option>
        <option value="acil">Acil</option>
    </select>

    <br><br>

    <button type="submit">Gönder</button>

</form>