<?php
session_start();
require "conn.php";

// Initialize the question number if not set
if (!isset($_SESSION['question_number'])) {
    $_SESSION['question_number'] = 1;
}

// Fetch the current question based on the question number
$question_result = $conn->query("SELECT * FROM questions LIMIT " . ($_SESSION['question_number'] - 1) . ", 1");

if ($question_result && $question_result->num_rows > 0) {
    $question = $question_result->fetch_assoc();
    $question_id = $question['id'];

    // Fetch the associated choices
    $choices_result = $conn->query("SELECT * FROM choices WHERE question_id = $question_id");
} else {
    // If no more questions are available, redirect to the results page
    header("Location: results.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $selected_choices = isset($_POST['selected_choices']) ? $_POST['selected_choices'] : [];

    // Store each choice in the user_choices table
    foreach ($selected_choices as $choice_id) {
        $is_correct = $conn->query("SELECT is_correct FROM choices WHERE id = $choice_id")->fetch_assoc()['is_correct'];
        $stmt = $conn->prepare("INSERT INTO user_choices (user_id, question_id, choice_id, is_correct) VALUES (?, ?, ?, ?)");
        $user_id = 1; // Replace with dynamic user ID from session or login system
        $stmt->bind_param("iiii", $user_id, $question_id, $choice_id, $is_correct);
        $stmt->execute();
    }

    // Increment question number and move to the next question or results page
    $_SESSION['question_number']++;
    $total_questions = $conn->query("SELECT COUNT(*) as total FROM questions")->fetch_assoc()['total'];

    if ($_SESSION['question_number'] > $total_questions) {
        header("Location: results.php");
        exit();
    } else {
        header("Location: quiz.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>Quiz</title>
    <style>
        /* Add your existing styles from previous quiz.php example here */
    </style>
</head>
<body>
    <div class="quiz-container">
        <h3>Take the Quiz</h3>
        <form action="quiz.php" method="POST">
            <div class="question-text"><?php echo $question['question_text']; ?></div>
            <div class="choices-container">
                <?php while ($choice = $choices_result->fetch_assoc()): ?>
                    <input type="checkbox" name="selected_choices[]" id="choice-<?php echo $choice['id']; ?>" value="<?php echo $choice['id']; ?>">
                    <label for="choice-<?php echo $choice['id']; ?>"><?php echo $choice['choice_text']; ?></label>
                <?php endwhile; ?>
            </div>
            <button type="submit">Next</button>
        </form>
    </div>
</body>
</html>
