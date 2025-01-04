<?php
include 'db.php';

$theme = isset($_COOKIE["theme"]) ? $_COOKIE["theme"] : "light";

function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['theme'])) {
        $theme = $_POST['theme'];
        setcookie("theme", $theme, time() + (86400 * 30), "/");
    } else {
        $username = htmlspecialchars($_POST['username']);
        $email = htmlspecialchars($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $directory = generateRandomString();

        $stmt = $conn->prepare("INSERT INTO users (username, email, password, directory) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $password, $directory);

        try {
            if ($stmt->execute()) {
                header("Location: login.php");
            } else {
                throw new Exception($stmt->error);
            }
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $error = "Username or email already exists.";
            } else {
                $error = "Error: " . $e->getMessage();
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: <?php echo $theme == "dark" ? "#333" : "#f0f0f0"; ?>;
            background-image: radial-gradient(circle, <?php echo $theme == "dark" ? "#444" : "#e0e0e0"; ?> 1px, transparent 1px);
            background-size: 20px 20px;
            color: <?php echo $theme == "dark" ? "#fff" : "#000"; ?>;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            text-align: center;
            background-color: <?php echo $theme == "dark" ? "#444" : "#fff"; ?>;
            padding: 50px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 300px;
            min-height: 450px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        input {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 10px 0;
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
            gap: 10px;
        }
        .message {
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            min-height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Sign Up</h1>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Sign Up</button>
        </form>
        <div class="message <?php echo isset($error) ? 'error' : ''; ?>">
            <?php echo isset($error) ? $error : ''; ?>
        </div>
        <div class="buttons">
            <a href="index.php"><button>Go Back</button></a>
            <a href="login.php"><button>Login</button></a>
        </div>
    </div>
</body>
</html>
