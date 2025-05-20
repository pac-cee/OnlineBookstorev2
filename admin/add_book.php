<?php
// File: admin/add_book.php

require_once __DIR__ . '/../includes/functions_admin.php';
redirectIfNotAdmin();

$message = '';
$error   = '';

// Handle Add or Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title            = trim($_POST['title']);
    $author           = trim($_POST['author']);
    $price            = floatval($_POST['price']);
    $original_price   = floatval($_POST['original_price']);
    $description      = trim($_POST['description']);
    $cover_image      = trim($_POST['cover_image']); // assume URL or path
    $category_id      = intval($_POST['category_id']);
    $isbn             = trim($_POST['isbn']);
    $publisher        = trim($_POST['publisher']);
    $publication_date = trim($_POST['publication_date']);
    $pages            = intval($_POST['pages']);
    $language         = trim($_POST['language']);
    $stock_quantity   = intval($_POST['stock_quantity']);
    $featured         = isset($_POST['featured']) ? 1 : 0;

    // If edit_id present → update; else → insert
    if (!empty($_POST['edit_id'])) {
        // UPDATE flow
        $edit_id = intval($_POST['edit_id']);
        $stmt = $conn->prepare("
            UPDATE books
            SET title = ?, author = ?, price = ?, original_price = ?, description = ?, cover_image = ?,
                category_id = ?, isbn = ?, publisher = ?, publication_date = ?, pages = ?, language = ?,
                stock_quantity = ?, featured = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            'ssddssisssiiiii',
            $title,
            $author,
            $price,
            $original_price,
            $description,
            $cover_image,
            $category_id,
            $isbn,
            $publisher,
            $publication_date,
            $pages,
            $language,
            $stock_quantity,
            $featured,
            $edit_id
        );
        if ($stmt->execute()) {
            $message = 'Book updated successfully.';
        } else {
            $error = 'Error updating book: ' . $conn->error;
        }
    } else {
        // INSERT flow
        $stmt = $conn->prepare("
            INSERT INTO books
            (title, author, price, original_price, description, cover_image,
             category_id, isbn, publisher, publication_date, pages, language,
             stock_quantity, featured)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param(
            'ssddssisssiiii',
            $title,
            $author,
            $price,
            $original_price,
            $description,
            $cover_image,
            $category_id,
            $isbn,
            $publisher,
            $publication_date,
            $pages,
            $language,
            $stock_quantity,
            $featured
        );
        if ($stmt->execute()) {
            $message = 'Book added successfully.';
        } else {
            $error = 'Error adding book: ' . $conn->error;
        }
    }
}

// If editing, fetch existing data
$editData = null;
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = $conn->prepare("SELECT * FROM books WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $edit_id);
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

// Fetch categories for the dropdown
$catRes = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $editData ? 'Edit Book' : 'Add Book' ?> • Admin</title>
  <link rel="stylesheet" href="/css/index.css">
  <link rel="stylesheet" href="/css/dashboard.css">
  <link rel="stylesheet" href="/admin/css/admin.css">
</head>
<body>
  <?php include __DIR__ . '/../views/includes/header.php'; ?>

  <div class="admin-container">
    <h1 class="admin-title"><?= $editData ? 'Edit Book' : 'Add New Book' ?></h1>

    <?php if ($message): ?>
      <div class="alert-admin alert-success"><?= htmlspecialchars($message) ?></div>
    <?php elseif ($error): ?>
      <div class="alert-admin alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="add_book.php<?= $editData ? '?edit_id=' . $editData['id'] : '' ?>" method="POST" class="form-admin">
      <?php if ($editData): ?>
        <input type="hidden" name="edit_id" value="<?= intval($editData['id']) ?>">
      <?php endif; ?>

      <label for="title">Title*</label>
      <input type="text" name="title" id="title" required
             value="<?= $editData ? htmlspecialchars($editData['title']) : '' ?>">

      <label for="author">Author*</label>
      <input type="text" name="author" id="author" required
             value="<?= $editData ? htmlspecialchars($editData['author']) : '' ?>">

      <label for="price">Price (e.g. 19.99)*</label>
      <input type="number" step="0.01" name="price" id="price" required
             value="<?= $editData ? htmlspecialchars($editData['price']) : '' ?>">

      <label for="original_price">Original Price</label>
      <input type="number" step="0.01" name="original_price" id="original_price"
             value="<?= $editData ? htmlspecialchars($editData['original_price']) : '' ?>">

      <label for="description">Description</label>
      <textarea name="description" id="description" rows="4"><?= $editData ? htmlspecialchars($editData['description']) : '' ?></textarea>

      <label for="cover_image">Cover Image URL</label>
      <input type="text" name="cover_image" id="cover_image"
             value="<?= $editData ? htmlspecialchars($editData['cover_image']) : '' ?>">

      <label for="category_id">Category*</label>
      <select name="category_id" id="category_id" required>
        <option value="">-- Select Category --</option>
        <?php while ($cat = $catRes->fetch_assoc()): ?>
          <option value="<?= $cat['id'] ?>"
            <?= $editData && $editData['category_id'] == $cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['name']) ?>
          </option>
        <?php endwhile; ?>
      </select>

      <label for="isbn">ISBN</label>
      <input type="text" name="isbn" id="isbn"
             value="<?= $editData ? htmlspecialchars($editData['isbn']) : '' ?>">

      <label for="publisher">Publisher</label>
      <input type="text" name="publisher" id="publisher"
             value="<?= $editData ? htmlspecialchars($editData['publisher']) : '' ?>">

      <label for="publication_date">Publication Date</label>
      <input type="date" name="publication_date" id="publication_date"
             value="<?= $editData ? htmlspecialchars($editData['publication_date']) : '' ?>">

      <label for="pages">Pages</label>
      <input type="number" name="pages" id="pages"
             value="<?= $editData ? htmlspecialchars($editData['pages']) : '' ?>">

      <label for="language">Language</label>
      <input type="text" name="language" id="language"
             value="<?= $editData ? htmlspecialchars($editData['language']) : '' ?>">

      <label for="stock_quantity">Stock Quantity*</label>
      <input type="number" name="stock_quantity" id="stock_quantity" required
             value="<?= $editData ? htmlspecialchars($editData['stock_quantity']) : '0' ?>">

      <label>
        <input type="checkbox" name="featured" <?= $editData && $editData['featured'] ? 'checked' : '' ?>>
        Featured Book
      </label>

      <button type="submit" class="btn-admin"><?= $editData ? 'Update Book' : 'Add Book' ?></button>
    </form>
  </div>

  <?php include __DIR__ . '/../views/includes/footer.php'; ?>
</body>
</html>
