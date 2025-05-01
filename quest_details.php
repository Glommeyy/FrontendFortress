<?php
session_start();
require 'database.php';

$quest_id = $_GET['id'] ?? null;

if (!$quest_id) {
    echo "Quest not found.";
    exit;
}

// Fetch quest details
$stmt = $pdo->prepare("SELECT * FROM quests WHERE id = ?");
$stmt->execute([$quest_id]);
$quest = $stmt->fetch();

if (!$quest) {
    echo "Quest not found.";
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

// Handle answer submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['answer_file']) && $user_id) {
    $file = $_FILES['answer_file'];
    
    // Check if file is a valid ZIP
    $allowed_mime_types = ['application/zip'];
    $file_mime_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_mime_type, $allowed_mime_types)) {
        echo "<p style='color: red;'>Please upload a valid ZIP file.</p>";
        exit;
    }

    // Optional: Validate file extension (ZIP)
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    if ($file_extension !== 'zip') {
        echo "<p style='color: red;'>Please upload a valid ZIP file with a .zip extension.</p>";
        exit;
    }

    // Move the uploaded file to a directory
    $upload_dir = 'uploads/';  // Change this to your desired directory
    $file_path = $upload_dir . basename($file['name']);
    
    if (move_uploaded_file($file['tmp_name'], $file_path)) {
        // Insert the file information into the database
        $file_size = $file['size'];
        $file_type = $file_mime_type;
    
        $stmt = $pdo->prepare("INSERT INTO quest_answers (quest_id, user_id, file_path, file_size, file_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$quest_id, $user_id, $file_path, $file_size, $file_type]);
    
        // Clear the quest from the user's pending slot
        $stmt = $pdo->prepare("UPDATE quest_slots SET quest_title = NULL, status = 'available' WHERE user_id = ? AND quest_title = ?");
        $stmt->execute([$user_id, $quest['title']]);
    
        // Redirect to homepage with a success message
        $_SESSION['message'] = "Answer submitted successfully!";
        header("Location: homepage.php");
        exit;
    }else {
        echo "<p style='color: red;'>Failed to upload file.</p>";
    }
}
?>

<h1><?php echo htmlspecialchars($quest['title']); ?></h1>
<p><strong>Short Description:</strong> <?php echo htmlspecialchars($quest['short_description']); ?></p>
<p><strong>Full Description:</strong> <?php echo nl2br(htmlspecialchars($quest['full_description'])); ?></p>
<p><strong>Difficulty:</strong> <?php echo htmlspecialchars($quest['difficulty']); ?></p>
<a href="homepage.php">Back to Homepage</a>

<hr>

<?php if ($user_id): ?>
    <h3>Submit Your Answer</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="answer_file" required><br>
        <button type="submit">Submit Answer</button>
    </form>
<?php else: ?>
    <p><em>You must be logged in to submit an answer.</em></p>
<?php endif; ?>
