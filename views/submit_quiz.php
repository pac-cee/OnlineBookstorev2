<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
require_once __DIR__ . '/../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['quiz_id']) || !isset($_POST['answers'])) {
    echo '<p>Invalid quiz submission.</p><a href="quiz.php">&larr; Back to Quizzes</a>';
    exit;
}
$user_id = $_SESSION['user_id'];
$quiz_id = intval($_POST['quiz_id']);
$answers = $_POST['answers'];
$db = new Database();
$conn = $db->getConnection();
// Fetch correct answers
$sql = 'SELECT id, correct_answer FROM quiz_questions WHERE quiz_id = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $quiz_id);
$stmt->execute();
$result = $stmt->get_result();
$correct = [];
while ($row = $result->fetch_assoc()) {
    $correct[$row['id']] = $row['correct_answer'];
}
$stmt->close();
// Grade quiz
$score = 0;
$total = count($correct);
foreach ($correct as $qid => $corr_idx) {
    if (isset($answers[$qid]) && $answers[$qid] == $corr_idx) {
        $score++;
    }
}
// Save result
$stmt = $conn->prepare('INSERT INTO quiz_results (user_id, quiz_id, score, total, taken_at) VALUES (?, ?, ?, ?, NOW())');
$stmt->bind_param('iiii', $user_id, $quiz_id, $score, $total);
$stmt->execute();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Result - OnlineBookstore</title>
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
    <div class="quiz-result">
        <h2>Quiz Submitted</h2>
        <p>Your score: <strong><?php echo $score; ?> / <?php echo $total; ?></strong></p>
        <a href="quiz.php">&larr; Back to Quizzes</a>
    </div>
</body>
</html>
