<?php
// Get the same root path calculation from header
$currentPath = $_SERVER['SCRIPT_NAME'];
$rootPath = '../';
if (strpos($currentPath, '/views/includes/') !== false) {
    $rootPath = '../../';
}

// Common JavaScript files
echo "<script src=\"{$rootPath}assets/js/main.js\" defer></script>";

// Add page-specific JavaScript files if they exist
if (isset($pageScripts) && is_array($pageScripts)) {
    foreach ($pageScripts as $script) {
        echo "\n<script src=\"{$rootPath}assets/js/{$script}.js\" defer></script>";
    }
}
?>
