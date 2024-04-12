<?php
include('../test_connection.php');

// Ερώτημα SQL για ανάκτηση του διασώστη με βάση την κατάσταση "In Progress"
$sql = "SELECT Vehicles.*, CitizenRequests.*, Citizens.latitude AS citizen_latitude, Citizens.longitude AS citizen_longitude
        FROM Vehicles
        JOIN CitizenRequests ON Vehicles.rescuer_id = CitizenRequests.rescuer_id
        JOIN Citizens ON CitizenRequests.citizen_id = Citizens.citizen_id
        WHERE CitizenRequests.status = 'In Progress'";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Επιστροφή όλων των αποτελεσμάτων
    $data = array();

    while($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Μετατροπή των δεδομένων σε μορφή JSON
    $json_data = json_encode($data);
    echo $json_data;
} else {
    // Δεν υπάρχουν αποτελέσματα
    echo "No rescuer found for requests in progress.";
}

$conn->close();
?>