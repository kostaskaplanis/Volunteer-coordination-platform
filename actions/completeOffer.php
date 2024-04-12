<?php
session_start();
include('../test_connection.php');

// Check if the user is not logged in or not a rescuer
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'rescuer') {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Check if offer_id is provided in the POST data
if (isset($_POST['offer_id'])) {
    $rescuer_id = $_SESSION['user_id'];
    $offer_id = $_POST['offer_id'];

    // Log the received data
    error_log('Received Data: ' . print_r($_POST, true));

    // Update the CitizenOffers table
    $updateQuery = "UPDATE CitizenOffers SET rescuer_id = ?, status = 'completed', date_completed = CURRENT_TIMESTAMP WHERE offer_id = ?";
    $stmt = $conn->prepare($updateQuery);

    if ($stmt) {
        $stmt->bind_param('ii', $rescuer_id, $offer_id);
        $stmt->execute();
        $stmt->close();

        // Send a response back to the client
        echo json_encode(['status' => 'success', 'message' => 'offer marked as complete successfully']);
    } else {
        // Handle any errors with the prepared statement
        $errorMessage = $conn->error; // Get the error message
        echo json_encode(['status' => 'error', 'message' => "Failed to prepare statement: $errorMessage"]);
    }
} else {
    // If offer_id is not set in the POST data
    echo json_encode(['status' => 'error', 'message' => 'Invalid offer: offer_id not provided']);
}
?>