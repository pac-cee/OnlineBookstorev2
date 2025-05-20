<?php
// File: views/profile.php

// 1) Include the Database class and create a $conn instance
require_once __DIR__ . '/../config/db.php';
session_start();

$dbInstance = new Database();
$conn       = $dbInstance->getConnection();

// 2) Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$uid     = $_SESSION['user_id'];
$error   = '';
$message = '';

// 3) Fetch user information (name, username, email, password hash)
$stmtUser = $conn->prepare("SELECT name, username, email, password FROM users WHERE id = ? LIMIT 1");
$stmtUser->bind_param('i', $uid);
$stmtUser->execute();
$resUser = $stmtUser->get_result();

if ($resUser->num_rows !== 1) {
    // Something’s wrong—user not found => force logout
    header('Location: logout.php');
    exit;
}

$user = $resUser->fetch_assoc();

// 4) Handle Password Update
if (isset($_POST['update_password'])) {
    $currentPassword    = $_POST['current_password'] ?? '';
    $newPassword        = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

    if (empty($currentPassword) || empty($newPassword) || empty($confirmNewPassword)) {
        $error = 'All password fields are required.';
    } elseif (!password_verify($currentPassword, $user['password'])) {
        $error = 'Current password is incorrect.';
    } elseif (strlen($newPassword) < 6) {
        $error = 'New password must be at least 6 characters long.';
    } elseif ($newPassword !== $confirmNewPassword) {
        $error = 'New password and confirmation do not match.';
    } else {
        $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmtUpd = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmtUpd->bind_param('si', $newHash, $uid);
        if ($stmtUpd->execute()) {
            $message         = 'Password updated successfully.';
            $user['password'] = $newHash;
        } else {
            $error = 'Error updating password. Please try again later.';
        }
    }
}
// 5) Handle “Generate Report” request
if (isset($_POST['generate_report'])) {
    // a) Purchased Books
    $stmtBooks = $conn->prepare("
      SELECT b.title, b.author, oi.quantity, o.order_date
      FROM orders o
      JOIN order_items oi ON o.id = oi.order_id
      JOIN books b ON oi.book_id = b.id
      WHERE o.user_id = ?
      ORDER BY o.order_date DESC
    ");
    $stmtBooks->bind_param('i', $uid);
    $stmtBooks->execute();
    $booksRes = $stmtBooks->get_result();
    $bookRows = [];
    while ($r = $booksRes->fetch_assoc()) {
        $bookRows[] = [
            $r['title'],
            $r['author'],
            intval($r['quantity']),
            date('Y-m-d', strtotime($r['order_date']))
        ];
    }
    $bookCols = ['Title','Author','Quantity','Purchased On'];

    // b) Quiz Results (corrected: no qr.passed)
    $stmtQuiz = $conn->prepare("
      SELECT q.title AS quiz_title,
             qr.score,
             qr.taken_at
      FROM quiz_results qr
      JOIN quizzes q ON qr.quiz_id = q.id
      WHERE qr.user_id = ?
      ORDER BY qr.taken_at DESC
    ");
    $stmtQuiz->bind_param('i', $uid);
    $stmtQuiz->execute();
    $quizRes  = $stmtQuiz->get_result();
    $quizRows = [];
    while ($rq = $quizRes->fetch_assoc()) {
        $quizRows[] = [
            $rq['quiz_title'],
            intval($rq['score']),
            date('Y-m-d', strtotime($rq['taken_at']))
        ];
    }
    $quizCols = ['Quiz Title','Score','Date Taken'];

    // c) Build PDF using FPDF (unchanged)
    require_once __DIR__ . '/../includes/fpdf/fpdf.php';
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,'My Purchases & Quiz Report',0,1,'C');
    $pdf->Ln(5);

    // Section: Purchased Books
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,8,'Books Purchased',0,1);
    $pdf->SetFont('Arial','B',12);
    $colW = (int)(180 / count($bookCols));
    foreach ($bookCols as $col) {
        $pdf->Cell($colW,7,iconv('UTF-8','ISO-8859-1',$col),1,0,'C');
    }
    $pdf->Ln();
    $pdf->SetFont('Arial','',11);
    foreach ($bookRows as $row) {
        foreach ($row as $cell) {
            $text = (string)$cell;
            $pdf->Cell($colW,6,iconv('UTF-8','ISO-8859-1',$text),1,0,'C');
        }
        $pdf->Ln();
    }
    $pdf->Ln(10);

    // Section: Quiz Results
    $pdf->SetFont('Arial','B',14);
    $pdf->Cell(0,8,'Quiz Results',0,1);
    $pdf->SetFont('Arial','B',12);
    $colW2 = (int)(180 / count($quizCols));
    foreach ($quizCols as $col) {
        $pdf->Cell($colW2,7,iconv('UTF-8','ISO-8859-1',$col),1,0,'C');
    }
    $pdf->Ln();
    $pdf->SetFont('Arial','',11);
    foreach ($quizRows as $row) {
        foreach ($row as $cell) {
            $text = (string)$cell;
            $pdf->Cell($colW2,6,iconv('UTF-8','ISO-8859-1',$text),1,0,'C');
        }
        $pdf->Ln();
    }

    $pdf->Output('D', "profile_report_user_{$uid}.pdf");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile • OnlineBookstore</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Your CSS files -->
  <link rel="stylesheet" href="/assets/css/styles.css">
  <link rel="stylesheet" href="/assets/css/dashboard.css">
  <link rel="stylesheet" href="/assets/css/dashboard_custom.css">
  <link rel="stylesheet" href="/assets/css/profile.css">
</head>
<body>
  <?php include __DIR__ . '/../views/includes/header.php'; ?>

  <div class="container" style="max-width:800px; margin:2rem auto;">
    <h1>My Profile</h1>

    <!-- Display user details -->
    <div class="profile-details" style="margin-bottom:1.5rem;">
      <p><strong>Name:</strong> <?= htmlspecialchars($user['name']) ?></p>
      <p><strong>Username:</strong> <?= htmlspecialchars($user['username']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
    </div>

    <!-- Update Password Form -->
    <div class="profile-card" style="border:1px solid #ddd; padding:1rem; border-radius:0.5rem; margin-bottom:2rem;">
      <h2>Change Password</h2>
      <?php if ($message): ?>
        <div class="alert-admin alert-success"><?= htmlspecialchars($message) ?></div>
      <?php elseif ($error): ?>
        <div class="alert-admin alert-error"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="profile.php" class="form-admin">
        <label for="current_password">Current Password*</label>
        <input type="password" name="current_password" id="current_password" required>

        <label for="new_password">New Password* (min 6 chars)</label>
        <input type="password" name="new_password" id="new_password" required>

        <label for="confirm_new_password">Confirm New Password*</label>
        <input type="password" name="confirm_new_password" id="confirm_new_password" required>

        <button type="submit" name="update_password" class="btn-admin">Update Password</button>
      </form>
    </div>

    <!-- Generate Report Button -->
    <form method="POST" action="profile.php" style="margin-top:1.5rem;">
      <button type="submit" name="generate_report" class="btn-admin">
        📄 Download My Purchases & Quiz Report
      </button>
    </form>
  </div>

  <?php include __DIR__ . '/../views/includes/footer.php'; ?>
</body>
</html>
