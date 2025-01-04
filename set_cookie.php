<?php
setcookie("user", "John Doe", time() + 3600, "/");

header("Location: get_cookie.php");
exit();
?>
