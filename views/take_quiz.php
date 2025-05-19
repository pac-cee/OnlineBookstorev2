<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

if (!isset($_GET['quiz_id'])) {
    echo '<p>Quiz not specified.</p><a href="quiz.php">&larr; Back to Quizzes</a>';
    exit;
}
$quiz_id = intval($_GET['quiz_id']);
$db = new Database();
$conn = $db->getConnection();
// Fetch quiz info
$qsql = 'SELECT * FROM quizzes WHERE id = ?';
$qstmt = $conn->prepare($qsql);
$qstmt->bind_param('i', $quiz_id);
$qstmt->execute();
$qres = $qstmt->get_result();
$quiz = $qres->fetch_assoc();
if (!$quiz) {
    echo '<p>Quiz not found.</p><a href="quiz.php">&larr; Back to Quizzes</a>';
    exit;
}
// Fetch quiz questions
$sql = 'SELECT * FROM quiz_questions WHERE quiz_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$questions = [];
while ($row = $result->fetch_assoc()) {
    $questions[] = $row;
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Take Quiz</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A modern online bookstore to buy, sell, and manage books.">
    <link rel="icon" type="image/png" href="https://cdn.jsdelivr.net/gh/twitter/twemoji@14.0.2/assets/72x72/1f4da.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="/assets/css/quiz.css">
    <link rel="stylesheet" href="/assets/css/dashboard.css">
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
    <div class="quiz-container">
        <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
        <p><?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
        <?php if (empty($questions)): ?>
            <p>No questions found for this quiz.</p>
            <a href="quiz.php">&larr; Back to Quizzes</a>
        <?php else: ?>
            <form method="POST" action="submit_quiz.php">
                <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
                <?php foreach ($questions as $idx => $q): ?>
                    <div class="question">
                        <h4>Q<?php echo $idx+1; ?>. <?php echo htmlspecialchars($q['question']); ?></h4>
                        <div class="choices">
                            <?php
                            $choices = json_decode($q['choices'], true);
                            foreach ($choices as $choice_idx => $choice): ?>
                                <label>
                                    <input type="radio" name="answers[<?php echo $q['id']; ?>]" value="<?php echo $choice_idx; ?>" required>
                                    <?php echo htmlspecialchars($choice); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <button type="submit" class="btn-primary">Submit Quiz</button>
            </form>
        <?php endif; ?>
        <div style="margin-top:2rem;"><a href="quiz.php">&larr; Back to Quizzes</a></div>
    </div>
</body>
</html>
