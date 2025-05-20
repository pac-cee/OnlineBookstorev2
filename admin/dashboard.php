<?php
// File: admin/dashboard.php

require_once __DIR__ . '/../includes/functions_admin.php';
redirectIfNotAdmin();

$stats         = getStats();
$lowStockRes   = getLowStockBooks();      // threshold = 5 by default
$pendingOrders = getPendingOrders();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard • OnlineBookstore</title>
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <div class="admin-container">
    <h1 class="admin-title">Admin Dashboard</h1>

    <!-- KPI Cards -->
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
      <div class="admin-card" style="flex: 1 1 200px;">
        <h3>Total Books</h3>
        <p style="font-size: 2rem;"><?= intval($stats['total_books']) ?></p>
      </div>
      <div class="admin-card" style="flex: 1 1 200px;">
        <h3>Total Users</h3>
        <p style="font-size: 2rem;"><?= intval($stats['total_users']) ?></p>
      </div>
      <div class="admin-card" style="flex: 1 1 200px;">
        <h3>Total Orders</h3>
        <p style="font-size: 2rem;"><?= intval($stats['total_orders']) ?></p>
      </div>
      <div class="admin-card" style="flex: 1 1 200px;">
        <h3>Total Revenue</h3>
        <p style="font-size: 2rem;">$<?= number_format($stats['total_revenue'], 2) ?></p>
      </div>
    </div>

    <!-- Notifications: Low‐stock + Pending Orders -->
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 2rem;">
      <div class="admin-card" style="flex: 1 1 300px;">
        <h4>Low‐Stock Books (&lt; 5)</h4>
        <?php if ($lowStockRes->num_rows): ?>
          <ul>
            <?php while ($row = $lowStockRes->fetch_assoc()): ?>
              <li><?= htmlspecialchars($row['title']) ?> – <?= intval($row['stock_quantity']) ?> left</li>
            <?php endwhile; ?>
          </ul>
          <a href="view_books.php" class="btn-admin">Manage Books</a>
        <?php else: ?>
          <p>All stocks are sufficient.</p>
        <?php endif; ?>
      </div>
      <div class="admin-card" style="flex: 1 1 300px;">
        <h4>Pending Orders</h4>
        <?php if ($pendingOrders->num_rows): ?>
          <ul>
            <?php while ($o = $pendingOrders->fetch_assoc()): ?>
              <li>Order #<?= $o['id'] ?> by User ID <?= $o['user_id'] ?> (<?= date('Y-m-d', strtotime($o['order_date'])) ?>)</li>
            <?php endwhile; ?>
          </ul>
          <a href="view_orders.php" class="btn-admin">Manage Orders</a>
        <?php else: ?>
          <p>No pending orders.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Quick Links -->
    <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
      <a href="add_book.php" class="btn-admin">➕ Add New Book</a>
      <a href="view_books.php" class="btn-admin">📚 View All Books</a>
      <a href="view_users.php" class="btn-admin">👥 View All Users</a>
      <a href="view_orders.php" class="btn-admin">🛒 View All Orders</a>
      <a href="generate_report.php" class="btn-admin">📄 Generate Report</a>
      <a href="settings.php" class="btn-admin">⚙️ Settings</a>
    </div>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
