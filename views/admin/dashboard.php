<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

// Verify admin session
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: /views/login.php');
    exit;
}

$db = new Database();
$conn = $db->getConnection();

// Fetch KPIs
$today = date('Y-m-d');
$month = date('Y-m');

// Total books
$books_query = $conn->query('SELECT COUNT(*) as total FROM books');
$total_books = $books_query->fetch_assoc()['total'];

// Total users
$users_query = $conn->query('SELECT COUNT(*) as total FROM users');
$total_users = $users_query->fetch_assoc()['total'];

// Orders today
$orders_today_query = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE(order_date) = '$today'");
$orders_today = $orders_today_query->fetch_assoc()['total'];

// Orders this month
$orders_month_query = $conn->query("SELECT COUNT(*) as total FROM orders WHERE DATE_FORMAT(order_date, '%Y-%m') = '$month'");
$orders_month = $orders_month_query->fetch_assoc()['total'];

// Low stock books
$low_stock_query = $conn->query('SELECT COUNT(*) as total FROM books WHERE stock_quantity < stock_threshold');
$low_stock = $low_stock_query->fetch_assoc()['total'];

// Recent orders
$recent_orders = $conn->query('SELECT o.id, u.username, o.order_date, o.status FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC LIMIT 5');

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Online Bookstore</title>
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard_custom.css">
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
    <div class="dashboard">
        <nav class="main-nav">
            <a href="/index.php" class="nav-logo">
                <i class="fas fa-book"></i> Bookstore Admin
            </a>
            <div class="nav-links">
                <a href="/views/admin/books.php">
                    <i class="fas fa-book-open"></i> Books
                </a>
                <a href="/views/admin/users.php">
                    <i class="fas fa-users"></i> Users
                </a>
                <a href="/views/admin/orders.php">
                    <i class="fas fa-shopping-cart"></i> Orders
                </a>
                <a href="/views/admin/reports.php">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <a href="/views/admin/settings.php">
                    <i class="fas fa-cog"></i> Settings
                </a>
            </div>
            <div class="nav-actions">
                <div class="profile-dropdown">
                    <button id="profileBtn">
                        <i class="fas fa-user-shield"></i> 
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </button>
                    <div class="profile-menu" id="profileMenu">
                        <form action="/views/logout.php" method="POST">
                            <button type="submit" class="logout-btn">
                                <i class="fas fa-sign-out-alt"></i> Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <main>
            <section class="dashboard-hero">
                <div class="hero-text">
                    <h1>Admin Dashboard</h1>
                    <p class="subtitle">Manage your online bookstore</p>
                </div>
            </section>

            <section class="dashboard-insights">
                <div class="insight-card">
                    <div class="insight-title">Total Books</div>
                    <div class="insight-value"><?php echo $total_books; ?></div>
                </div>
                <div class="insight-card">
                    <div class="insight-title">Total Users</div>
                    <div class="insight-value"><?php echo $total_users; ?></div>
                </div>
                <div class="insight-card">
                    <div class="insight-title">Orders Today</div>
                    <div class="insight-value"><?php echo $orders_today; ?></div>
                </div>
                <div class="insight-card">
                    <div class="insight-title">Orders This Month</div>
                    <div class="insight-value"><?php echo $orders_month; ?></div>
                </div>
            </section>

            <section class="quick-actions">
                <a href="/views/admin/add_book.php" class="quick-btn">
                    <i class="fas fa-plus"></i> Add New Book
                </a>
                <a href="/views/admin/users.php" class="quick-btn">
                    <i class="fas fa-user-plus"></i> Add User
                </a>
                <a href="/views/admin/reports.php" class="quick-btn">
                    <i class="fas fa-file-pdf"></i> Generate Report
                </a>
            </section>

            <?php if ($low_stock > 0): ?>
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo $low_stock; ?> books are below stock threshold
            </div>
            <?php endif; ?>

            <section class="recent-orders">
                <h2>Recent Orders</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>User</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <span class="status-badge <?php echo strtolower($order['status']); ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="/views/admin/orders.php?id=<?php echo $order['id']; ?>" 
                                       class="btn-link">View Details</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
    <script src="/assets/js/dashboard_custom.js"></script>
</body>
</html>