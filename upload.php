<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$response = ['success' => false];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT directory FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$directory = $user['directory'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['images'])) {
    $uploadDir = 'uploads/' . $directory . '/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
        $fileName = basename($_FILES['images']['name'][$key]);
        $filePath = $uploadDir . $fileName;

        if (move_uploaded_file($tmpName, $filePath)) {
            $sql = "INSERT INTO images (filepath, user_id) VALUES ('$filePath', $user_id)";
            if ($conn->query($sql) === TRUE) {
                $response['success'] = true;
            }
        }
    }
}

echo json_encode($response);
?>
