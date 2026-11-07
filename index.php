<?php
require_once "config.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $uname = trim($_POST['uname']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role, status FROM users WHERE username = ?");
    $stmt->bind_param('s', $uname);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    // Direct text verification matching simulated root user setup
    if ($user && $password === $user['password']) {
        if ($user['status'] !== "approved") {
            $errors[] = "Your account registration is pending admin approval.";
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            header('Location: dashboard.php');
            exit;
        }
    } else {
        $errors[] = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Bartolay Mart Inventory</title>
    <link rel="stylesheet" href="style.css">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="brand-header">
                <i data-lucide="boxes"></i>
                <h1>Bartolay Mart</h1>
                <p>Inventory Management System</p>
            </div>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <span><?= h($e) ?></span>
                </div>
            <?php endforeach; ?>

            <form action="index.php" method="post">
                <div class="form-group">
                    <label for="uname">Username</label>
                    <div class="input-icon-wrapper">
                        <i data-lucide="user"></i>
                        <input type="text" name="uname" id="uname" class="form-control" placeholder="Enter username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon-wrapper">
                        <i data-lucide="lock"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>No account yet? <a href="register.php">Create Account</a></p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>