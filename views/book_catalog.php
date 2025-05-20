<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

$db = new Database();
$conn = $db->getConnection();
$sql = 'SELECT b.*, a.audio_file FROM books b LEFT JOIN audio_books a ON b.id = a.book_id';
$result = $conn->query($sql);
$books = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A modern online bookstore to buy, sell, and manage books.">
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f4da.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="/assets/css/book_catalog.css">
    <link rel="stylesheet" href="/assets/css/dashboard_custom.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <script src="/assets/js/main.js" defer></script>
    <style>
.dashboard-book-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 2rem;
    padding: 2rem;
}

.dashboard-book-card {
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    border: 1px solid #eee;
}

.dashboard-book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(42,82,152,0.15);
}

.book-card-img {
    height: 320px;
    position: relative;
    overflow: hidden;
}

.book-card-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.dashboard-book-card:hover .book-card-img img {
    transform: scale(1.08);
}

.book-card-content {
    padding: 1.5rem;
    background: linear-gradient(to bottom, rgba(255,255,255,0.95), #fff);
    flex: 1;
}

.book-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #2a5298;
    margin-bottom: 0.5rem;
    transition: color 0.3s ease;
}

.dashboard-book-card:hover .book-title {
    color: #1c3561;
}

.book-author {
    color: #6c7a89;
    font-size: 0.95rem;
    margin-bottom: 0.75rem;
    font-style: italic;
}

.book-price {
    font-weight: 700;
    color: #2a5298;
    font-size: 1.2rem;
    margin-bottom: 1rem;
    display: inline-block;
    padding: 0.3rem 0.8rem;
    background: #f0f4f8;
    border-radius: 20px;
}

.book-desc {
    font-size: 0.9rem;
    color: #555;
    line-height: 1.5;
    margin-bottom: 1.5rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.actions {
    display: flex;
    gap: 0.8rem;
    flex-wrap: wrap;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.quick-btn {
    background: #2a5298;
    color: #fff;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s ease;
    flex: 1;
    text-align: center;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.quick-btn:hover {
    background: #1c3561;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #f0f4f8;
    color: #2a5298;
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
    flex: 1;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
}

.btn-secondary:hover {
    background: #dbe4f0;
    transform: translateY(-2px);
}

@media (max-width: 768px) {
    .dashboard-book-list {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        padding: 1rem;
        gap: 1rem;
    }
    
    .book-card-img {
        height: 250px;
    }
}
    </style>
    <script defer src="../assets/js/dashboard_custom.js"></script>
</head>
</head>
<body>
    <div class="dashboard">
        <nav class="main-nav">
            <a href="../index.php" class="nav-logo"><i class="fas fa-house"></i> Home</a>
            <div class="nav-links">
                <a href="book_catalog.php" class="active"><i class="fas fa-book-open"></i> Book Catalog</a>
                <a href="quiz.php"><i class="fas fa-question-circle"></i> Quizzes</a>
                <a href="quiz_results.php"><i class="fas fa-list-ol"></i> Quiz Results</a>
                <a href="orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a>
                <a href="progress.php"><i class="fas fa-chart-line"></i> My Progress</a>
            </div>
            <div class="nav-actions">
                <button id="theme-btn" title="Toggle theme"><span id="theme-icon">🌙</span></button>
                <div class="profile-dropdown">
                    <button id="profileBtn"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></button>
                    <div class="profile-menu" id="profileMenu">
                        <a href="#">Profile</a>
                        <form action="logout.php" method="POST"><button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</button></form>
                    </div>
                </div>
            </div>
        </nav>
        <section class="dashboard-hero">
            <h2>Book Catalog</h2>
        </section>
        <?php if (empty($books)): ?>
            <div class="dashboard-book-list"><p>No books found.</p></div>
        <?php else: ?>
            <div class="dashboard-book-list">
                <?php foreach ($books as $book): ?>
                    <div class="dashboard-book-card">
            <div class="book-card-img">
                <?php if (!empty($book['cover_image'])): ?>
                    <img src="<?php echo htmlspecialchars(strpos($book['cover_image'], 'http') === 0 ? 
                        $book['cover_image'] : '../assets/images/' . $book['cover_image']); ?>" 
                        alt="<?php echo htmlspecialchars($book['title']); ?> Cover">
                <?php else: ?>
                    <img src="../assets/images/default_cover.png" alt="Default Cover">
                <?php endif; ?>
            </div>
            <div class="book-card-content">
                <h3 class="book-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                <p class="book-author">by <?php echo htmlspecialchars($book['author']); ?></p>
                <div class="book-price">$<?php echo number_format($book['price'], 2); ?></div>
                <p class="book-desc"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                <div class="actions">
                    <form method="POST" action="order_book.php" style="flex: 1;">
                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                        <button type="submit" class="quick-btn">
                            <i class="fas fa-shopping-cart"></i> Buy Now
                        </button>
                    </form>
                    <?php if (!empty($book['read_file'])): ?>
                        <a href="../books/<?php echo htmlspecialchars($book['read_file']); ?>" 
                           class="btn-secondary" target="_blank">
                            <i class="fas fa-book-open"></i> Read
                        </a>
                    <?php endif; ?>
                    <?php if (!empty($book['audio_file'])): ?>
                        <a href="../audio/<?php echo htmlspecialchars($book['audio_file']); ?>" 
                           class="btn-secondary" target="_blank">
                            <i class="fas fa-headphones"></i> Listen
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <p><a href="dashboard.php" class="dashboard-link">&larr; Back to Dashboard</a></p>
    </div>
</body>
</html>
