<?php
// Include the database connection file and start the session
include 'db.php';
session_start();

// Set the response content type to JSON
header('Content-Type: application/json');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$image_id = $data['id'];

// Fetch the image file path from the database
$stmt = $conn->prepare("SELECT filepath FROM images WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $image_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();
$stmt->close();

// Check if the image exists and delete it
if ($image) {
    $filepath = $image['filepath'];
    $stmt = $conn->prepare("DELETE FROM images WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $image_id, $user_id);
    if ($stmt->execute()) {
        $stmt->close();
        if (unlink($filepath)) {
            echo json_encode(['success' => true]);
        } else {
            // Reinsert the image record if file deletion fails
            $stmt = $conn->prepare("INSERT INTO images (id, user_id, filepath) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $image_id, $user_id, $filepath);
            $stmt->execute();
            $stmt->close();
            echo json_encode(['success' => false, 'message' => 'Failed to delete file: ' . $filepath]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete image from database: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Image not found']);
}
?>
