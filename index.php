<?php
session_start();
include 'db.php';
 
// Fetch featured content (e.g., latest or random)
$stmt = $pdo->query("SELECT * FROM content ORDER BY created_at DESC LIMIT 5");
$featured = $stmt->fetchAll();
 
// Fetch trending (e.g., based on watch history counts)
$stmt = $pdo->query("SELECT c.*, COUNT(wh.id) AS views FROM content c LEFT JOIN watch_history wh ON c.id = wh.content_id GROUP BY c.id ORDER BY views DESC LIMIT 5");
$trending = $stmt->fetchAll();
 
// Recommendations if logged in
$recommendations = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    // Get user's watched genres
    $stmt = $pdo->prepare("SELECT DISTINCT g.id FROM watch_history wh 
                           JOIN content_genres cg ON wh.content_id = cg.content_id 
                           JOIN genres g ON cg.genre_id = g.id 
                           WHERE wh.user_id = ?");
    $stmt->execute([$user_id]);
    $user_genres = $stmt->fetchAll(PDO::FETCH_COLUMN);
 
    if (!empty($user_genres)) {
        $genre_placeholders = implode(',', array_fill(0, count($user_genres), '?'));
        $stmt = $pdo->prepare("SELECT DISTINCT c.* FROM content c 
                               JOIN content_genres cg ON c.id = cg.content_id 
                               WHERE cg.genre_id IN ($genre_placeholders) 
                               AND c.id NOT IN (SELECT content_id FROM watch_history WHERE user_id = ?)
                               LIMIT 5");
        $params = array_merge($user_genres, [$user_id]);
        $stmt->execute($params);
        $recommendations = $stmt->fetchAll();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Netflix Clone - Homepage</title>
    <style>
        /* Amazing, real-looking CSS - Dark theme like Netflix, responsive */
        body { background-color: #141414; color: #fff; font-family: Arial, sans-serif; margin: 0; padding: 0; }
        header { background: #000; padding: 10px; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; }
        nav a { color: #fff; margin: 0 10px; text-decoration: none; }
        .carousel { position: relative; overflow: hidden; width: 100%; height: 400px; }
        .carousel img { width: 100%; height: 100%; object-fit: cover; position: absolute; top: 0; left: 0; opacity: 0; transition: opacity 1s; }
        .carousel img.active { opacity: 1; }
        .section { padding: 20px; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
        .item { background: #222; padding: 10px; border-radius: 5px; }
        .item img { width: 100%; height: auto; }
        .item button { background: #e50914; color: #fff; border: none; padding: 5px; cursor: pointer; }
        @media (max-width: 768px) { .carousel { height: 200px; } .grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); } }
    </style>
</head>
<body>
    <header>
        <h1>Netflix Clone</h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Profile</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="signup.php">Signup</a>
            <?php endif; ?>
            <a href="search.php">Search</a>
        </nav>
    </header>
    <section class="carousel">
        <?php foreach ($featured as $index => $item): ?>
            <img src="<?php echo $item['thumbnail_url']; ?>" alt="<?php echo $item['title']; ?>" class="<?php echo $index === 0 ? 'active' : ''; ?>">
        <?php endforeach; ?>
    </section>
    <section class="section">
        <h2>Trending</h2>
        <div class="grid">
            <?php foreach ($trending as $item): ?>
                <div class="item">
                    <img src="<?php echo $item['thumbnail_url']; ?>" alt="<?php echo $item['title']; ?>">
                    <h3><?php echo $item['title']; ?></h3>
                    <a href="watch.php?id=<?php echo $item['id']; ?>"><button>Watch</button></a>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button onclick="addToWatchlist(<?php echo $item['id']; ?>)">Add to Watchlist</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
    <?php if (!empty($recommendations)): ?>
        <section class="section">
            <h2>Recommendations</h2>
            <div class="grid">
                <?php foreach ($recommendations as $item): ?>
                    <div class="item">
                        <img src="<?php echo $item['thumbnail_url']; ?>" alt="<?php echo $item['title']; ?>">
                        <h3><?php echo $item['title']; ?></h3>
                        <a href="watch.php?id=<?php echo $item['id']; ?>"><button>Watch</button></a>
                        <button onclick="addToWatchlist(<?php echo $item['id']; ?>)">Add to Watchlist</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <script>
        // Carousel JS
        let current = 0;
        const images = document.querySelectorAll('.carousel img');
        setInterval(() => {
            images[current].classList.remove('active');
            current = (current + 1) % images.length;
            images[current].classList.add('active');
        }, 5000);
 
        // Add to watchlist AJAX
        function addToWatchlist(contentId) {
            fetch('add_to_watchlist.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'content_id=' + contentId
            }).then(response => response.text()).then(data => {
                alert(data);
            });
        }
    </script>
</body>
</html>
