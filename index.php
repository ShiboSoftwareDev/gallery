<?php
// Check if a theme cookie is set, otherwise default to "light"
$theme = isset($_COOKIE["theme"]) ? $_COOKIE["theme"] : "light";

// Handle form submission to change the theme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['theme'])) {
    $theme = $_POST['theme'];
    setcookie("theme", $theme, time() + (86400 * 30), "/");
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery Landing Page</title>
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
        }
        h1 {
            margin-bottom: 20px;
        }
        a {
            display: inline-block;
            margin: 10px;
            width: 100px;
            padding: 10px 20px;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        a:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome to the Gallery</h1>
        <!-- Links to login and signup pages -->
        <a href="login.php">Login</a>
        <a href="signup.php">Sign Up</a>
        <!-- Form to select and submit the theme -->
        <form method="POST">
            <label for="theme">Select Theme:</label>
            <select name="theme" id="theme" onchange="this.form.submit()">
                <option value="light" <?php echo $theme == 'light' ? 'selected' : ''; ?>>Light</option>
                <option value="dark" <?php echo $theme == 'dark' ? 'selected' : ''; ?>>Dark</option>
            </select>
        </form>
    </div>
</body>
</html>
