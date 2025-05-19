<?php
require_once __DIR__ . '/../config/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';
    if ($username && $email && $password && $password === $confirm) {
        $db = new Database();
        $conn = $db->getConnection();
        // Check if username exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $message = 'Username already exists.';
        } else {
            // Check if email exists
            $stmt->close();
            $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows > 0) {
                $message = 'Email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt->close();
                $stmt = $conn->prepare('INSERT INTO users (username, email, password) VALUES (?, ?, ?)');
                $stmt->bind_param('sss', $username, $email, $hash);
                if ($stmt->execute()) {
                    $message = 'Registration successful! <a href="login.php">Login here</a>.';
                } else {
                    $message = 'Registration failed. Please try again.';
                }
            }
        }
        $stmt->close();
        $conn->close();
    } else {
        $message = 'Please fill all fields and ensure passwords match.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - OnlineBookstore</title>
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f4da.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/auth.css">
    <script src="../assets/js/auth.js" defer></script>
</head>
<body class="auth-page">
    <?php include 'includes/header.php'; ?>
    
    <main class="auth-container">
        <h1><i class="fas fa-user-plus"></i> Create Account</h1>
        
        <?php if ($message): ?>
            <div class="auth-message <?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form" id="registerForm">
            <div class="form-group">
                <label for="username">Username</label>
                <div class="input-group">
                    <input type="text" id="username" name="username" required>
                    <i class="fas fa-user icon"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email</label>
                <div class="input-group">
                    <input type="email" id="email" name="email" required>
                    <i class="fas fa-envelope icon"></i>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-input-group">
                    <input type="password" id="password" name="password" required>
                    <i class="fas fa-lock icon"></i>
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirm">Confirm Password</label>
                <div class="password-input-group">
                    <input type="password" id="confirm" name="confirm" required>
                    <i class="fas fa-lock icon"></i>
                    <button type="button" class="password-toggle" aria-label="Toggle password visibility">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="auth-button">
                Register <i class="fas fa-arrow-right"></i>
            </button>
        </form>
        
        <div class="auth-links">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>

