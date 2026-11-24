<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $pdo = db();
    $stmt = $pdo->prepare('DELETE FROM rumah WHERE id = ?');
    $stmt->execute(array((int) $_POST['id']));
}
header('Location: index.php?pesan=hapus');
exit;
