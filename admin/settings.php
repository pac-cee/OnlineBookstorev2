<?php
// File: admin/settings.php

require_once __DIR__ . '/../includes/functions_admin.php';
redirectIfNotAdmin();

$message = '';
$error   = '';

// Ensure you have a `settings` table. If not, run:
/*
  CREATE TABLE settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) UNIQUE NOT NULL,
    setting_value VARCHAR(255) NOT NULL
  );
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stockThreshold = intval($_POST['stock_threshold']);
    $salesTax       = floatval($_POST['sales_tax']);
    $emailVerify    = isset($_POST['email_verify']) ? '1' : '0';

    // Helper to insert/update
    $pairs = [
      ['stock_threshold', $stockThreshold],
      ['sales_tax', $salesTax],
      ['email_verify', $emailVerify]
    ];
    foreach ($pairs as $pair) {
        $key = $pair[0];
        $val = (string)$pair[1];
        $stmt = $conn->prepare("
          INSERT INTO settings (setting_key, setting_value)
          VALUES (?, ?)
          ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
        ");
        $stmt->bind_param('ss', $key, $val);
        $stmt->execute();
    }
    $message = 'Settings saved.';
}

// Fetch current settings
$res = $conn->query("SELECT setting_key, setting_value FROM settings");
$settings = [];
while ($r = $res->fetch_assoc()) {
    $settings[$r['setting_key']] = $r['setting_value'];
}

$stockThreshold = $settings['stock_threshold'] ?? 5;
$salesTax       = $settings['sales_tax'] ?? 0;
$emailVerify    = $settings['email_verify'] ?? '0';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Settings • Admin</title>
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
  <?php include __DIR__ . '/../includes/header.php'; ?>

  <div class="admin-container">
    <h1 class="admin-title">Site Settings</h1>

    <?php if ($message): ?>
      <div class="alert-admin alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
      <div class="alert-admin alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="settings.php" method="POST" class="form-admin">
      <label for="stock_threshold">Low‐Stock Threshold</label>
      <input type="number" name="stock_threshold" id="stock_threshold" 
             value="<?= intval($stockThreshold) ?>" required>

      <label for="sales_tax">Sales Tax (%)</label>
      <input type="number" step="0.01" name="sales_tax" id="sales_tax" 
             value="<?= htmlspecialchars($salesTax) ?>">

      <label>
        <input type="checkbox" name="email_verify" 
          <?= $emailVerify === '1' ? 'checked' : '' ?>>
        Require Email Verification for New Users
      </label>

      <button type="submit" class="btn-admin">Save Settings</button>
    </form>
  </div>

  <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
