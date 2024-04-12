<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../index.php");
    exit();
}

// Check if the user is not a rescuer
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'rescuer') {
    // Redirect to an unauthorized page or show an error message for non-admin users
    header("Location: ../unauthorized.php");
    exit();
}

// Include your database connection file
require('../test_connection.php');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Decode the JSON data from the request body
    $requestData = json_decode(file_get_contents('php://input'), true);
    file_put_contents('debug_log.txt', print_r($requestData, true)); // Log received data

    // Extract data from the decoded JSON
    $productName = $requestData['product_name'];
    $rescuer_id = $_SESSION['user_id'];
    $updatedQuantity = $requestData['updatedQuantity'];

    // Retrieve product_id based on product name
    $stmt = $conn->prepare("SELECT product_id FROM Products WHERE name = ?");
    $stmt->bind_param('s', $productName);
    $stmt->execute();
    $stmt->bind_result($product_id);

    // Fetch the result
    $stmt->fetch();
    $stmt->close();

    // Update RescuerInventory based on the offer case
    $sqlInsertInventory = "INSERT INTO RescuerInventory (rescuer_id, product_id, quantity)
                               VALUES(?,?,?)
                               ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)";
    

    try {
        // Prepare the SQL statement using the correct connection object
        $stmt = $conn->prepare($sqlInsertInventory);

        // Bind parameters using bind_param for MySQLi
        $stmt->bind_param('iii',  $rescuer_id, $product_id, $updatedQuantity,);

        // Execute the prepared statement
        $stmt->execute();

        // Return a success message
        echo json_encode(['status' => 'success', 'message' => 'RescuerInventory updated successfully']);
    } catch (Exception $e) {
        // Handle database errors
        echo json_encode(['status' => 'error', 'message' => 'Error updating RescuerInventory']);
    }
} else {
    // Return an error for non-POST requests
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}

?>