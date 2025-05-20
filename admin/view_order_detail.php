<?php
// File: admin/view_order_detail.php

require_once __DIR__ . '/../includes/functions_admin.php';
redirectIfNotAdmin();

if (!isset($_GET['order_id'])) {
    header('Location: view_orders.php');
    exit;
}

$order_id = intval($_GET['order_id']);

// 1) Fetch order & user info
$stmt = $conn->prepare("
  SELECT o.id, o.user_id, o.status, o.order_date, u.username, u.email
  FROM orders o
  JOIN users u ON o.user_id = u.id
  WHERE o.id = ? LIMIT 1
");
$stmt->bind_param('i', $order_id);
$stmt->execute();
$orderInfo = $stmt->get_result()->fetch_assoc();
if (!$orderInfo) {
    header('Location: view_orders.php');
    exit;
}

// 2) Fetch all order_items for that order
$stmt2 = $conn->prepare("
  SELECT oi.book_id, oi.quantity, oi.price, b.title
  FROM order_items oi
  JOIN books b ON oi.book_id = b.id
  WHERE oi.order_id = ?
");
$stmt2->bind_param('i', $order_id);
$stmt2->execute();
$resItems = $stmt2->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Order #<?= $orderInfo['id'] ?> Details • Admin</title>
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <div class="admin-container">
    <h1 class="admin-title">Order #<?= $orderInfo['id'] ?> Details</h1>

    <div class="admin-card">
      <p><strong>User:</strong> <?= htmlspecialchars($orderInfo['username']) ?> (<?= htmlspecialchars($orderInfo['email']) ?>)</p>
      <p><strong>Status:</strong> <?= htmlspecialchars($orderInfo['status']) ?></p>
      <p><strong>Date:</strong> <?= htmlspecialchars(date('Y-m-d', strtotime($orderInfo['order_date']))) ?></p>
    </div>

    <h3>Ordered Items</h3>
    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Book Title</th>
          <th>Quantity</th>
          <th>Price ($)</th>
          <th>Subtotal ($)</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resItems->num_rows): ?>
          <?php 
            $i = 1;
            $grandTotal = 0;
            while ($item = $resItems->fetch_assoc()):
              $subtotal = $item['price'] * $item['quantity'];
              $grandTotal += $subtotal;
          ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($item['title']) ?></td>
              <td><?= intval($item['quantity']) ?></td>
              <td><?= number_format($item['price'], 2) ?></td>
              <td><?= number_format($subtotal, 2) ?></td>
            </tr>
          <?php endwhile; ?>
          <tr>
            <td colspan="4" style="text-align:right;"><strong>Grand Total:</strong></td>
            <td><strong>$<?= number_format($grandTotal, 2) ?></strong></td>
          </tr>
        <?php else: ?>
          <tr>
            <td colspan="5">No items found for this order.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <a href="view_orders.php" class="btn-admin">← Back to Orders</a>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
