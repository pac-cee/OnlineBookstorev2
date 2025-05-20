<?php
// File: admin/view_users.php

require_once __DIR__ . '/../includes/functions_admin.php';
redirectIfNotAdmin();

$message = '';
$error   = '';

// 1) Handle “Create Admin” form submission
if (isset($_POST['create_admin'])) {
    $username    = trim($_POST['username']);
    $email       = trim($_POST['email']);
    $password    = $_POST['password'];
    $confirmPass = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password) || empty($confirmPass)) {
        $error = 'All fields are required.';
    } elseif ($password !== $confirmPass) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email or username already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param('ss', $email, $username);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $error = 'Username or email already in use.';
        } else {
            $hash = hashPassword($password);
            $stmt2 = $conn->prepare("
              INSERT INTO users (name, username, password, email, role)
              VALUES (?, ?, ?, ?, 'admin')
            ");
            // We'll set name = username for simplicity
            $stmt2->bind_param('ssss', $username, $username, $hash, $email);
            if ($stmt2->execute()) {
                $message = 'New admin account created.';
            } else {
                $error = 'Error creating admin: ' . $conn->error;
            }
        }
    }
}

// 2) Handle Promote / Demote via GET param ?toggle_role=USER_ID
if (isset($_GET['toggle_role'])) {
    $uid = intval($_GET['toggle_role']);
    // Fetch current role
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $uid);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $row = $res->fetch_assoc();
        $newRole = ($row['role'] === 'admin') ? 'individual' : 'admin';
        $stmt2 = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt2->bind_param('si', $newRole, $uid);
        $stmt2->execute();
    }
    header('Location: view_users.php');
    exit;
}

// 3) Handle Delete user via GET param ?delete_id=USER_ID
if (isset($_GET['delete_id'])) {
    $did = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param('i', $did);
    $stmt->execute();
    header('Location: view_users.php');
    exit;
}

// 4) Fetch all users (with optional search)
$search = '';
$whereClause = '';
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string(trim($_GET['search']));
    $whereClause = "WHERE username LIKE '%$search%' OR email LIKE '%$search%'";
}
$sql = "
  SELECT id, username, email, role, created_at
  FROM users
  $whereClause
  ORDER BY created_at DESC
";
$resUsers = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Users • Admin</title>
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
  <?php include __DIR__ . '/../views/includes/header.php'; ?>

  <div class="admin-container">
    <h1 class="admin-title">All Users</h1>

    <?php if ($message): ?>
      <div class="alert-admin alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
      <div class="alert-admin alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="GET" action="view_users.php" style="margin-bottom: 1rem;">
      <input type="text" name="search" placeholder="Search by username or email" 
             value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn-admin">Search</button>
      <button type="button" class="btn-admin" onclick="document.getElementById('createAdminModal').style.display='flex';">
        Create Admin
      </button>
    </form>

    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Username</th>
          <th>Email</th>
          <th>Role</th>
          <th>Created At</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resUsers->num_rows): ?>
          <?php $i = 1; while ($user = $resUsers->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($user['username']) ?></td>
              <td><?= htmlspecialchars($user['email']) ?></td>
              <td><?= htmlspecialchars($user['role']) ?></td>
              <td><?= htmlspecialchars(date('Y-m-d', strtotime($user['created_at']))) ?></td>
              <td>
                <a href="view_users.php?toggle_role=<?= $user['id'] ?>" class="btn-admin">
                  <?= $user['role'] === 'admin' ? 'Demote to Individual' : 'Promote to Admin' ?>
                </a>
                <a href="view_users.php?delete_id=<?= $user['id'] ?>" class="btn-admin" 
                   onclick="return confirm('Delete this user?');">
                  Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6">No users found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Create Admin Modal -->
  <div id="createAdminModal" class="modal-overlay" onclick="this.style.display='none';">
    <div class="modal-content fade-in" onclick="event.stopPropagation();">
      <span class="modal-close" onclick="document.getElementById('createAdminModal').style.display='none';">
        &times;
      </span>
      <h2>Create New Admin</h2>
      <form action="view_users.php" method="POST" class="form-admin">
        <input type="hidden" name="create_admin" value="1">
        <label for="username">Username*</label>
        <input type="text" name="username" id="username" required>

        <label for="email">Email*</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Password*</label>
        <input type="password" name="password" id="password" required>

        <label for="confirm_password">Confirm Password*</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <button type="submit" class="btn-admin">Create Admin</button>
      </form>
    </div>
  </div>

  <?php include __DIR__ . '/../views/includes/footer.php'; ?>

  <script>
    // Close modal when pressing Escape
    document.addEventListener('keydown', function(e) {
      if (e.key === 'Escape') {
        document.getElementById('createAdminModal').style.display = 'none';
      }
    });
  </script>
</body>
</html>
