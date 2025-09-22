<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['content_id'])) {
    echo 'Error: Not logged in or invalid request';
    exit;
}
include 'db.php';
 
$user_id = $_SESSION['user_id'];
$content_id = $_POST['content_id'];
 
try {
    $stmt = $pdo->prepare("INSERT IGNORE INTO watchlist (user_id, content_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $content_id]);
    echo 'Added to watchlist!';
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}
?>
