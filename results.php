<?php
session_start();
require "conn.php";

$user_id = 1; // Replace with dynamic user ID from session or login system

// Handle restart request
if (isset($_POST['restart_quiz'])) {
    // Delete the user's previous answers
    if (!$conn->query("DELETE FROM user_choices WHERE user_id = $user_id")) {
        die("Error deleting user choices: " . $conn->error);
    }

    // Reset session question number
    $_SESSION['question_number'] = 1;

    // Redirect to the first question
    header("Location: quiz.php");
    exit();
}

// Fetch the user's answers along with the question text and choice text
$query = "
    SELECT uc.*, q.question_text, c.choice_text, c.is_correct AS choice_correct
    FROM user_choices uc
    INNER JOIN questions q ON uc.question_id = q.id
    INNER JOIN choices c ON uc.choice_id = c.id
    WHERE uc.user_id = $user_id
";

$user_choices_result = $conn->query($query);

if (!$user_choices_result) {
    die("Error fetching user choices: " . $conn->error);
}

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

        <div class="user-choices">
            <?php while ($row = $user_choices_result->fetch_assoc()): ?>
                <div class="question">
                    <h4>Question: <?php echo htmlspecialchars($row['question_text']); ?></h4>
                    <p>Your Answer: <?php echo htmlspecialchars($row['choice_text']); ?></p>
                    <p class="<?php echo $row['is_correct'] ? 'correct' : 'incorrect'; ?>">
                        <?php echo $row['is_correct'] ? 'Correct' : 'Incorrect'; ?>
                    </p>
                    <?php if (!$row['is_correct']): ?>
                        <p class="correct-answer">Correct Answer: 
                            <?php
                                // Fetch the correct choice for this question
                                $correct_choice_query = "
                                    SELECT choice_text FROM choices 
                                    WHERE question_id = {$row['question_id']} AND is_correct = 1
                                ";
                                $correct_choice_result = $conn->query($correct_choice_query);
                                if ($correct_choice_result && $correct_choice_result->num_rows > 0) {
                                    $correct_choice = $correct_choice_result->fetch_assoc();
                                    echo htmlspecialchars($correct_choice['choice_text']);
                                }
                            ?>
                        </p>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>

        <form method="POST" action="">
            <button type="submit" name="restart_quiz" class="restart-button">Restart Quiz</button>
        </form>
    </div>
</body>
</html>
