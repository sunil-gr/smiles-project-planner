<?php
// Start session before anything
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in (before any HTML or includes!)
if (isset($_SESSION['user'])) {
    header('Location: upload.php');
    exit;
}

// Handle login POST
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($username === "admin" && $password === "admin123") {
        $_SESSION['user'] = $username;
        header('Location: upload.php');
        exit;
    } else {
        $error = 'Invalid username or password.';
    }
}

// Now it's safe to include files that produce output
include 'header.php';
?>
<link rel="stylesheet" href="style.css">
<div class="login-bg">
    <form class="login-card" method="post">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="login-form">
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" autocomplete="username" required>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" autocomplete="current-password" required>
            <button type="submit">Login</button>
        </div>
    </form>
</div>
<?php include 'footer.php'; ?>
