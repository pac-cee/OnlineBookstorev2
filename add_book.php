<?php
require_once __DIR__ . '/config/db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $price = $_POST['price'] ?? 0;
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->prepare("INSERT INTO books (title, author, price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $title, $author, $price);
    if ($stmt->execute()) {
        $msg = "Book added successfully!";
    } else {
        $msg = "Error: " . $stmt->error;
    }
    $stmt->close();
}
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
    <link rel="stylesheet" href="/assets/css/book_catalog.css">
    <link rel="stylesheet" href="/assets/css/dashboard_custom.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
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
</head>
<body class="fade-in">
    <nav>
        <a href="index.php"><i class="fas fa-house"></i> Home</a>
        <a href="add_book.php" class="active"><i class="fas fa-plus-circle"></i> Add Book</a>
        <a href="view_books.php"><i class="fas fa-book"></i> View Books</a>
        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
    </nav>
    <main class="book-management">
        <div class="book-form">
            <h1>Add New Book</h1>
            <?php if(isset($msg)) echo "<div class='message " . (strpos($msg, 'success') !== false ? 'success' : 'error') . "'>$msg</div>"; ?>
            <form method="post" id="addBookForm">
                <div class="form-group">
                    <label for="title">Book Title</label>
                    <input type="text" id="title" name="title" required>
                </div>
                <div class="form-group">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" required>
                </div>
                <div class="form-group">
                    <label for="price">Price ($)</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                <button type="submit" class="auth-button">Add Book <i class="fas fa-plus"></i></button>
            </form>
        </div>
    </main>
</body>
</html>
