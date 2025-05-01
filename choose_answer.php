<?php
session_start();
require 'database.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "You must be logged in to choose an answer.";
    exit;
}

if (isset($_GET['answer_id'], $_GET['quest_id'], $_GET['difficulty'])) {
    $answer_id = $_GET['answer_id'];
    $quest_id = $_GET['quest_id'];
    $difficulty = strtolower($_GET['difficulty']);  // Convert to lowercase

    // Validate difficulty
    if (!in_array($difficulty, ['easy', 'medium', 'hard'])) {
        echo "Invalid difficulty.";
        exit;
    }

    // Fetch the answer's user ID
    $stmt = $pdo->prepare("SELECT user_id FROM quest_answers WHERE id = ?");
    $stmt->execute([$answer_id]);
    $answer = $stmt->fetch();

    if (!$answer) {
        echo "Answer not found.";
        exit;
    }

    $answer_user_id = $answer['user_id'];

    // Map difficulty to experience points and gold reward
    switch ($difficulty) {
        case 'easy':
            $exp_gain = 50;
            $gold_gain = 10;
            break;
        case 'medium':
            $exp_gain = 100;
            $gold_gain = 20;
            break;
        case 'hard':
            $exp_gain = 200;
            $gold_gain = 40;
            break;
        default:
            echo "Invalid difficulty.";
            exit;
    }

    // Call the stored procedure to update the user's experience and level
    $stmt = $pdo->prepare("
        CALL update_user_exp_and_level(:user_id, :exp_gain)
    ");
    $stmt->execute([
        ':user_id' => $answer_user_id,
        ':exp_gain' => $exp_gain,
    ]);

    // Update the answerer with the gold reward (optional)
    $stmt = $pdo->prepare("
        UPDATE user_profiles
        SET gold = gold + :gold_gain
        WHERE user_id = :user_id
    ");
    $stmt->execute([
        ':gold_gain' => $gold_gain,
        ':user_id' => $answer_user_id,
    ]);

    // Mark the answer as selected
    $stmt = $pdo->prepare("UPDATE quest_answers SET is_selected = 1 WHERE id = ?");
    $stmt->execute([$answer_id]);

    // Delete other answers for this quest
    $stmt = $pdo->prepare("DELETE FROM quest_answers WHERE quest_id = ? AND id != ?");
    $stmt->execute([$quest_id, $answer_id]);

    // Delete the quest itself after marking the answer
    $stmt = $pdo->prepare("DELETE FROM quests WHERE id = ?");
    $stmt->execute([$quest_id]);

    // Redirect to homepage.php after completing the process
    header("Location: homepage.php");
    exit;  // Always call exit after header redirection
} else {
    echo "Invalid request.";
}
?>
