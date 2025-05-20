<?php
// File: admin/view_orders.php

require_once __DIR__ . '/../includes/functions_admin.php';
redirectIfNotAdmin();

$error   = '';
$message = '';

// 1) Handle status update via GET (e.g., ?update_id=123&status=shipped)
if (isset($_GET['update_id'], $_GET['status'])) {
    $oid = intval($_GET['update_id']);
    $newStatus = $conn->real_escape_string($_GET['status']);
    $validStatuses = ['pending','paid','shipped','completed'];
    if (in_array($newStatus, $validStatuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param('si', $newStatus, $oid);
        if ($stmt->execute()) {
            $message = "Order #$oid status updated to $newStatus.";
        } else {
            $error = "Error updating status: " . $conn->error;
        }
    }
    header('Location: view_orders.php');
    exit;
}

// 2) Fetch filters and build WHERE clause
$whereClauses = [];

if (!empty($_GET['search_user'])) {
    $uid = intval($_GET['search_user']);
    $whereClauses[] = "o.user_id = $uid";
}
if (!empty($_GET['status_filter'])) {
    $st = $conn->real_escape_string($_GET['status_filter']);
    $whereClauses[] = "o.status = '$st'";
}
if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
    $df = $conn->real_escape_string($_GET['date_from']) . " 00:00:00";
    $dt = $conn->real_escape_string($_GET['date_to']) . " 23:59:59";
    $whereClauses[] = "o.order_date BETWEEN '$df' AND '$dt'";
}

$whereSQL = '';
if (count($whereClauses)) {
    $whereSQL = 'WHERE ' . implode(' AND ', $whereClauses);
}

// 3) Main query: join users + aggregated order total
//    We compute total_amount by summing order_items.price * quantity per order.
$sql = "
  SELECT 
    o.id AS order_id,
    u.username,
    o.status,
    o.order_date,
    IFNULL(SUM(oi.price * oi.quantity), 0) AS total_amount
  FROM orders o
  JOIN users u ON o.user_id = u.id
  LEFT JOIN order_items oi ON o.id = oi.order_id
  $whereSQL
  GROUP BY o.id, u.username, o.status, o.order_date
  ORDER BY o.order_date DESC
";
$resOrders = $conn->query($sql);

// 4) Fetch all users for “search by user” dropdown
$resUsers = $conn->query("SELECT id, username FROM users ORDER BY username ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Orders • Admin</title>
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <div class="admin-container">
    <h1 class="admin-title">All Orders</h1>

    <?php if ($message): ?>
      <div class="alert-admin alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
      <div class="alert-admin alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="GET" action="view_orders.php" class="form-admin" 
          style="display: flex; gap: 1rem; flex-wrap: wrap;">
      <label for="search_user">By User:</label>
      <select name="search_user" id="search_user">
        <option value="">-- All Users --</option>
        <?php while ($u = $resUsers->fetch_assoc()): ?>
          <option value="<?= $u['id'] ?>" 
            <?= isset($_GET['search_user']) && intval($_GET['search_user']) === intval($u['id']) 
                ? 'selected' : '' ?>>
            <?= htmlspecialchars($u['username']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label for="status_filter">By Status:</label>
      <select name="status_filter" id="status_filter">
        <option value="">-- All --</option>
        <?php foreach (['pending','paid','shipped','completed'] as $st): ?>
          <option value="<?= $st ?>" 
            <?= isset($_GET['status_filter']) && $_GET['status_filter'] === $st ? 'selected' : '' ?>>
            <?= ucfirst($st) ?>
          </option>
        <?php endforeach; ?>
      </select>

      <label for="date_from">From:</label>
      <input type="date" name="date_from" id="date_from" 
        value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>">

      <label for="date_to">To:</label>
      <input type="date" name="date_to" id="date_to" 
        value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>">

      <button type="submit" class="btn-admin">Filter</button>
      <a href="generate_report.php?type=sales
               &date_from=<?= htmlspecialchars($_GET['date_from'] ?? '') ?>
               &date_to=<?= htmlspecialchars($_GET['date_to'] ?? '') ?>" 
         class="btn-admin" style="margin-left:auto;">
        Generate Sales PDF
      </a>
    </form>

    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>User</th>
          <th>Total ($)</th>
          <th>Status</th>
          <th>Order Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resOrders->num_rows): ?>
          <?php $i = 1; while ($order = $resOrders->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($order['username']) ?></td>
              <td><?= number_format($order['total_amount'], 2) ?></td>
              <td><?= htmlspecialchars($order['status']) ?></td>
              <td><?= htmlspecialchars(date('Y-m-d', strtotime($order['order_date']))) ?></td>
              <td>
                <a href="view_order_detail.php?order_id=<?= $order['order_id'] ?>" 
                   class="btn-admin">Details</a>
                <?php if ($order['status'] === 'pending'): ?>
                  <a href="view_orders.php?update_id=<?= $order['order_id'] ?>&status=paid" 
                     class="btn-admin">Mark Paid</a>
                <?php elseif ($order['status'] === 'paid'): ?>
                  <a href="view_orders.php?update_id=<?= $order['order_id'] ?>&status=shipped" 
                     class="btn-admin">Mark Shipped</a>
                <?php elseif ($order['status'] === 'shipped'): ?>
                  <a href="view_orders.php?update_id=<?= $order['order_id'] ?>&status=completed" 
                     class="btn-admin">Mark Completed</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="6">No orders found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
