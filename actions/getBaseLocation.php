<?php
include('../test_connection.php');
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
} 
$sql = "SELECT MAX(map_id) AS max_id FROM Map";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Αν υπάρχουν αποτελέσματα, επιστροφή του μέγιστου ID ως JSON
    $row = $result->fetch_assoc();
    $maxId = $row['max_id'];

    // Τώρα, μπορείτε να εκτελέσετε ένα δεύτερο ερώτημα για την ανάκτηση των συντεταγμένων με βάση το μέγιστο ID
    $sqlCoordinates = "SELECT base_latitude, base_longitude FROM Map WHERE map_id = $maxId";
    $resultCoordinates = $conn->query($sqlCoordinates);

    if ($resultCoordinates->num_rows > 0) {
        // Αν υπάρχουν αποτελέσματα, επιστροφή των συντεταγμένων ως JSON
        $rowCoordinates = $resultCoordinates->fetch_assoc();
        $response = array(
            'baseLatitude' => $rowCoordinates['base_latitude'],
            'baseLongitude' => $rowCoordinates['base_longitude']
        );
        echo json_encode($response);
    } else {
        // Αν δεν υπάρχουν αποτελέσματα, επιστροφή κενού JSON
        echo json_encode(array());
    }
} else {
    // Αν δεν υπάρχουν αποτελέσματα για το μέγιστο ID, επιστροφή κενού JSON
    echo json_encode(array());
}

// Κλείσιμο σύνδεσης
$conn->close();

?>