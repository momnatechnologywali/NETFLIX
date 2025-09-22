<?php
session_start();
include 'db.php';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
 
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
 
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        echo "<script>window.location.href = 'index.php';</script>";
    } else {
        echo "<script>alert('Invalid credentials');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        body { background-color: #141414; color: #fff; font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        form { background: #222; padding: 20px; border-radius: 5px; width: 300px; }
        input { display: block; width: 100%; margin: 10px 0; padding: 10px; background: #333; color: #fff; border: none; }
        button { background: #e50914; color: #fff; border: none; padding: 10px; width: 100%; cursor: pointer; }
        @media (max-width: 768px) { form { width: 90%; } }
    </style>
</head>
<body>
    <form method="POST">
        <h2>Login</h2>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>
</body>
</html>
