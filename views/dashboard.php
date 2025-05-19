<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';
$username = $_SESSION['username'] ?? 'User';
$role = $_SESSION['role'] ?? 'individual';
$user_id = $_SESSION['user_id'];

// Fetch stats for insights
$db = new Database();
$conn = $db->getConnection();
$books = $conn->query('SELECT COUNT(*) AS c FROM books');
$book_count = $books ? $books->fetch_assoc()['c'] : 0;
$quizzes = $conn->query('SELECT COUNT(*) AS c FROM quizzes');
$quiz_count = $quizzes ? $quizzes->fetch_assoc()['c'] : 0;
$orders = $conn->query('SELECT COUNT(*) AS c FROM orders WHERE user_id = ' . intval($user_id));
$order_count = $orders ? $orders->fetch_assoc()['c'] : 0;
$last_score = $conn->query('SELECT score, total FROM quiz_results WHERE user_id = ' . intval($user_id) . ' ORDER BY taken_at DESC LIMIT 1');
$score_row = $last_score ? $last_score->fetch_assoc() : null;
// --- Dynamic Activity Feed ---
$activity = [];
$conn = $db->getConnection();
// Last 3 orders
$stmt = $conn->prepare('SELECT b.title, o.order_date FROM orders o JOIN books b ON o.book_id = b.id WHERE o.user_id = ? ORDER BY o.order_date DESC LIMIT 3');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$order_result = $stmt->get_result();
while ($row = $order_result->fetch_assoc()) {
    $activity[] = [
        'type' => 'order',
        'title' => $row['title'],
        'date' => $row['order_date']
    ];
}
$stmt->close();
// Last 3 quiz attempts
$stmt = $conn->prepare('SELECT q.title, qr.taken_at, qr.score, qr.total FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id WHERE qr.user_id = ? ORDER BY qr.taken_at DESC LIMIT 3');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$quiz_result = $stmt->get_result();
while ($row = $quiz_result->fetch_assoc()) {
    $activity[] = [
        'type' => 'quiz',
        'title' => $row['title'],
        'date' => $row['taken_at'],
        'score' => $row['score'],
        'total' => $row['total']
    ];
}
$stmt->close();
// Sort activity by date desc
usort($activity, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});
$activity = array_slice($activity, 0, 5);
// --- Dynamic Recommendations ---
$recommend_books = [];
$recommend_quiz = null;
// Books user hasn't ordered
$stmt = $conn->prepare('SELECT b.id, b.title, b.author FROM books b WHERE b.id NOT IN (SELECT o.book_id FROM orders o WHERE o.user_id = ?) LIMIT 2');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$rec_books_result = $stmt->get_result();
while ($row = $rec_books_result->fetch_assoc()) {
    $recommend_books[] = $row;
}
$stmt->close();
// Quiz user hasn't taken
$stmt = $conn->prepare('SELECT q.id, q.title FROM quizzes q WHERE q.id NOT IN (SELECT qr.quiz_id FROM quiz_results qr WHERE qr.user_id = ?) LIMIT 1');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$rec_quiz_result = $stmt->get_result();
if ($row = $rec_quiz_result->fetch_assoc()) {
    $recommend_quiz = $row;
}
$stmt->close();
// --- Currently Reading Widget ---
$current_reading = null;
$stmt = $conn->prepare('SELECT b.id, b.title, b.cover_image, b.read_file, a.audio_file, p.pages_read, p.last_read FROM progress p JOIN books b ON p.book_id = b.id LEFT JOIN audio_books a ON b.id = a.book_id WHERE p.user_id = ? ORDER BY p.last_read DESC LIMIT 1');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$reading_result = $stmt->get_result();
if ($row = $reading_result->fetch_assoc()) {
    $current_reading = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - OnlineBookstore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/dashboard_custom.css">
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
                <?php if ($role === 'admin' || $role === 'teacher'): ?>
                    <a href="../school/school_dashboard.php"><i class="fas fa-school"></i> School Dashboard</a>
                <?php endif; ?>
            </div>
            <div class="nav-actions">
                <button id="theme-btn" title="Toggle theme"><span id="theme-icon">🌙</span></button>
                <div class="profile-dropdown">
                    <button id="profileBtn"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($username); ?></button>
                    <div class="profile-menu" id="profileMenu">
                        <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                        <form action="logout.php" method="POST"><button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button></form>
                    </div>
                </div>
            </div>
        </nav>
        <main>
            <section class="hero dashboard-hero">
                <div class="hero-text">
                    <h1>Welcome back, <?php echo htmlspecialchars($username); ?>!</h1>
                    <p class="subtitle">Your personalized Online Bookstore dashboard. See your stats, recent activity, and get recommendations.</p>
                    <div class="quick-actions">
                        <a href="quiz.php" class="quick-btn"><i class="fas fa-bolt"></i> Take a Quiz</a>
                        <a href="book_catalog.php" class="quick-btn"><i class="fas fa-search"></i> Browse Books</a>
                        <a href="progress.php" class="quick-btn"><i class="fas fa-book-reader"></i> View Progress</a>
                        <a href="orders.php" class="quick-btn"><i class="fas fa-receipt"></i> Order History</a>
                        <a href="quiz.php" class="quick-btn"><i class="fas fa-plus-circle"></i> Take New Quiz</a>
                    </div>
                </div>
                <div>
                    
                </div>

            </section>
            <section class="dashboard-insights">
                <div class="insight-card">
                    <div class="insight-title">Books in Catalog</div>
                    <div class="insight-value" id="books-count"><?php echo $book_count; ?></div>
                </div>
                <div class="insight-card">
                    <div class="insight-title">Quizzes Available</div>
                    <div class="insight-value" id="quizzes-count"><?php echo $quiz_count; ?></div>
                </div>
                <div class="insight-card">
                    <div class="insight-title">My Orders</div>
                    <div class="insight-value" id="orders-count"><?php echo $order_count; ?></div>
                </div>
                <div class="insight-card">
                    <div class="insight-title">Last Quiz Score</div>
                    <div class="insight-value" id="last-score">
                        <?php echo $score_row ? $score_row['score'] . ' / ' . $score_row['total'] : '--'; ?>
                    </div>
                </div>
            </section>
        <section class="dashboard-features">
            <div class="dashboard-left">
                <div class="recent-activity">
                    <h3><i class="fas fa-history"></i> Recent Activity</h3>
                    <ul id="activity-feed">
                        <?php if (empty($activity)): ?>
                            <li><i class="fas fa-info-circle"></i> No recent activity found.</li>
                        <?php else: ?>
                            <?php foreach ($activity as $item): ?>
                                <?php if ($item['type'] === 'order'): ?>
                                    <li class="activity-order"><i class="fas fa-shopping-cart"></i> Ordered: "<?= htmlspecialchars($item['title']) ?>" <span class="activity-date"><?= htmlspecialchars(date('M d, Y', strtotime($item['date']))) ?></span></li>
                                <?php elseif ($item['type'] === 'quiz'): ?>
                                    <li class="activity-quiz"><i class="fas fa-question-circle"></i> Quiz: "<?= htmlspecialchars($item['title']) ?>" (<?= $item['score'] ?>/<?= $item['total'] ?>) <span class="activity-date"><?= htmlspecialchars(date('M d, Y', strtotime($item['date']))) ?></span></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="notifications">
                    <h3><i class="fas fa-bell"></i> Notifications</h3>
                    <ul id="notifications-feed"></ul>
                </div>
            </div>
            <div class="dashboard-right">
                <div class="recommendations">
                    <h3><i class="fas fa-star"></i> Recommended for You</h3>
                    <ul id="recommendations-list">
                        <?php if (empty($recommend_books) && !$recommend_quiz): ?>
                            <li><i class="fas fa-info-circle"></i> No new recommendations at this time.</li>
                        <?php else: ?>
                            <?php foreach ($recommend_books as $book): ?>
                                <li class="recommend-book"><i class="fas fa-book"></i> "<?= htmlspecialchars($book['title']) ?>" by <?= htmlspecialchars($book['author']) ?> <a href="book_catalog.php" class="rec-action">View</a></li>
                            <?php endforeach; ?>
                            <?php if ($recommend_quiz): ?>
                                <li class="recommend-quiz"><i class="fas fa-question"></i> Try the quiz: "<?= htmlspecialchars($recommend_quiz['title']) ?>" <a href="quiz.php" class="rec-action">Start</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="stats-graphs">
                    <h3><i class="fas fa-chart-bar"></i> Your Stats</h3>
                    <canvas id="statsChart" height="160"></canvas>
                </div>
                <div class="dashboard-widget currently-reading">
                    <h3><i class="fas fa-book-reader"></i> Currently Reading</h3>
                    <?php if ($current_reading): ?>
                        <div class="reading-title">"<?= htmlspecialchars($current_reading['title']) ?>"</div>
                        <div class="reading-pages">Pages read: <?= htmlspecialchars($current_reading['pages_read']) ?></div>
                        <div class="reading-date">Last read: <?= htmlspecialchars(date('M d, Y', strtotime($current_reading['last_read']))) ?></div>
                        <div class="reading-actions" style="margin-top:0.7em;display:flex;gap:0.7em;">
                            <?php if (!empty($current_reading['read_file'])): ?>
                                <a href="../books/<?= htmlspecialchars($current_reading['read_file']) ?>" class="quick-btn" target="_blank"><i class="fas fa-book-open"></i> Read Online</a>
                            <?php endif; ?>
                            <?php if (!empty($current_reading['audio_file'])): ?>
                                <a href="../audio/<?= htmlspecialchars($current_reading['audio_file']) ?>" class="quick-btn" target="_blank"><i class="fas fa-headphones"></i> Listen</a>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="reading-none">You are not currently reading any book.</div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>

        <div class="insights">
            <div class="insight-card">
                <div class="insight-title">Books in Catalog</div>
                <div class="insight-value" id="books-count"><?php echo $book_count; ?></div>
            </div>
            <div class="insight-card">
                <div class="insight-title">Quizzes Available</div>
                <div class="insight-value" id="quizzes-count"><?php echo $quiz_count; ?></div>
            </div>
            <div class="insight-card">
                <div class="insight-title">My Orders</div>
                <div class="insight-value" id="orders-count"><?php echo $order_count; ?></div>
            </div>
            <div class="insight-card">
                <div class="insight-title">Last Quiz Score</div>
                <div class="insight-value" id="last-score">
                    <?php echo $score_row ? $score_row['score'] . ' / ' . $score_row['total'] : '--'; ?>
                </div>
            </div>
        </div>

    </div>
    <script>
        // Animate counters
        function animateValue(id, start, end, duration) {
            let obj = document.getElementById(id);
            let range = end - start;
            let current = start;
            let increment = end > start ? 1 : -1;
            let stepTime = Math.abs(Math.floor(duration / range));
            if (isNaN(stepTime) || !isFinite(stepTime) || stepTime < 10) stepTime = 40;
            let timer = setInterval(function() {
                current += increment;
                obj.textContent = current;
                if (current == end) {
                    clearInterval(timer);
                }
            }, stepTime);
        }
        
    </script>
</body>
</html>
