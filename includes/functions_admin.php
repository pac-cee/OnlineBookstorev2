<?php
// File: includes/functions_admin.php

// 1) Load the DB connection
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/fpdf/fpdf.php';
session_start();

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
 *  - total_revenue   (summing order_items.price * order_items.quantity for all orders with status ≠ 'pending')
 */
function getStats() {
    global $conn;
    $stats = [];

    // 1. Total books
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM books");
    $stats['total_books'] = (int) $res->fetch_assoc()['cnt'];

    // 2. Total users (any role)
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM users");
    $stats['total_users'] = (int) $res->fetch_assoc()['cnt'];

    // 3. Total orders (every row in orders table)
    $res = $conn->query("SELECT COUNT(*) AS cnt FROM orders");
    $stats['total_orders'] = (int) $res->fetch_assoc()['cnt'];

    // 4. Total revenue: sum of (order_items.price * order_items.quantity)
    //    for orders whose status is NOT 'pending'.
    $sql = "
      SELECT IFNULL(SUM(oi.price * oi.quantity), 0) AS revenue
      FROM orders o
      JOIN order_items oi ON o.id = oi.order_id
      WHERE o.status != 'pending'
    ";
    $res = $conn->query($sql);
    $stats['total_revenue'] = (float) $res->fetch_assoc()['revenue'];

    return $stats;
}

/**
 * getLowStockBooks($threshold = 5): mysqli_result
 * -----------------------------------------------
 * Returns a result set of books whose stock_quantity < $threshold.
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
 * Returns orders with status = 'pending', most recent first.
 */
function getPendingOrders() {
    global $conn;
    $sql = "
      SELECT o.id, o.user_id, o.order_date
      FROM orders o
      WHERE o.status = 'pending'
      ORDER BY o.order_date DESC
    ";
    return $conn->query($sql);
}

/**
 * generatePDF($title, $columns, $dataRows, $outputName)
 * -----------------------------------------------------
 * A minimal FPDF‐based generator of a simple tabular PDF.
 *   - $title: Report title string
 *   - $columns: array of column‐header strings
 *   - $dataRows: array of rows, each row itself an indexed array of cell values
 *   - $outputName: filename for download (e.g. 'report.pdf')
 *
 * You must have placed FPDF (fpdf.php) under /includes/fpdf/.
 */
function generatePDF($title, $columns, $dataRows, $outputName = 'report.pdf') {
    // Load FPDF
    require_once __DIR__ . '/fpdf/fpdf.php';

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    // Title
    $pdf->Cell(0, 10, $title, 0, 1, 'C');
    $pdf->Ln(5);

    // Header Row
    $pdf->SetFont('Arial', 'B', 12);
    $colWidth = (int)(180 / count($columns)); // distribute across page width (approx)
    foreach ($columns as $col) {
        $pdf->Cell($colWidth, 7, iconv('UTF-8', 'ISO-8859-1', $col), 1, 0, 'C');
    }
    $pdf->Ln();

    // Data Rows
    $pdf->SetFont('Arial', '', 11);
    foreach ($dataRows as $row) {
        foreach ($row as $cell) {
            $cellText = (string)$cell;
            $pdf->Cell($colWidth, 6, iconv('UTF-8', 'ISO-8859-1', $cellText), 1, 0, 'C');
        }
        $pdf->Ln();
    }

    // Output as download
    $pdf->Output('D', $outputName);
    exit;
}
