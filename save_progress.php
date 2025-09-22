<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_POST['content_id']) || !isset($_POST['progress'])) {
    exit;
}
include 'db.php';
 
$user_id = $_SESSION['user_id'];
$content_id = $_POST['content_id'];
$progress = $_POST['progress'];
 
$stmt = $pdo->prepare("UPDATE watch_history SET progress = ? WHERE user_id = ? AND content_id = ?");
$stmt->execute([$progress, $user_id, $content_id]);
?>
