<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $db   = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare('
            SELECT id, password, role 
            FROM users 
            WHERE username = ? 
            LIMIT 1
        ');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 1) {
            $stmt->bind_result($id, $hash, $role);
            $stmt->fetch();

            if (password_verify($password, $hash)) {
                $_SESSION['user_id']  = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role']     = $role;

                // Redirect based on role
                if ($role === 'admin') {
                    header('Location: ../admin/dashboard.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit;
            } else {
                $message = 'Incorrect password.';
            }
        } else {
            $message = 'User not found.';
        }
        $stmt->close();
        $conn->close();
    } else {
        $message = 'Please fill all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login – OnlineBookstore</title>
  <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f4da.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/styles.css">
  <link rel="stylesheet" href="../assets/css/auth.css">
  <script src="../assets/js/auth.js" defer></script>
</head>
<body class="auth-page">
  <?php include __DIR__ . '/../views/includes/header.php'; ?>

  <main class="auth-container">
    <h1><i class="fas fa-book-reader"></i> Welcome Back</h1>

    <?php if ($message): ?>
      <div class="auth-message error">
        <?= htmlspecialchars($message) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="login.php" class="auth-form" id="loginForm">
      <div class="form-group">
        <label for="username">Username</label>
        <input
          type="text"
          id="username"
          name="username"
          required
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
        >
        <i class="fas fa-user icon"></i>
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-input-group">
          <input type="password" id="password" name="password" required>
          <i class="fas fa-lock icon"></i>
        </div>
      </div>

      <button type="submit" class="auth-button">
        Login <i class="fas fa-arrow-right"></i>
      </button>
    </form>

    <div class="auth-links">
      <p>Don't have an account? <a href="register.php">Register here</a></p>
      <p><a href="forgot-password.php"><i class="fas fa-key"></i> Forgot Password?</a></p>
    </div>
  </main>

  <?php include __DIR__ . '/../views/includes/footer.php'; ?>
</body>
</html>
