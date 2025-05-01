<?php
session_start();
require 'database.php';

$quest_id = $_GET['id'] ?? null;

if (!$quest_id) {
    echo "Quest not found.";
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "You must be logged in to view this page.";
    exit;
}

// Fetch quest info to check ownership
$stmt = $pdo->prepare("SELECT * FROM quests WHERE id = ?");
$stmt->execute([$quest_id]);
$quest = $stmt->fetch();

if (!$quest) {
    echo "Quest not found.";
    exit;
}

if ($quest['user_id'] != $user_id) {
    echo "You are not authorized to view answers for this quest.";
    exit;
}

// Fetch submitted answers
$stmt = $pdo->prepare("SELECT qa.answer, qa.created_at, u.name 
                       FROM quest_answers qa 
                       JOIN users u ON qa.user_id = u.id 
                       WHERE qa.quest_id = ?");
$stmt->execute([$quest_id]);
$answers = $stmt->fetchAll();
?>

<h1>Answers for "<?php echo htmlspecialchars($quest['title']); ?>"</h1>
<a href="homepage.php">Back to Homepage</a>
<hr>

<?php
if ($answers) {
    foreach ($answers as $ans) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin:10px 0;'>";
        echo "<strong>" . htmlspecialchars($ans['name']) . ":</strong><br>";
        echo nl2br(htmlspecialchars($ans['answer'])) . "<br>";
        echo "<small>Submitted on " . $ans['created_at'] . "</small>";
        echo "</div>";
    }
} else {
    echo "<p>No answers submitted yet.</p>";
}
?>
