<?php
require_once __DIR__ . '/config/db.php';
// Add more includes as needed
?>
<!DOCTYPE html>
<html lang="en">
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
    <link rel="stylesheet" href="assets/css/book_catalog.css">
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <script src="assets/js/main.js" defer></script>
</head>
<body class="fade-in">
    <?php session_start(); ?>
    <nav>
        <a href="index.php" class="active"><i class="fas fa-house"></i> Home</a>
        <a href="views/book_catalog.php"><i class="fas fa-book-open"></i> Book Catalog</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="views/dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="views/quiz.php"><i class="fas fa-question-circle"></i> Quizzes</a>
            <a href="views/quiz_results.php"><i class="fas fa-list-ol"></i> Quiz Results</a>
            <a href="views/orders.php"><i class="fas fa-shopping-cart"></i> My Orders</a>
            <a href="views/progress.php"><i class="fas fa-chart-line"></i> My Progress</a>
            <a href="views/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="views/register.php"><i class="fas fa-user-plus"></i> Register</a>
            <a href="views/login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
        <?php endif; ?>
    </nav>
    <main>
        <section class="hero">
            <div class="hero-text">
                <h1>Welcome to the Online Bookstore</h1>
                <p class="hero-description">Your one-stop shop for discovering, buying, and selling books online. Whether you're a passionate reader, a student, or a book collector, our platform connects you with thousands of titles and a vibrant community.</p>
                <ul class="feature-list">
                    <li><i class="fas fa-check-circle"></i> Browse a vast collection of books across genres</li>
                    <li><i class="fas fa-check-circle"></i> Add and manage your own books for sale</li>
                    <li><i class="fas fa-check-circle"></i> Simple registration and secure login</li>
                    <li><i class="fas fa-check-circle"></i> Fast, user-friendly, and mobile-ready</li>
                </ul>
                <a href="views/register.php" class="btn btn-primary">Get Started <i class="fas fa-arrow-right"></i></a>
            </div>
            <div class="hero-img">
                <img src="https://plus.unsplash.com/premium_photo-1669652639337-c513cc42ead6?q=80&w=1974&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D" alt="Bookstore Illustration" style="border-radius:12px;box-shadow:0 4px 24px #6c63ff22;">
            </div>
        </section>
        <section>
            <h2 class="section-title">Why Choose Us?</h2>
            <div class="features">
                <div class="feature-card">
                    <i class="fas fa-book-reader"></i>
                    <h3>Huge Selection</h3>
                    <p>Find books from bestsellers to rare finds, all in one place.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-user-shield"></i>
                    <h3>Secure & Private</h3>
                    <p>Your data is protected and your transactions are safe.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-mobile-alt"></i>
                    <h3>Mobile Friendly</h3>
                    <p>Enjoy a seamless experience on any device, anytime.</p>
                </div>
                <div class="feature-card">
                    <i class="fas fa-headset"></i>
                    <h3>Support</h3>
                    <p>Get help fast with our friendly customer support team.</p>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
