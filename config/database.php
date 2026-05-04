<?php
$host = 'localhost';
$db   = 'inventory_system_v2';
$user = 'root';
$pass = '';  

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>