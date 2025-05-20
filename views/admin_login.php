<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username && $password) {
        $db = new Database();
        $conn = $db->getConnection();
        
        $sql = "SELECT id, username, role, status FROM users 
                WHERE username = ? AND password = SHA2(?, 256) AND role = 'admin'";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            if ($row['status'] === 'inactive') {
                $message = 'Account is inactive. Please contact support.';
            } else {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                
                header('Location: /views/admin/dashboard.php');
                exit;
            }
        } else {
            $message = 'Invalid credentials or insufficient privileges.';
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $message = 'Please enter both username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Online Bookstore</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/auth.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="auth-page">
    <main class="auth-container">
        <h1><i class="fas fa-user-shield"></i> Admin Login</h1>
        
        <?php if ($message): ?>
            <div class="auth-message error">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="auth-button">
                Login <i class="fas fa-arrow-right"></i>
            </button>
        </form>
        
        <div class="auth-links">
            <a href="/index.php">&larr; Back to Homepage</a>
        </div>
    </main>
</body>
</html>