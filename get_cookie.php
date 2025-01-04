<?php
if (isset($_COOKIE["user"])) {
    echo "User is: " . $_COOKIE["user"];
} else {
    echo "User is not set.";
}
?>
