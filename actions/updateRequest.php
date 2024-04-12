<?php
session_start();
include('../test_connection.php');

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Respond with an error message if the user is not logged in
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Check if the user is not a rescuer
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'rescuer') {
    // Respond with an error message for non-rescuer users
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit();
}

// Check if request_id is provided in the POST data
if (isset($_POST['request_id'])) {
    $rescuer_id = $_SESSION['user_id'];
    $request_id = $_POST['request_id'];

    // Update the CitizenRequests table
    $updateQuery = "UPDATE CitizenRequests SET rescuer_id = ?, status = 'in progress', date_accepted = CURRENT_TIMESTAMP WHERE request_id = ?";
    $stmt = $conn->prepare($updateQuery);

    if ($stmt) {
        $stmt->bind_param('ii', $rescuer_id, $request_id);
        $stmt->execute();
        $stmt->close();

        // Select the updated date_accepted from the database
        $selectQuery = "SELECT date_accepted FROM CitizenRequests WHERE request_id = ?";
        $selectStmt = $conn->prepare($selectQuery);

        if ($selectStmt) {
            $selectStmt->bind_param('i', $request_id);
            $selectStmt->execute();
            $selectStmt->bind_result($updatedDateAccepted);
            $selectStmt->fetch();
            $selectStmt->close();

            // Send a response back to the client with the updated date_accepted
            echo json_encode(['status' => 'success', 'message' => 'Request updated successfully', 'date_accepted' => $updatedDateAccepted]);
        } else {
            // Handle any errors with the select statement
            $errorMessage = $conn->error; // Get the error message
            echo json_encode(['status' => 'error', 'message' => "Failed to prepare select statement: $errorMessage"]);
        }
    } else {
        // Handle any errors with the update statement
        $errorMessage = $conn->error; // Get the error message
        echo json_encode(['status' => 'error', 'message' => "Failed to prepare update statement: $errorMessage"]);
    }
} else {
    // If request_id is not set in the POST data
    echo json_encode(['status' => 'error', 'message' => 'Invalid request: request_id not provided']);
}
?>
