<?php
// File: includes/functions_admin.php

// 1) Load the DB connection class
require_once __DIR__ . '/../config/db.php';
// 2) Place FPDF if you generate PDF reports here.
//    (We'll leave that require for generatePDF itself.)

session_start();

// 3) Instantiate Database & expose $conn globally
$db   = new Database();
$conn = $db->getConnection();

/**
 * redirectIfNotAdmin()
 * --------------------
 * Verifies that $_SESSION['role'] === 'admin'. If not, redirect to admin_login.php.
 */
function redirectIfNotAdmin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header('Location: /admin/admin_login.php');
        exit;
    }
}

/**
 * hashPassword($plainPassword): string
 * ------------------------------------
 * Hash a plain‐text password using bcrypt.
 */
function hashPassword($plainPassword) {
    return password_hash($plainPassword, PASSWORD_BCRYPT);
}

/**
 * verifyPassword($plainPassword, $hash): bool
 * -------------------------------------------
 * Verify a plain‐text password against its bcrypt hash.
 */
function verifyPassword($plainPassword, $hash) {
    return password_verify($plainPassword, $hash);
}

/**
 * getStats(): array
 * -----------------
 * Returns an associative array of admin KPIs:
 *  - total_books
 *  - total_users
 *  - total_orders
 *  - total_revenue
 */
function getStats() {
    // pull in our global connection
    global $conn;
    $stats = [];

    // 1. Total books
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM books");
    $stats['total_books'] = (int) $res->fetch_assoc()['cnt'];

    // 2. Total users
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM users");
    $stats['total_users'] = (int) $res->fetch_assoc()['cnt'];

    // 3. Total orders
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM orders");
    $stats['total_orders'] = (int) $res->fetch_assoc()['cnt'];

    // 4. Total revenue
    $sql = "
      SELECT IFNULL(SUM(oi.price * oi.quantity),0) AS revenue
      FROM orders o
      JOIN order_items oi ON o.id = oi.order_id
      WHERE o.status != 'pending'
    ";
    $res = $conn->query($sql);
    $stats['total_revenue'] = (float) $res->fetch_assoc()['revenue'];

    return $stats;
}

/**
 * getLowStockBooks(): mysqli_result
 * ---------------------------------
 */
function getLowStockBooks($threshold = 5) {
    global $conn;
    $stmt = $conn->prepare("
      SELECT id, title, stock_quantity 
      FROM books 
      WHERE stock_quantity < ?
      ORDER BY stock_quantity ASC
    ");
    $stmt->bind_param('i', $threshold);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * getPendingOrders(): mysqli_result
 * ---------------------------------
 */
function getPendingOrders() {
    global $conn;
    $sql = "
      SELECT id, user_id, order_date
      FROM orders
      WHERE status = 'pending'
      ORDER BY order_date DESC
    ";
    return $conn->query($sql);
}

/**
 * generatePDF(...)
 * ----------------
 */
function generatePDF($title, $columns, $dataRows, $outputName = 'report.pdf') {
    // Load FPDF
    require_once __DIR__ . '/fpdf/fpdf.php';

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial','B',16);
    $pdf->Cell(0,10,$title,0,1,'C');
    $pdf->Ln(5);

    // Header
    $pdf->SetFont('Arial','B',12);
    $colW = (int)(180/count($columns));
    foreach ($columns as $col) {
        $pdf->Cell($colW,7,iconv('UTF-8','ISO-8859-1',$col),1,0,'C');
    }
    $pdf->Ln();

    // Rows
    $pdf->SetFont('Arial','',11);
    foreach ($dataRows as $row) {
        foreach ($row as $cell) {
            $pdf->Cell($colW,6,iconv('UTF-8','ISO-8859-1',(string)$cell),1,0,'C');
        }
        $pdf->Ln();
    }

    $pdf->Output('D',$outputName);
    exit;
}
