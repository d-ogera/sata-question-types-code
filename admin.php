<?php
require "conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question_text = $_POST['question_text'];
    $correct_choices = $_POST['correct_choices'];
    $choices = $_POST['choices'];
    $correct_choices_count = count(array_filter($correct_choices));

    $stmt = $conn->prepare("INSERT INTO questions (question_text, correct_choices_count) VALUES (?, ?)");
    $stmt->bind_param("si", $question_text, $correct_choices_count);
    $stmt->execute();
    $question_id = $stmt->insert_id;
    $stmt->close();

    foreach ($choices as $index => $choice_text) {
        $is_correct = in_array($index, $correct_choices) ? 1 : 0;
        $stmt = $conn->prepare("INSERT INTO choices (question_id, choice_text, is_correct) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $question_id, $choice_text, $is_correct);
        $stmt->execute();
    }
    echo "<div class='success-message'>Question added successfully!</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>Admin - Add Question</title>
</head>
<body>
    <div class="admin-container">
        <h3>Add a New Question</h3>
        <form method="POST">
            <label for="question_text">Question Text:</label>
            <textarea name="question_text" id="question_text" required></textarea>
            
            <label>Choices:</label>
            <div class="choices-container" id="choices">
                <input type="text" name="choices[]" placeholder="Choice 1" required>
                <input type="checkbox" name="correct_choices[]" value="0"> Correct<br>
                
                <input type="text" name="choices[]" placeholder="Choice 2" required>
                <input type="checkbox" name="correct_choices[]" value="1"> Correct<br>
                
                <input type="text" name="choices[]" placeholder="Choice 3" required>
                <input type="checkbox" name="correct_choices[]" value="2"> Correct<br>
                
                <input type="text" name="choices[]" placeholder="Choice 4" required>
                <input type="checkbox" name="correct_choices[]" value="3"> Correct<br>
            </div>
            <button type="submit">Add Question</button>
        </form>
    </div>
</body>
</html>
