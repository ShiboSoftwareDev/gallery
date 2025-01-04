<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$theme = isset($_COOKIE["theme"]) ? $_COOKIE["theme"] : "light";

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['delete'])) {
        $stmt = $conn->prepare("SELECT filepath FROM images WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($image = $result->fetch_assoc()) {
            unlink($image['filepath']);
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM images WHERE user_id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("SELECT directory FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->bind_param("i", $user_id);
        if ($stmt->execute()) {
            $user_directory = $user['directory'];
            $dir_path = __DIR__ . "/uploads/" . $user_directory;
            if (is_dir($dir_path)) {
                array_map('unlink', glob("$dir_path/*.*"));
                rmdir($dir_path);
            }
            session_destroy();
            header("Location: index.php");
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['email'])) {
        $email = htmlspecialchars($_POST['email']);
        $stmt = $conn->prepare("UPDATE users SET email=? WHERE id=?");
        $stmt->bind_param("si", $email, $user_id);
        if ($stmt->execute()) {
            $success = "Email updated successfully";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } elseif (isset($_POST['theme'])) {
        $theme = $_POST['theme'];
        setcookie("theme", $theme, time() + (86400 * 30), "/");
    } elseif (isset($_POST['logout'])) {
        session_destroy();
        header("Location: login.php");
        exit();
    }
}

$stmt = $conn->prepare("SELECT email FROM users WHERE id=?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: <?php echo $theme == 'dark' ? '#333' : '#f0f0f0'; ?>;
            background-image: radial-gradient(circle, <?php echo $theme == 'dark' ? '#444' : '#e0e0e0'; ?> 1px, transparent 1px);
            background-size: 20px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            background-color: <?php echo $theme == 'dark' ? '#444' : '#fff'; ?>;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .input-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        button {
            width: 100px;
            padding: 10px 20px;
            color: #fff;
            background-color: #007bff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        button:hover {
            background-color: #0056b3;
        }
        .buttons {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            color: #fff;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .success {
            color: #28a745;
        }
        .error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>User Page</h1>
        <div class="message <?php echo isset($success) ? 'success' : (isset($error) ? 'error' : ''); ?>">
            <?php echo isset($success) ? $success : (isset($error) ? $error : ''); ?>
        </div>
        <form method="POST">
            <div class="input-group">
                <input type="email" name="email" placeholder="Email" value="<?php echo $user['email']; ?>" required>
                <button type="submit" style="padding: 10.5px 5px;">Update Email</button>
            </div>
        </form>
        <form method="POST">
            <button type="submit" name="delete" onclick="return confirm('Are you sure you want to delete your account?');">Delete Account</button>
        </form>
        <form method="POST">
            <button type="submit" name="logout">Logout</button>
        </form>
        <div class="buttons">
            <a href="gallery.php"><button>Go Back</button></a>
        </div>
    </div>
</body>
</html>
