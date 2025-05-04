<?php
session_start();
include 'database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['quest_id'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$quest_id = $_POST['quest_id'];

// Find the first available slot
$stmt = $pdo->prepare("SELECT slot_number FROM quest_slots WHERE user_id = ? AND status = 'available' LIMIT 1");
$stmt->execute([$user_id]);
$slot = $stmt->fetch(PDO::FETCH_ASSOC);

if ($slot) {
    $stmt = $pdo->prepare("UPDATE quest_slots SET quest_title = (SELECT title FROM quests WHERE id = ?), status = 'occupied' WHERE user_id = ? AND slot_number = ?");
    $stmt->execute([$quest_id, $user_id, $slot['slot_number']]);
}

header("Location: homepage.php");
exit();
?>