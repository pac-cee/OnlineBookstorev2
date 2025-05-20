<?php
// File: admin/generate_report.php

require_once __DIR__ . '/../includes/functions_admin.php';
redirectIfNotAdmin();

/**
 * We support three types of reports:
 *  - books: list all books or by category
 *  - sales: all orders between date_from & date_to
 *  - users: user activity (total orders, spent) between date range
 */

$type       = $_GET['type'] ?? '';
$dateFrom   = $_GET['date_from'] ?? '';
$dateTo     = $_GET['date_to'] ?? '';
$categoryId = $_GET['category_id'] ?? '';
$userId     = $_GET['user_id'] ?? '';

if ($type === 'books') {
    // Books Report
    $columns = ['ID','Title','Author','Category','Price ($)','Stock Qty'];
    $sql = "
      SELECT b.id, b.title, b.author, c.name AS category, b.price, b.stock_quantity
      FROM books b
      LEFT JOIN categories c ON b.category_id = c.id
    ";
    if (!empty($categoryId)) {
        $cid = intval($categoryId);
        $sql .= " WHERE b.category_id = $cid";
        $suffix = "_cat_$cid";
    } else {
        $suffix = "_all";
    }
    $sql .= " ORDER BY b.title ASC";
    $res = $conn->query($sql);

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            $r['id'],
            $r['title'],
            $r['author'],
            $r['category'],
            number_format($r['price'], 2),
            intval($r['stock_quantity'])
        ];
    }

    generatePDF("Books Report{$suffix}", $columns, $rows, "books_report{$suffix}.pdf");
}

if ($type === 'sales') {
    // Sales Report: orders (with total amount) between date range
    if (empty($dateFrom) || empty($dateTo)) {
        die("<p style='color:red;'>Date range required. <a href='view_orders.php'>Go back</a></p>");
    }
    $columns = ['Order ID','User','Total ($)','Status','Order Date'];
    $df = $conn->real_escape_string($dateFrom) . " 00:00:00";
    $dt = $conn->real_escape_string($dateTo) . " 23:59:59";

    $sql = "
      SELECT 
        o.id AS order_id,
        u.username,
        IFNULL(SUM(oi.price * oi.quantity), 0) AS total_amount,
        o.status,
        o.order_date
      FROM orders o
      JOIN users u ON o.user_id = u.id
      LEFT JOIN order_items oi ON o.id = oi.order_id
      WHERE o.order_date BETWEEN '$df' AND '$dt'
      GROUP BY o.id, u.username, o.status, o.order_date
      ORDER BY o.order_date ASC
    ";
    $res = $conn->query($sql);

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            $r['order_id'],
            $r['username'],
            number_format($r['total_amount'], 2),
            $r['status'],
            date('Y-m-d', strtotime($r['order_date']))
        ];
    }

    generatePDF("Sales Report ({$dateFrom} to {$dateTo})", $columns, $rows, "sales_report_{$dateFrom}_{$dateTo}.pdf");
}

if ($type === 'users') {
    // User Activity Report: users + their total orders & total spent in given range
    if (empty($dateFrom) || empty($dateTo)) {
        die("<p style='color:red;'>Date range required. <a href='view_users.php'>Go back</a></p>");
    }
    $columns = ['User ID','Username','Email','Total Orders','Total Spent ($)'];
    $df = $conn->real_escape_string($dateFrom) . " 00:00:00";
    $dt = $conn->real_escape_string($dateTo) . " 23:59:59";

    $sql = "
      SELECT 
        u.id,
        u.username,
        u.email,
        COUNT(o.id) AS total_orders,
        IFNULL(SUM(oi.price * oi.quantity), 0) AS total_spent
      FROM users u
      LEFT JOIN orders o ON u.id = o.user_id 
          AND o.order_date BETWEEN '$df' AND '$dt'
      LEFT JOIN order_items oi ON o.id = oi.order_id
      GROUP BY u.id, u.username, u.email
      ORDER BY total_spent DESC
    ";
    $res = $conn->query($sql);

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = [
            $r['id'],
            $r['username'],
            $r['email'],
            intval($r['total_orders']),
            number_format($r['total_spent'], 2)
        ];
    }

    generatePDF("User Activity Report ({$dateFrom} to {$dateTo})", $columns, $rows, "user_activity_{$dateFrom}_{$dateTo}.pdf");
}

// Fallback: show the form to pick report type / filters
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Generate Report • Admin</title>
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
  <?php include __DIR__ . '/../views/includes/header.php'; ?>

  <div class="admin-container">
    <h1 class="admin-title">Generate PDF Report</h1>
    <p>Select the report type and any filters below, then click “Generate PDF.”</p>

    <form action="generate_report.php" method="GET" class="form-admin">
      <label for="type">Report Type*</label>
      <select name="type" id="type" required onchange="toggleFilters()">
        <option value="">-- Select Report --</option>
        <option value="books" <?= $type === 'books' ? 'selected' : '' ?>>Books Report</option>
        <option value="sales" <?= $type === 'sales' ? 'selected' : '' ?>>Sales Report</option>
        <option value="users" <?= $type === 'users' ? 'selected' : '' ?>>User Activity Report</option>
      </select>

      <div id="filter-dates" style="display:none;">
        <label for="date_from">Date From</label>
        <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($dateFrom) ?>">
        <label for="date_to">Date To</label>
        <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($dateTo) ?>">
      </div>

      <div id="filter-category" style="display:none;">
        <?php
        $resCats = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
        ?>
        <label for="category_id">Category</label>
        <select name="category_id" id="category_id">
          <option value="">-- All Categories --</option>
          <?php while ($c = $resCats->fetch_assoc()): ?>
            <option value="<?= $c['id'] ?>"
              <?= $categoryId == $c['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div id="filter-user" style="display:none;">
        <?php
        $resUsers = $conn->query("SELECT id, username FROM users ORDER BY username ASC");
        ?>
        <label for="user_id">User</label>
        <select name="user_id" id="user_id">
          <option value="">-- All Users --</option>
          <?php while ($u = $resUsers->fetch_assoc()): ?>
            <option value="<?= $u['id'] ?>"
              <?= $userId == $u['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['username']) ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <button type="submit" class="btn-admin">Generate PDF</button>
    </form>
  </div>

  <?php include __DIR__ . '/../views/includes/footer.php'; ?>

  <script>
    function toggleFilters() {
      var type = document.getElementById('type').value;
      document.getElementById('filter-dates').style.display = (type === 'sales' || type === 'users') ? 'block' : 'none';
      document.getElementById('filter-category').style.display = (type === 'books') ? 'block' : 'none';
      document.getElementById('filter-user').style.display = (type === 'users') ? 'block' : 'none';
    }
    document.addEventListener('DOMContentLoaded', toggleFilters);
  </script>
</body>
</html>
