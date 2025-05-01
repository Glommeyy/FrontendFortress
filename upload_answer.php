<?php

// upload_answer.php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['answer_file'])) {
    $quest_id = $_POST['quest_id'];
    $user_id = $_SESSION['user_id'];
    $answer_file = $_FILES['answer_file'];

    // Check if file is a valid zip file
    $file_type = mime_content_type($answer_file['tmp_name']);
    if ($file_type !== 'application/zip') {
        $_SESSION['error'] = "Please upload a valid ZIP file.";
        header("Location: homepage.php");
        exit();
    }

    // Handle file upload
    $upload_dir = 'uploads/answers/';
    $file_name = 'answer_' . $user_id . '_' . $quest_id . '.zip';
    $file_path = $upload_dir . $file_name;

    if (move_uploaded_file($answer_file['tmp_name'], $file_path)) {
        try {
            // Store the uploaded answer in the database
            $stmt = $pdo->prepare("INSERT INTO quest_answers (user_id, quest_id, file_path) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $quest_id, $file_path]);

            // Redirect after successful upload
            header("Location: homepage.php");
            exit();
        } catch (PDOException $e) {
            $_SESSION['error'] = "Database error: " . $e->getMessage();
            header("Location: homepage.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Error uploading file.";
        header("Location: homepage.php");
        exit();
    }
}

?>