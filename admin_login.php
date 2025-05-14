<?php
session_start();
require_once 'util.php';

$util = new Util();
$pdo = $util->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin'] = $admin['email'];
        header("Location: agent_management.php");
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-5">
    <h2>Admin Login</h2>
    <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST" class="w-50">
        <div class="mb-3">
            <input class="form-control" type="email" name="email" placeholder="Email" required>
        </div>
        <div class="mb-3">
            <input class="form-control" type="password" name="password" placeholder="Password" required>
        </div>
        <button class="btn btn-primary" type="submit">Login</button>
    </form>
</body>
</html>
