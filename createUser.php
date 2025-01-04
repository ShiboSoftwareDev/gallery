<?php
include 'db.php';

$username = 'one';
$password = password_hash('two', PASSWORD_DEFAULT);

$sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
if ($conn->query($sql) === TRUE) {
    echo "User created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>