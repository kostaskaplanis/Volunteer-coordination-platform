<?php
$servername = "localhost";
$username = "kostas";
$password = "kostas1234";
$database = "FYSIKES_KATASTROFES";
// Create connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 
?>