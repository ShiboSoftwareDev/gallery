<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['theme'])) {
    $theme = $_POST['theme'];
    setcookie("theme", $theme, time() + (86400 * 30), "/");
}

header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
