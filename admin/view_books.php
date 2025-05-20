<?php
// File: admin/view_books.php

require_once __DIR__ . '/../includes/functions_admin.php';
redirectIfNotAdmin();

// Handle Delete action if present
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    try {
        // First check if book has any orders
        $check_stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM orders WHERE book_id = ?");
        $check_stmt->bind_param('i', $delete_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $has_orders = $result->fetch_assoc()['order_count'] > 0;
        $check_stmt->close();

        if ($has_orders) {
            // Book has orders - set as inactive
            $update_stmt = $conn->prepare("UPDATE books SET active = 0 WHERE id = ?");
            if (!$update_stmt) {
                throw new Exception("Error preparing update statement: " . $conn->error);
            }
            $update_stmt->bind_param('i', $delete_id);
            $update_stmt->execute();
            $update_stmt->close();
            $_SESSION['message'] = "Book has existing orders. Marked as inactive instead of deleting.";
        } else {
            // No orders - safe to delete
            $delete_stmt = $conn->prepare("DELETE FROM books WHERE id = ?");
            $delete_stmt->bind_param('i', $delete_id);
            $delete_stmt->execute();
            $delete_stmt->close();
            $_SESSION['message'] = "Book deleted successfully.";
        }
        
        header('Location: view_books.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header('Location: view_books.php');
        exit;
    }
}

// Search/filter
$search = '';
$whereClause = '';
if (!empty($_GET['search'])) {
    $search = $conn->real_escape_string(trim($_GET['search']));
    $whereClause = "WHERE b.title LIKE '%$search%' OR b.author LIKE '%$search%'";
}

// Modify the books query to include active status
$sql = "
  SELECT 
    b.id, b.title, b.author, c.name AS category, 
    b.price, b.stock_quantity, b.active
  FROM books b
  LEFT JOIN categories c ON b.category_id = c.id
  $whereClause
  ORDER BY b.title ASC
";
$resBooks = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Books • Admin</title>
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
  <?php include __DIR__ . '/../views/includes/header.php'; ?>

  <div class="admin-container">
    <h1 class="admin-title">All Books</h1>

    <form method="GET" action="view_books.php" style="margin-bottom: 1rem;">
      <input type="text" name="search" placeholder="Search by title or author" 
             value="<?= htmlspecialchars($search) ?>">
      <button type="submit" class="btn-admin">Search</button>
      <a href="add_book.php" class="btn-admin" style="margin-left: 1rem;">Add New Book</a>
    </form>

    <table class="admin-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Title</th>
          <th>Author</th>
          <th>Category</th>
          <th>Price ($)</th>
          <th>Stock</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($resBooks->num_rows): ?>
          <?php $i = 1; while ($book = $resBooks->fetch_assoc()): ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= htmlspecialchars($book['title']) ?></td>
              <td><?= htmlspecialchars($book['author']) ?></td>
              <td><?= htmlspecialchars($book['category']) ?></td>
              <td><?= number_format($book['price'], 2) ?></td>
              <td <?= $book['stock_quantity'] < 5 ? 'style="color:#dc3545;"' : '' ?>>
                <?= intval($book['stock_quantity']) ?>
              </td>
              <td>
                <?= $book['active'] ? 'Active' : 'Inactive' ?>
              </td>
              <td>
                <a href="add_book.php?edit_id=<?= $book['id'] ?>" class="btn-admin">Edit</a>
                <a href="view_books.php?delete_id=<?= $book['id'] ?>" class="btn-admin" 
                   onclick="return confirm('Delete this book?');">
                  Delete
                </a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr>
            <td colspan="8">No books found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <?php include __DIR__ . '/../views/includes/footer.php'; ?>
</body>
</html>
