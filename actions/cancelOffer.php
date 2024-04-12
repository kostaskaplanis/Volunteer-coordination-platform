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

// Check if offer_id is provided in the POST data
if (isset($_POST['offer_id'])) {
    $offer_id = $_POST['offer_id'];

    // Reset query for CitizenRescuers table
    $resetQuery = "UPDATE CitizenOffers SET status = 'new', date_accepted = NULL, rescuer_id = NULL WHERE offer_id = ?";
    $stmt = $conn->prepare($resetQuery);

    if ($stmt) {
        $stmt->bind_param('i', $offer_id);
        $stmt->execute();
        $stmt->close();

        echo json_encode(['status' => 'success', 'message' => 'offer canceled successfully']);
    } else {
        $errorMessage = $conn->error;
        echo json_encode(['status' => 'error', 'message' => "Failed to prepare statement: $errorMessage"]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request: offer_id not provided']);
}
?>