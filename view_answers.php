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
  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      display: flex;
      height: 100vh;
    }

    .sidebar {
      width: 250px;
      background-color: #2b0a37;
      color: white;
      padding: 20px;
      overflow-y: auto;
    }

    .sidebar button {
      width: 100%;
      margin-bottom: 10px;
      padding: 10px;
      border: none;
      background-color: #d4a45f;
      color: black;
      cursor: pointer;
      font-weight: bold;
    }

    .main-content {
      flex-grow: 1;
      background: linear-gradient(to bottom, #1b002d, #e1c6fa);
      padding: 20px;
      overflow-y: auto;
      color: white;
    }

    .answer-section {
      display: none;
    }

    .card-grid {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
    }

    .card {
      background-color: #f4d7aa;
      border: 2px solid #000;
      border-radius: 8px;
      padding: 16px;
      width: 300px;
      color: black;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .card-footer {
      margin-top: 10px;
    }

    .card-footer form {
      margin-top: 10px;
    }

    a {
      color: #0056b3;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }
  </style>
  <script>
    function showAnswers(questId) {
      const allAnswerSections = document.querySelectorAll('.answer-section');
      allAnswerSections.forEach(section => {
        section.style.display = section.dataset.questId == questId ? 'block' : 'none';
      });
    }
  </script>
</head>
<body>

<div class="sidebar">
  <h3>Your Quests</h3>
  <?php foreach ($quests as $quest): ?>
    <button onclick="showAnswers(<?php echo $quest['id']; ?>)">
      <?php echo htmlspecialchars($quest['title']); ?>
    </button>
  <?php endforeach; ?>
</div>

<div class="main-content">
  <h2>Answers</h2>

  <?php
  // Group answers by quest_id
  $groupedAnswers = [];
  foreach ($answers as $answer) {
    $groupedAnswers[$answer['quest_id']][] = $answer;
  }

  foreach ($quests as $quest):
    $questId = $quest['id'];
    ?>
    <div class="answer-section" data-quest-id="<?php echo $questId; ?>">
      <?php if (!empty($groupedAnswers[$questId])): ?>
        <div class="card-grid">
          <?php foreach ($groupedAnswers[$questId] as $answer): ?>
            <div class="card">
              <div>
                <h4><?php echo htmlspecialchars($answer['quest_title']); ?></h4>
                <p><strong>Uploaded by:</strong> <?php echo htmlspecialchars($answer['uploader_name']); ?></p>
                <p><strong>File Size:</strong> <?php echo number_format($answer['file_size'] / 1024, 2); ?> KB</p>
              </div>

              <div class="card-footer">
                <a href="<?php echo htmlspecialchars($answer['file_path']); ?>" download>Download Answer</a>

                <?php if (!$answer['is_accepted']): ?>
                  <form action="choose_answer.php" method="GET">
                    <input type="hidden" name="answer_id" value="<?php echo $answer['id']; ?>">
                    <input type="hidden" name="quest_id" value="<?php echo $questId; ?>">
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
        <p>No answers submitted yet for this quest.</p>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>

</body>
</html>
