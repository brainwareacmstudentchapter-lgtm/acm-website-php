<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// If already logged in, redirect to dashboard
if (isLoggedIn()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    
    if (loginUser($username, $password)) {
        redirect('dashboard.php');
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ACM VIT Chennai</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-login-page">
    <div class="admin-login-container">
        <div class="login-brand">
            <div class="brand-icon">ACM</div>
            <h1>Admin Portal</h1>
            <p>ACM Student Chapter - VIT Chennai</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" class="login-form">
            <div class="form-input">
                <i class="fas fa-user input-icon"></i>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            
            <div class="form-input">
                <i class="fas fa-lock input-icon"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" class="login-btn">Login</button>
        </form>
        
        <p class="login-footer">Default: admin / Admin@123</p>
    </div>
</body>
</html>
