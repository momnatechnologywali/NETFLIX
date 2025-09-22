<?php
session_start();
if (!isset($_GET['id'])) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit;
}
include 'db.php';
 
$content_id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM content WHERE id = ?");
$stmt->execute([$content_id]);
$content = $stmt->fetch();
 
if (!$content) {
    echo "<script>alert('Content not found'); window.location.href = 'index.php';</script>";
    exit;
}
 
$progress = 0;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT progress FROM watch_history WHERE user_id = ? AND content_id = ?");
    $stmt->execute([$user_id, $content_id]);
    $history = $stmt->fetch();
    if ($history) {
        $progress = $history['progress'];
    } else {
        // Insert into history
        $stmt = $pdo->prepare("INSERT INTO watch_history (user_id, content_id, progress) VALUES (?, ?, 0)");
        $stmt->execute([$user_id, $content_id]);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Watching <?php echo $content['title']; ?></title>
    <style>
        body { background-color: #000; color: #fff; font-family: Arial, sans-serif; margin: 0; padding: 0; }
        video { width: 100%; height: auto; }
        .controls { padding: 20px; }
        @media (max-width: 768px) { video { height: 50vh; } }
    </style>
</head>
<body>
    <video controls autoplay>
        <source src="<?php echo $content['video_url']; ?>" type="video/mp4">
        Your browser does not support the video tag.
    </video>
    <div class="controls">
        <h1><?php echo $content['title']; ?></h1>
        <p><?php echo $content['description']; ?></p>
    </div>
    <script>
        const video = document.querySelector('video');
        let lastSave = 0;
        video.currentTime = <?php echo $progress; ?>;
 
        video.addEventListener('timeupdate', () => {
            const current = Math.floor(video.currentTime);
            if (Math.abs(current - lastSave) >= 10) { // Save every 10 seconds
                lastSave = current;
                fetch('save_progress.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'content_id=<?php echo $content_id; ?>&progress=' + current
                });
            }
        });
 
        video.addEventListener('ended', () => {
            fetch('save_progress.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'content_id=<?php echo $content_id; ?>&progress=0' // Reset on end
            });
        });
    </script>
</body>
</html>
