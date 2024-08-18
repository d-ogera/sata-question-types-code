<?php
session_start();
require "conn.php";

$user_id = 1; // Replace with dynamic user ID from session or login system

// Handle restart request
if (isset($_POST['restart_quiz'])) {
    // Delete the user's previous answers
    $conn->query("DELETE FROM user_choices WHERE user_id = $user_id");

    // Reset session question number
    $_SESSION['question_number'] = 1;

    // Redirect to the first question
    header("Location: quiz.php");
    exit();
}

// Fetch the user's answers
$user_choices_result = $conn->query("SELECT * FROM user_choices WHERE user_id = $user_id");

// Calculate score
$total_questions = $conn->query("SELECT COUNT(DISTINCT question_id) as total FROM user_choices WHERE user_id = $user_id")->fetch_assoc()['total'];
$correct_answers = $conn->query("SELECT COUNT(*) as correct FROM user_choices WHERE user_id = $user_id AND is_correct = 1")->fetch_assoc()['correct'];
$incorrect_answers = $total_questions - $correct_answers;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>Quiz Results</title>
</head>
<body>
    <div class="results-container">
        <h3>Quiz Results</h3>
        <p>Total Questions: <?php echo $total_questions; ?></p>
        <p>Correct Answers: <?php echo $correct_answers; ?></p>
        <p>Incorrect Answers: <?php echo $incorrect_answers; ?></p>

        <form method="POST" action="">
            <button type="submit" name="restart_quiz" class="restart-button">Restart Quiz</button>
        </form>
    </div>
</body>
</html>
