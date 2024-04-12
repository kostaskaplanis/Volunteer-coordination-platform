<?php 
include('../test_connection.php');
// Ερώτημα SQL για ανάκτηση των συντεταγμένων
$sql = "SELECT base_latitude, base_longitude FROM Map WHERE map_id = 1"; // Ανάλογα με τον τρόπο που έχετε οργανώσει τα δεδομένα

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Επιστροφή των δεδομένων σε μορφή JSON
    $row = $result->fetch_assoc();
    echo json_encode($row);
} else {
    // Καμία εγγραφή δεν βρέθηκε
    echo "Καμία εγγραφή δεν βρέθηκε.";
}

// Κλείσιμο σύνδεσης
$conn->close();
?>