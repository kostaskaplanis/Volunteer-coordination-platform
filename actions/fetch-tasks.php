<?php
// Include your database connection code here
include('test_connection.php');

// Επιλογή αιτημάτων από τον πίνακα CitizenRequests
$sqlRequests = "SELECT * FROM CitizenRequests WHERE status = 'Pending'";
$resultRequests = $conn->query($sqlRequests);

// Επιλογή προσφορών από τον πίνακα CitizenOffers
$sqlOffers = "SELECT * FROM CitizenOffers WHERE status = 'Pending'";
$resultOffers = $conn->query($sqlOffers);

// Αποτελέσματα σε μορφή JSON
$response = array(
    'requests' => array(),
    'offers' => array()
);

// Ανάκτηση δεδομένων αιτημάτων
if ($resultRequests->num_rows > 0) {
    while($row = $resultRequests->fetch_assoc()) {
        $response['requests'][] = $row;
    }
}

// Ανάκτηση δεδομένων προσφορών
if ($resultOffers->num_rows > 0) {
    while($row = $resultOffers->fetch_assoc()) {
        $response['offers'][] = $row;
    }
}

// Κλείσιμο σύνδεσης
$conn->close();

// Επιστροφή αποτελεσμάτων σε μορφή JSON
header('Content-Type: application/json');
echo json_encode($response);
?>
