<?php
$db_host = 'help-parking-db'; // This should match your MySQL service name in docker-compose
$db_user = 'programmerdatch';
$db_pass = 'password';
$db_name = 'parking_system';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to handle special characters properly
$conn->set_charset("utf8mb4");
?>