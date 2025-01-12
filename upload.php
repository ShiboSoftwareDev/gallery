<?php
// Include the database connection file and start the session
include 'db.php';
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize response array
$response = ['success' => false];
$user_id = $_SESSION['user_id'];

// Fetch the user's directory from the database
$stmt = $conn->prepare("SELECT directory FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$directory = $user['directory'];

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images'])) {
    $uploadDir = 'uploads/' . $directory . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // Loop through each uploaded file
    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['images']['name'][$key]);
        $filePath = $uploadDir . $fileName;

        // Move the uploaded file to the user's directory and insert file path into the database
        if (move_uploaded_file($tmpName, $filePath)) {
            $sql = "INSERT INTO images (filepath, user_id) VALUES ('$filePath', $user_id)";
            if ($conn->query($sql) === TRUE) {
                $response['success'] = true;
            }
        }
    }
}

// Return the response as JSON
echo json_encode($response);
?>
