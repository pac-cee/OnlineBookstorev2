<?php
require_once __DIR__ . '/../config/db.php';

function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /views/admin_login.php');
        exit;
    }
}

function getKPIs() {
    $db = new Database();
    $conn = $db->getConnection();
    
    $today = date('Y-m-d');
    $month = date('Y-m');
    
    $kpis = [
        'total_books' => 0,
        'total_users' => 0,
        'orders_today' => 0,
        'orders_month' => 0,
        'low_stock' => 0,
        'revenue_today' => 0,
        'revenue_month' => 0
    ];
    
    // Total books
    $result = $conn->query('SELECT COUNT(*) as total FROM books');
    $kpis['total_books'] = $result->fetch_assoc()['total'];
    
    // Total users
    $result = $conn->query('SELECT COUNT(*) as total FROM users');
    $kpis['total_users'] = $result->fetch_assoc()['total'];
    
    // Orders and revenue today
    $sql = "SELECT COUNT(*) as total, COALESCE(SUM(price), 0) as revenue 
            FROM orders o 
            JOIN books b ON o.book_id = b.id 
            WHERE DATE(o.order_date) = '$today'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $kpis['orders_today'] = $row['total'];
    $kpis['revenue_today'] = $row['revenue'];
    
    // Orders and revenue this month
    $sql = "SELECT COUNT(*) as total, COALESCE(SUM(price), 0) as revenue 
            FROM orders o 
            JOIN books b ON o.book_id = b.id 
            WHERE DATE_FORMAT(o.order_date, '%Y-%m') = '$month'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $kpis['orders_month'] = $row['total'];
    $kpis['revenue_month'] = $row['revenue'];
    
    // Low stock books
    $result = $conn->query('SELECT COUNT(*) as total FROM books WHERE stock_quantity < stock_threshold');
    $kpis['low_stock'] = $result->fetch_assoc()['total'];
    
    $conn->close();
    return $kpis;
}

function getRecentOrders($limit = 5) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT o.id, u.username, b.title, o.order_date, o.status, b.price 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            JOIN books b ON o.book_id = b.id 
            ORDER BY o.order_date DESC 
            LIMIT ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    
    $stmt->close();
    $conn->close();
    return $orders;
}

function generatePDF($html, $filename = 'report.pdf') {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $pdf = new TCPDF();
    $pdf->SetCreator('Online Bookstore');
    $pdf->SetAuthor('Admin');
    $pdf->SetTitle('Report');
    
    $pdf->AddPage();
    $pdf->writeHTML($html);
    
    return $pdf->Output($filename, 'D');
}

function addBook($data) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "INSERT INTO books (title, author, price, stock_quantity, description, cover_image) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdiis', 
        $data['title'],
        $data['author'],
        $data['price'],
        $data['stock_quantity'],
        $data['description'],
        $data['cover_image']
    );
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

function updateBook($id, $data) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "UPDATE books 
            SET title = ?, author = ?, price = ?, 
                stock_quantity = ?, description = ?, cover_image = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssdissi', 
        $data['title'],
        $data['author'],
        $data['price'],
        $data['stock_quantity'],
        $data['description'],
        $data['cover_image'],
        $id
    );
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

function deleteBook($id) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "DELETE FROM books WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

function getUsers($page = 1, $perPage = 10) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $offset = ($page - 1) * $perPage;
    
    $sql = "SELECT id, username, email, role, status, created_at 
            FROM users 
            ORDER BY created_at DESC 
            LIMIT ? OFFSET ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $perPage, $offset);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    // Get total count for pagination
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    $total = $result->fetch_assoc()['total'];
    
    $stmt->close();
    $conn->close();
    
    return [
        'users' => $users,
        'total' => $total,
        'pages' => ceil($total / $perPage)
    ];
}

function updateUserRole($userId, $role) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "UPDATE users SET role = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $role, $userId);
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

function updateUserStatus($userId, $status) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "UPDATE users SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $status, $userId);
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}

function getConfig($key) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "SELECT value FROM config WHERE `key` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $value = $result->fetch_assoc()['value'] ?? null;
    
    $stmt->close();
    $conn->close();
    
    return $value;
}

function updateConfig($key, $value) {
    $db = new Database();
    $conn = $db->getConnection();
    
    $sql = "UPDATE config SET value = ?, updated_by = ? WHERE `key` = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sis', $value, $_SESSION['user_id'], $key);
    
    $success = $stmt->execute();
    $stmt->close();
    $conn->close();
    
    return $success;
}