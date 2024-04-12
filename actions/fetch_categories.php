<?php
require('../test_connection.php');

// Fetch categories
$sqlCategories = "SELECT * FROM Categories";
$resultCategories = $conn->query($sqlCategories);

// Store categories in an array
$categories = array();
while ($rowCategory = $resultCategories->fetch_assoc()) {
    $categories[] = $rowCategory;
}

// Return categories as JSON
header('Content-Type: application/json');
echo json_encode($categories);

$conn->close();
?>
