<?php
include('../test_connection.php');
$data = json_decode(file_get_contents("php://input"));

// Αν είναι διαθέσιμα τα απαιτούμενα δεδομένα
if (isset($data->vehicleID) && isset($data->distanceFromBase)) {
    $vehicleID = $data->vehicleID;
    $distanceFromBase = $data->distanceFromBase;

    // Ενημέρωση της απόστασης στη βάση δεδομένων
    $sql = "UPDATE RescuerInventory SET DistanceFromBase = $distanceFromBase WHERE VehicleID = $vehicleID";

    if ($conn->query($sql) === TRUE) {
        $response = array('status' => 'success', 'message' => 'Επιτυχής ενημέρωση απόστασης.');
        echo json_encode($response);
    } else {
        $response = array('status' => 'error', 'message' => 'Σφάλμα κατά την ενημέρωση απόστασης: ' . $conn->error);
        echo json_encode($response);
    }
} else {
    $response = array('status' => 'error', 'message' => 'Λείπουν απαραίτητα δεδομένα.');
    echo json_encode($response);
}

// Κλείσιμο σύνδεσης με τη βάση δεδομένων
$conn->close();
?>