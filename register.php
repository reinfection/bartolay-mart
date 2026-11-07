<?php
require_once "config.php";

$mess = [];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $uname = trim($_POST['uname']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];

    if ($password !== $password2) {
        $errors[] = "Passwords do not match.";
    } else {
        // Check duplicate user
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param('s', $uname);
        $check->execute();
        $check_res = $check->get_result();

        if ($check_res->num_rows > 0) {
            $errors[] = "Username is already taken.";
        } else {
            // New user defaults to 'staff' and 'pending' status
            $stmt = $conn->prepare("INSERT INTO users (username, password, role, status) VALUES (?, ?, 'staff', 'pending')");
            $stmt->bind_param('ss', $uname, $password);

            if ($stmt->execute()) {
                $mess[] = "Registration submitted! Please wait for admin approval.";
            } else {
                $errors[] = "System error occurred. Please try again.";
            }
            $stmt->close();
        }
        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bartolay Mart Inventory</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="brand-header">
                <i data-lucide="user-plus"></i>
                <h1>Create Account</h1>
                <p>Join Bartolay Mart Inventory System</p>
            </div>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px;"></i>
                    <span><?= h($e) ?></span>
                </div>
            <?php endforeach; ?>

            <?php foreach ($mess as $m): ?>
                <div class="alert alert-success">
                    <i data-lucide="check-circle" style="width: 18px; height: 18px;"></i>
                    <span><?= h($m) ?></span>
                </div>
            <?php endforeach; ?>

            <form action="register.php" method="post">
                <div class="form-group">
                    <label for="uname">Username</label>
                    <div class="input-icon-wrapper">
                        <i data-lucide="user"></i>
                        <input type="text" name="uname" id="uname" class="form-control" placeholder="Choose a username" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-icon-wrapper">
                        <i data-lucide="lock"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Create password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password2">Confirm Password</label>
                    <div class="input-icon-wrapper">
                        <i data-lucide="shield-check"></i>
                        <input type="password" name="password2" id="password2" class="form-control" placeholder="Re-enter password" required>
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                    <span>Register</span>
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="index.php">Sign In</a></p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>