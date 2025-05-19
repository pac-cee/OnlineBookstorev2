<?php
// Calculate the relative path to the root directory based on the current file path
$currentPath = $_SERVER['SCRIPT_NAME'];
$rootPath = '../';

// Handle nested includes
if (strpos($currentPath, '/views/includes/') !== false) {
    $rootPath = '../../';
}

// Page title fallback
$pageTitle = $pageTitle ?? 'Online Bookstore';

// Common meta tags and basic CSS
$commonHead = <<<HTML
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A modern online bookstore to buy, sell, and manage books.">
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f4da.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{$rootPath}styles.css">
HTML;

echo $commonHead;

// Add page-specific CSS files if they exist
if (isset($pageStyles) && is_array($pageStyles)) {
    foreach ($pageStyles as $style) {
        echo "\n    <link rel=\"stylesheet\" href=\"{$rootPath}assets/css/{$style}.css\">";
    }
}
?>
