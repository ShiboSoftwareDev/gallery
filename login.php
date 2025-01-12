<?php
// Include the database connection file and start the session
include 'db.php';
session_start();

// Check if a theme cookie is set, otherwise default to "light"
$theme = isset($_COOKIE["theme"]) ? $_COOKIE["theme"] : "light";

// Handle form submission for login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // Prepare and execute the SQL statement to fetch the user
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Verify the password and set session variables if valid
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['logged_in'] = true;
        header("Location: gallery.php");
    } else {
        $error = "Invalid username or password";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <style>
        /* Set the body styles based on the selected theme */
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
        <h1>Login</h1>
        <!-- Form to handle login -->
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <!-- Display error message if login fails -->
        <div class="message <?php echo isset($error) ? 'error' : ''; ?>">
            <?php echo isset($error) ? $error : ''; ?>
        </div>
        <!-- Buttons to go back to the index page or sign up page -->
        <div class="buttons">
            <a href="index.php"><button>Go Back</button></a>
            <a href="signup.php"><button>Sign Up</button></a>
        </div>
    </div>
</body>
</html>
