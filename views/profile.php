<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';

$user_id = $_SESSION['user_id'];
$db = new Database();
$conn = $db->getConnection();

// Get user details
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get user statistics
$stats = [
    'total_books' => 0,
    'total_quizzes' => 0,
    'total_orders' => 0
];

// Get total books read
$sql = "SELECT COUNT(*) as total FROM progress WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_books'] = $result->fetch_assoc()['total'];

// Get total quizzes taken
$sql = "SELECT COUNT(*) as total FROM quiz_results WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_quizzes'] = $result->fetch_assoc()['total'];

// Get total orders
$sql = "SELECT COUNT(*) as total FROM orders WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();
$stats['total_orders'] = $result->fetch_assoc()['total'];

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<he>
    <title>My Profile - OnlineBookstore</title>
    <?php
    $pageStyles = ['dashboard_custom', 'profile'];
    require_once __DIR__ . '/includes/header.php';
    ?>
   <head>
    <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A modern online bookstore to buy, sell, and manage books.">
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f4da.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
     <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/profile.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/dashboard_custom.css">
    <script src="/assets/js/main.js" defer></script>
    <style>
        /* Only keep unique dashboard layout tweaks, rely on styles.css for shared UI */
        .dashboard {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--bg);
            min-height: 100vh;
            padding-bottom: 2em;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(42,82,152,0.10);
        }
        .dashboard-hero-img img {
            max-width: 160px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(42,82,152,0.09);
        }
        .dashboard-insights {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1.2em;
            margin-bottom: 2em;
        }
        .insight-card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(42,82,152,0.08);
            padding: 1.4em 1.1em 1.2em 1.1em;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        .insight-title {
            font-size: 1em;
            color: #6c7a89;
            margin-bottom: 0.4em;
            font-weight: 600;
        }
        .insight-value {
            font-size: 2em;
            font-weight: 700;
            color: var(--text);
        }
        .dashboard-hero .hero-text h1 { font-size: 2em; font-weight: 700; color: #23263a; margin-bottom: 0.4em; }
        .dashboard-hero .hero-text .subtitle { font-size: 1.1em; color: #6c7a89; margin-bottom: 1.2em; }
        .quick-actions { display: flex; gap: 1em; flex-wrap: wrap; }
        .quick-btn { display: inline-flex; align-items: center; gap: 0.5em; background: #2a5298; color: #fff; padding: 0.65em 1.2em; border-radius: 8px; text-decoration: none; font-weight: 500; transition: background 0.2s; }
        .quick-btn:hover { background: #1c3561; }
        .dashboard-hero-img { flex: 1; display: flex; justify-content: flex-end; }
        .dashboard-hero-img img { max-width: 160px; border-radius: 10px; box-shadow: 0 2px 8px rgba(42,82,152,0.09); }
        .dashboard-insights { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.2em; margin-bottom: 2em; }
        .insight-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 10px rgba(42,82,152,0.08); padding: 1.4em 1.1em 1.2em 1.1em; display: flex; flex-direction: column; align-items: flex-start; }
        .insight-title { font-size: 1em; color: #6c7a89; margin-bottom: 0.4em; font-weight: 600; }
        .insight-value { font-size: 2em; font-weight: 700; color: #23263a; }
        @media (max-width: 900px) {
            .dashboard-insights { grid-template-columns: repeat(2, 1fr); }
            .dashboard-hero { flex-direction: column; gap: 1.5em; padding: 1.2em 0.9em 1.2em 0.9em; }
            .dashboard-hero-img { justify-content: center; }
        }
        @media (max-width: 600px) {
            .dashboard-insights { grid-template-columns: 1fr; }
            .dashboard { padding: 0.4em; }
            .main-nav { flex-direction: column; gap: 1em; padding: 1em 0.7em; }
            .dashboard-hero { padding: 1em 0.4em 1em 0.4em; }
        }
    </style>
    <script defer src="../assets/js/dashboard_custom.js"></script>
</head>
<body>
    <div class="dashboard">
        <nav class="main-nav">
            <a href="../index.php" class="nav-logo"><i class="fas fa-house"></i> Home</a>
            <div class="nav-links">
                <a href="book_catalog.php"><i class="fas fa-book-open"></i> Book Catalog</a>
                <a href="quiz.php"><i class="fas fa-question-circle"></i> Quizzes</a>
                <a href="quiz_results.php"><i class="fas fa-list-ol"></i> Quiz Results</a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a>
                <a href="progress.php"><i class="fas fa-chart-line"></i> My Progress</a>
            </div>
            <div class="nav-actions">
                <button id="theme-btn" title="Toggle theme"><span id="theme-icon">🌙</span></button>
                <div class="profile-dropdown">
                    <button id="profileBtn" class="active"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['username']); ?></button>
                    <div class="profile-menu" id="profileMenu">
                        <a href="#" class="active">Profile</a>
                        <form action="logout.php" method="POST"><button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button></form>
                    </div>
                </div>
            </div>
        </nav>

        <div class="profile-container">
            <section class="profile-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h1><?php echo htmlspecialchars($user['username']); ?>'s Profile</h1>
                <p class="member-since">Member since: <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
            </section>

            <section class="profile-stats">
                <div class="stat-card">
                    <i class="fas fa-book"></i>
                    <div class="stat-value"><?php echo $stats['total_books']; ?></div>
                    <div class="stat-label">Books Read</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-question-circle"></i>
                    <div class="stat-value"><?php echo $stats['total_quizzes']; ?></div>
                    <div class="stat-label">Quizzes Taken</div>
                </div>
                <div class="stat-card">
                    <i class="fas fa-shopping-cart"></i>
                    <div class="stat-value"><?php echo $stats['total_orders']; ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
            </section>

            <section class="profile-details">
                <div class="profile-section">
                    <h2>Account Settings</h2>
                    <form id="profileUpdateForm" class="profile-form">
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password">
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
    <script src="../assets/js/dashboard_custom.js"></script>
    <script src="../assets/js/profile.js"></script>
</body>
</html>
