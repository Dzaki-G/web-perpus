<!-- auth/login.php -->
<?php
require_once '../config/db.php';
$pdo = getConnection();
require_once 'auth.php';

if (isLoggedIn()) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['username'] = $user['username'];
        
        header("Location: ../admin/dashboard.php");
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../assets/style/login.css">
    <script src="../assets/script/script.js"></script>   
    <title>Login - Sistem Perpustakaan Bappeda</title>
</head>
<body>
    <a href="../index.php" class="home-icon">
        <i class="bi bi-house-door-fill"></i>
    </a>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="banner-section">
                    <div class="banner-placeholder"></div>
                    <div class="header-content">
                        <h2>Sistem Perpustakaan Bappeda</h2>
                        <p>Silahkan Login</p>
                    </div>
                </div>
                <div class="logo-section">
                    <div class="logo-circle">
                        <img src="/assets/img/Lampung_coa.png" alt="Logo Bappeda" style="width: 70%; height: 70%; object-fit: contain;">
                    </div>
                </div>
            </div>
            
            <div class="login-form-section">
                <form action="" method="POST" class="login-form">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Masukan Username" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Masukan Password" required>
                    </div>
                    
                    <button type="submit" class="login-btn">Masuk</button>
                    
                    <div class="forgot-password">
                        <i class="fas fa-lock"></i>
                        <a href="https://wa.me/6282269522432" class="forgot-link" target="_blank" rel="noopener noreferrer">Lupa Password?</a>
                    </div>
                    <?php if (isset($error)) echo "<p class='text-danger mt-2'>$error</p>"; ?>
                </form>
            </div>
        </div>
    </div>

</body>
</html>