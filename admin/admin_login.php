<?php
// File: admin/admin_login.php

require_once __DIR__ . '/../config/db.php';
session_start();

// If user is already logged in as admin, redirect to dashboard
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    header('Location: dashboard.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$error = '';
$debug = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        // Prepare & execute lookup
        $stmt = $conn->prepare("
            SELECT id, username, password, role
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        // Check if user exists
        if ($stmt->num_rows === 1) {
            // Bind the columns
            $stmt->bind_result($id, $username, $hash, $role);
            $stmt->fetch();

            // Check role & password
            if ($role === 'admin' && password_verify($password, $hash)) {
                // Login successful
                $_SESSION['user_id'] = $id;
                $_SESSION['username'] = $username;
                $_SESSION['role'] = $role;
                
                // Redirect to dashboard
                header('Location: dashboard.php');
                exit;
            } else {
                // Login failed - provide appropriate error
                if ($role !== 'admin') {
                    $error = 'This account does not have admin privileges.';
                } else {
                    $error = 'Invalid password.';
                }
            }
        } else {
            $error = 'No account found with that email.';
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login • OnlineBookstore</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* Centered login box */
    body {
      background: #f7f9fc;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 100vh;
    }
    .login-container {
      width: 100%;
      max-width: 400px;
      background: #fff;
      padding: 2rem;
      border-radius: 0.5rem;
      box-shadow: 0 4px 24px rgba(0,0,0,0.1);
    }
    .login-container h2 {
      margin-bottom: 1.5rem;
      color: #333;
      text-align: center;
      font-size: 1.5rem;
    }
    .login-container label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #555;
    }
    .login-container input {
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 1rem;
      border: 1px solid #ddd;
      border-radius: 0.25rem;
      font-size: 1rem;
      color: #333;
    }
    .login-container button {
      width: 100%;
      padding: 0.75rem;
      background: #0069ff;
      color: white;
      font-size: 1rem;
      font-weight: 600;
      border: none;
      border-radius: 0.25rem;
      cursor: pointer;
      transition: background 0.2s ease;
    }
    .login-container button:hover {
      background: #0057dd;
    }
    .error-msg {
      background: #ffe5e5;
      color: #d8000c;
      padding: 0.75rem;
      border-radius: 0.25rem;
      margin-bottom: 1rem;
      text-align: center;
    }
    .register-link {
      text-align: center;
      margin-top: 1rem;
      font-size: 0.9rem;
    }
    .register-link a {
      color: #0069ff;
      text-decoration: none;
    }
    .register-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="login-container">
    <h2>Admin Login</h2>

    <?php if ($error): ?>
      <div class="error-msg"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="admin_login.php" method="POST">
      <label for="email">Email</label>
      <input
        type="email"
        name="email"
        id="email"
        required
        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
        autocomplete="email"
      >

      <label for="password">Password</label>
      <input
        type="password"
        name="password"
        id="password"
        required
        autocomplete="current-password"
      >

      <button type="submit">Log In</button>
    </form>
    
    <div class="register-link">
      Need an admin account? <a href="register_admin.php">Register here</a>
    </div>
    <div class="home-link" style="text-align:center; margin-bottom:1rem;">
  <a href="/index.php" class="btn-home">
    <i class="fas fa-home"></i> Home
  </a>
</div>
    <div class="admin-login-link" style="text-align:center;">
      <p>Are you a user? <a href="../views/login.php">Login here</a></p>
    </div>
  </div>
</body>
</html>