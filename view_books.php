<?php
require_once __DIR__ . '/config/db.php';
$db = new Database();
$conn = $db->getConnection();
$result = $conn->query("SELECT * FROM books");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Books - Online Bookstore</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="assets/css/book_management.css">
    <script src="assets/js/book_management.js" defer></script>
</head>
<body class="fade-in">
    <nav>
        <a href="index.php"><i class="fas fa-house"></i> Home</a>
        <a href="add_book.php"><i class="fas fa-plus-circle"></i> Add Book</a>
        <a href="view_books.php" class="active"><i class="fas fa-book"></i> View Books</a>
        <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
        <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
    </nav>
    <main class="book-management">
        <h1>Book Collection</h1>
        <div class="books-table-container">
            <table class="books-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['id']) ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= htmlspecialchars($row['author']) ?></td>
                        <td>$<?= number_format(htmlspecialchars($row['price']), 2) ?></td>
                        <td class="book-actions">
                            <button class="edit-btn"><i class="fas fa-edit"></i> Edit</button>
                            <button class="delete-btn"><i class="fas fa-trash"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
        </table>
    </main>
</body>
</html>
