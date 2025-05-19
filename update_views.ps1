# PowerShell script to update view files with correct paths
$viewFiles = @(
    "book_catalog.php",
    "dashboard.php",
    "login.php",
    "orders.php",
    "profile.php",
    "progress.php",
    "quiz.php",
    "quiz_results.php",
    "register.php",
    "take_quiz.php"
)

$headerTemplate = @'
<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    $pageTitle = '$PAGE_TITLE';
    $pageStyles = [$PAGE_STYLES];
    $pageScripts = [$PAGE_SCRIPTS];
    require_once __DIR__ . '/includes/header.php';
    ?>
</head>
'@

# Create the necessary directories if they don't exist
New-Item -ItemType Directory -Force -Path "views/includes"

# Create a mapping of page titles, styles, and scripts
$pageConfig = @{
    "book_catalog.php" = @{
        "title" = "Book Catalog - Online Bookstore"
        "styles" = "'book_catalog'"
        "scripts" = "'book_management'"
    }
    "dashboard.php" = @{
        "title" = "Dashboard - Online Bookstore"
        "styles" = "'dashboard', 'dashboard_custom'"
        "scripts" = "'dashboard_custom'"
    }
    "login.php" = @{
        "title" = "Login - Online Bookstore"
        "styles" = "'auth'"
        "scripts" = "'auth'"
    }
    "orders.php" = @{
        "title" = "My Orders - Online Bookstore"
        "styles" = "'orders'"
        "scripts" = "'book_management'"
    }
    "profile.php" = @{
        "title" = "My Profile - Online Bookstore"
        "styles" = "'profile'"
        "scripts" = "'profile'"
    }
    "progress.php" = @{
        "title" = "My Progress - Online Bookstore"
        "styles" = "'progress'"
        "scripts" = "'progress'"
    }
    "quiz.php" = @{
        "title" = "Quizzes - Online Bookstore"
        "styles" = "'quiz'"
        "scripts" = "'quiz'"
    }
    "quiz_results.php" = @{
        "title" = "Quiz Results - Online Bookstore"
        "styles" = "'quiz_results'"
        "scripts" = "'quiz'"
    }
    "register.php" = @{
        "title" = "Register - Online Bookstore"
        "styles" = "'auth'"
        "scripts" = "'auth'"
    }
    "take_quiz.php" = @{
        "title" = "Take Quiz - Online Bookstore"
        "styles" = "'quiz'"
        "scripts" = "'quiz'"
    }
}

foreach ($file in $viewFiles) {
    $filePath = "views/$file"
    if (Test-Path $filePath) {
        $content = Get-Content $filePath -Raw
        
        # Get the configuration for this file
        $config = $pageConfig[$file]
        
        # Create the new header content
        $newHeader = $headerTemplate.Replace('$PAGE_TITLE', $config.title)
        $newHeader = $newHeader.Replace('$PAGE_STYLES', $config.styles)
        $newHeader = $newHeader.Replace('$PAGE_SCRIPTS', $config.scripts)
        
        # Replace the existing header with the new one
        $pattern = '(?s)<!DOCTYPE html>.*?</head>'
        $content = $content -replace $pattern, $newHeader
        
        # Add footer include before closing body tag
        if ($content -match '</body>') {
            $content = $content -replace '</body>', "    <?php require_once __DIR__ . '/includes/footer.php'; ?>`n</body>"
        }
        
        # Save the updated content
        $content | Set-Content $filePath -Force
        Write-Host "Updated $file"
    }
}

Write-Host "All view files have been updated with correct paths"
