<?php
// File: admin/register_admin.php

require_once __DIR__ . '/../config/db.php';
session_start();

// Check if user is already logged in as admin
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    // Already logged in as admin, redirect to dashboard
    header('Location: dashboard.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

$message = '';
$messageType = '';

// Get schools for dropdown (if needed)
$schools = [];
$schoolsQuery = $conn->query("SELECT id, name FROM schools ORDER BY name");
if ($schoolsQuery) {
    while ($row = $schoolsQuery->fetch_assoc()) {
        $schools[$row['id']] = $row['name'];
    }
}

// Get memberships for dropdown (if needed)
$memberships = [];
$membershipsQuery = $conn->query("SELECT id, name FROM memberships ORDER BY name");
if ($membershipsQuery) {
    while ($row = $membershipsQuery->fetch_assoc()) {
        $memberships[$row['id']] = $row['name'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $schoolId = !empty($_POST['school_id']) ? (int)$_POST['school_id'] : null;
    $membershipId = !empty($_POST['membership_id']) ? (int)$_POST['membership_id'] : null;
    
    // Validation
    $errors = [];
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($username)) {
        $errors[] = "Username is required";
    } else {
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Username already exists";
        }
        $stmt->close();
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    } else {
        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already exists";
        }
        $stmt->close();
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }
    
    // If no errors, insert the new admin user
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $role = 'admin'; // Always set role to admin for this page
        
        $stmt = $conn->prepare("
            INSERT INTO users (name, username, email, password, role, school_id, membership_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param('sssssii', $name, $username, $email, $hashedPassword, $role, $schoolId, $membershipId);
        
        if ($stmt->execute()) {
            $messageType = 'success';
            $message = "Admin account created successfully! Redirecting to login page...";
            // Set a redirect with JavaScript after displaying the message
            header("refresh:3;url=admin_login.php");
        } else {
            $messageType = 'error';
            $message = "Error creating account: " . $conn->error;
        }
        
        $stmt->close();
    } else {
        $messageType = 'error';
        $message = implode("<br>", $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register Admin • OnlineBookstore</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    body {
      background: #f7f9fc;
      font-family: 'Poppins', sans-serif;
      margin: 0;
      padding: 2rem;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }
    .register-container {
      width: 100%;
      max-width: 500px;
      background: #fff;
      padding: 2rem;
      border-radius: 0.5rem;
      box-shadow: 0 4px 24px rgba(0,0,0,0.1);
    }
    .register-container h2 {
      margin-bottom: 1.5rem;
      color: #333;
      text-align: center;
      font-size: 1.5rem;
    }
    .register-container label {
      display: block;
      margin-bottom: 0.5rem;
      font-weight: 600;
      color: #555;
    }
    .register-container input,
    .register-container select {
      width: 100%;
      padding: 0.75rem;
      margin-bottom: 1rem;
      border: 1px solid #ddd;
      border-radius: 0.25rem;
      font-size: 1rem;
      color: #333;
    }
    .register-container button {
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
    .register-container button:hover {
      background: #0057dd;
    }
    .form-group {
      margin-bottom: 1rem;
    }
    .message {
      padding: 0.75rem;
      border-radius: 0.25rem;
      margin-bottom: 1rem;
      text-align: center;
    }
    .success-msg {
      background: #e5ffea;
      color: #00a32a;
    }
    .error-msg {
      background: #ffe5e5;
      color: #d8000c;
    }
    .login-link {
      text-align: center;
      margin-top: 1rem;
    }
    .login-link a {
      color: #0069ff;
      text-decoration: none;
    }
    .login-link a:hover {
      text-decoration: underline;
    }
    .optional-label {
      font-weight: normal;
      font-size: 0.8rem;
      color: #777;
      margin-left: 0.5rem;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <h2>Register Admin Account</h2>

    <?php if ($message): ?>
      <div class="message <?= $messageType === 'success' ? 'success-msg' : 'error-msg' ?>">
        <?= $message ?>
      </div>
    <?php endif; ?>

    <form action="register_admin.php" method="POST">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input
          type="text"
          name="name"
          id="name"
          required
          value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
        >
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input
          type="text"
          name="username"
          id="username"
          required
          value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
        >
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input
          type="email"
          name="email"
          id="email"
          required
          value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
        >
      </div>

      <div class="form-group">
        <label for="password">Password</label>
        <input
          type="password"
          name="password"
          id="password"
          required
          minlength="8"
        >
      </div>

      <div class="form-group">
        <label for="confirm_password">Confirm Password</label>
        <input
          type="password"
          name="confirm_password"
          id="confirm_password"
          required
          minlength="8"
        >
      </div>

      <?php if (!empty($schools)): ?>
      <div class="form-group">
        <label for="school_id">School <span class="optional-label">(Optional)</span></label>
        <select name="school_id" id="school_id">
          <option value="">-- Select School --</option>
          <?php foreach ($schools as $id => $schoolName): ?>
            <option value="<?= $id ?>" <?= (isset($_POST['school_id']) && $_POST['school_id'] == $id) ? 'selected' : '' ?>>
              <?= htmlspecialchars($schoolName) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <?php if (!empty($memberships)): ?>
      <div class="form-group">
        <label for="membership_id">Membership <span class="optional-label">(Optional)</span></label>
        <select name="membership_id" id="membership_id">
          <option value="">-- Select Membership --</option>
          <?php foreach ($memberships as $id => $membershipName): ?>
            <option value="<?= $id ?>" <?= (isset($_POST['membership_id']) && $_POST['membership_id'] == $id) ? 'selected' : '' ?>>
              <?= htmlspecialchars($membershipName) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <button type="submit">Register Admin</button>
      
      <div class="login-link">
        Already have an account? <a href="admin_login.php">Login</a>
      </div>
    </form>
  </div>
</body>
</html>