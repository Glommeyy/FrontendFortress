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
    <title>Quest Submissions</title>
    <link rel="stylesheet" href="css/inbox.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxanium:wght@200..800&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxanium:wght@200..800&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Michroma&family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oxanium:wght@200..800&family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap');
    </style>
</head>

<body>
    <div class="navbar">
        <h3>FRONTEND FORTRESS</h3>
        <div id="navbtn">
        <a style="text-decoration: none;" href="homepage.php">
            <div id="homebtn" onclick="goHome()">
                <img src="pic/home 1.png" alt="Homepage Button">
                <span>HOME</span>
            </div>
        </a>
            <a style="text-decoration: none;" href="logout.php">
                <div id="logoutbtn" onclick="logmeout()">
                    <img src="pic/Exit.png" alt="Logout Button">
                    <span>LOGOUT</span>
                </div>
            </a>
        </div>
    </div>
    <div class="container">
        <div class="left-pane">
        <?php foreach ($quests as $quest): ?>
            <div class="button" onclick="showContent(<?php echo $quest['id']; ?>, event)"><?php echo htmlspecialchars($quest['title']); ?></div>
        <?php endforeach; ?>
            
        </div>
        <div class="right-pane" id="right-pane">
            <!-- Dynamic content will be loaded here -->
        </div>
    </div>



<script>
  const allAnswers = <?php echo json_encode($answers); ?>;
</script>
    <script src="js/inbox.js"></script>
</body>

</html>
