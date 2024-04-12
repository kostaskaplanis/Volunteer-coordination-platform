<?php
session_start();
include('../test_connection.php');

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

// Check if the user is not a rescuer
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'rescuer') {
    header("Location: ../unauthorized.php");
    exit();
}

// Check if request_id is provided in the POST data
if (isset($_POST['request_id'])) {
    $request_id = $_POST['request_id'];

    // Reset query for CitizenRescuers table
    $resetQuery = "UPDATE CitizenRequests SET status = 'new', date_accepted = NULL, rescuer_id = NULL WHERE request_id = ?";
    $stmt = $conn->prepare($resetQuery);

    if ($stmt) {
        $stmt->bind_param('i', $request_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'Request canceled successfully']);
    } else {
        $errorMessage = $conn->error;
        echo json_encode(['status' => 'error', 'message' => "Failed to prepare statement: $errorMessage"]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request: request_id not provided']);
}
?>
