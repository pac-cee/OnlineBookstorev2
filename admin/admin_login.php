<?php
// File: admin/admin_login.php

require_once __DIR__ . '/../config/db.php'; 
session_start();

// Instantiate Database to get $conn
$db   = new Database();
$conn = $db->getConnection();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $stmt = $conn->prepare("
            SELECT id, username, password, role
            FROM users
            WHERE email = ?
            LIMIT 1
        ");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows === 1) {
            $user = $res->fetch_assoc();

            // Check role = 'admin'
            if ($user['role'] === 'admin' && password_verify($password, $user['password'])) {
                // Set session and redirect
                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role']     = $user['role'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Invalid credentials or not an admin.';
            }
        } else {
            $error = 'No admin account found with that email.';
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
    <link rel="stylesheet" href="/css/index.css">
    <link rel="stylesheet" href="/css/dashboard.css">
    <style>
      /* Centered login box */
      .login-container {
        width: 100%;
        max-width: 400px;
        margin: 5rem auto;
        background: #fff;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      }
      .login-container h2 {
        margin-bottom: 1rem;
        color: #343a40;
        text-align: center;
      }
      .login-container label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
      }
      .login-container input {
        width: 100%;
        padding: 0.5rem;
        margin-bottom: 1rem;
        border: 1px solid #ced4da;
        border-radius: 0.3rem;
      }
      .login-container button {
        width: 100%;
        padding: 0.5rem;
        background: #007bff;
        color: #fff;
        border: none;
        border-radius: 0.3rem;
        cursor: pointer;
      }
      .error-msg {
        background: #f8d7da;
        color: #721c24;
        padding: 0.75rem;
        border-radius: 0.3rem;
        margin-bottom: 1rem;
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
      <input type="email" name="email" id="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

      <label for="password">Password</label>
      <input type="password" name="password" id="password" required>

      <button type="submit">Log In</button>
    </form>
  </div>
</body>
</html>
