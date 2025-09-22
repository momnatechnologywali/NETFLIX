<?php
session_start();
include 'db.php';
 
$results = [];
$genres = $pdo->query("SELECT * FROM genres")->fetchAll();
 
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $query = isset($_GET['query']) ? $_GET['query'] : '';
    $genre = isset($_GET['genre']) ? $_GET['genre'] : '';
 
    $sql = "SELECT DISTINCT c.* FROM content c 
            LEFT JOIN content_genres cg ON c.id = cg.content_id 
            LEFT JOIN genres g ON cg.genre_id = g.id 
            WHERE 1=1";
    $params = [];
 
    if ($query) {
        $sql .= " AND (c.title LIKE ? OR c.actors LIKE ?)";
        $params[] = "%$query%";
        $params[] = "%$query%";
    }
    if ($genre) {
        $sql .= " AND g.id = ?";
        $params[] = $genre;
    }
 
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search</title>
    <style>
        body { background-color: #141414; color: #fff; font-family: Arial, sans-serif; margin: 0; padding: 20px; }
        form { margin-bottom: 20px; }
        input, select { padding: 10px; background: #333; color: #fff; border: none; }
        button { background: #e50914; color: #fff; border: none; padding: 10px; cursor: pointer; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; }
        .item { background: #222; padding: 10px; border-radius: 5px; }
        .item img { width: 100%; height: auto; }
        @media (max-width: 768px) { .grid { grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); } }
    </style>
</head>
<body>
    <h1>Search Content</h1>
    <form method="GET">
        <input type="text" name="query" placeholder="Search by title or actor">
        <select name="genre">
            <option value="">All Genres</option>
            <?php foreach ($genres as $g): ?>
                <option value="<?php echo $g['id']; ?>"><?php echo $g['name']; ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Search</button>
    </form>
    <div class="grid">
        <?php foreach ($results as $item): ?>
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
    <script>
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
