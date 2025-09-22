<?php
$host = 'localhost'; // Change if your DB host is different (e.g., for cloud DB)
$dbname = 'dbmc0vujd3ogpm';
$username = 'uws1gwyttyg2r';
$password = 'k1tdlhq4qpsf';
 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
