<?php
session_start();
require 'database.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "You must be logged in to view answers.";
    exit;
}

// Fetch the quests uploaded by the logged-in user
$stmt = $pdo->prepare("SELECT * FROM quests WHERE user_id = ?");
$stmt->execute([$user_id]);
$quests = $stmt->fetchAll();

if (!$quests) {
    echo "You have not uploaded any quests.";
    exit;
}

// Fetch answers for the quests uploaded by the user, along with the uploader's name and difficulty
$stmt = $pdo->prepare("
    SELECT qa.*, q.title AS quest_title, u.name AS uploader_name, q.difficulty
    FROM quest_answers qa
    JOIN quests q ON qa.quest_id = q.id
    JOIN users u ON qa.user_id = u.id
    WHERE q.user_id = ?
");
$stmt->execute([$user_id]);
$answers = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Submitted Answers</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your styles here -->
    <style>
        .card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 16px;
            margin: 16px;
            width: 300px;
            display: inline-block;
            vertical-align: top;
        }

        .card-header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .card-body {
            margin-bottom: 16px;
        }

        .card-footer {
            text-align: right;
        }

        a {
            text-decoration: none;
            color: #007bff;
        }

        .uploader-info {
            font-style: italic;
            color: #555;
        }
    </style>
</head>
<body>

<h1>ANSWERs</h1>

<?php if ($answers): ?>
    <div class="answers-container">
        <?php foreach ($answers as $answer): ?>
            <div class="card">
                <div class="card-header">
                    Quest: <?php echo htmlspecialchars($answer['quest_title']); ?>
                </div>
                <div class="card-body">
                    <p><strong>Uploaded by:</strong> <?php echo htmlspecialchars($answer['uploader_name']); ?></p>
                    <p><strong>File Size:</strong> <?php echo number_format($answer['file_size'] / 1024, 2) . ' KB'; ?></p>
                </div>
                <div class="card-footer">
                    <a href="<?php echo htmlspecialchars($answer['file_path']); ?>" download>Download Answer</a><br><br>

                    <?php if (!$answer['is_accepted']): ?>
                        <form action="choose_answer.php" method="GET">
                            <input type="hidden" name="answer_id" value="<?php echo $answer['id']; ?>">
                            <input type="hidden" name="quest_id" value="<?php echo $answer['quest_id']; ?>">
                            <input type="hidden" name="difficulty" value="<?php echo htmlspecialchars($answer['difficulty']); ?>">
                            <button type="submit">Choose This Answer</button>
                        </form>
                    <?php else: ?>
                        <p style="color: green;"><strong>✔ This answer was selected.</strong></p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <p>No answers have been submitted for your quests yet.</p>
<?php endif; ?>

<a href="homepage.php">Back to Homepage</a>
</body>
</html>
