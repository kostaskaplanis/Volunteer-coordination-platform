<?php
include('../test_connection.php');
// Παίρνουμε τις συντεταγμένες από το αίτημα POST
$data = json_decode(file_get_contents("php://input"));

$baseLatitude = $data->baseLatitude;
$baseLongitude = $data->baseLongitude;

// Ελέγχουμε αν υπάρχει ήδη εγγραφή στον πίνακα Map
$checkSql = "SELECT COUNT(*) as count FROM Map";
$result = $conn->query($checkSql);
$row = $result->fetch_assoc();
$count = $row["count"];

if ($count > 0) {
    // Αν υπάρχει εγγραφή, ενημερώνουμε τις συντεταγμένες
    $updateSql = "UPDATE Map SET base_latitude = $baseLatitude, base_longitude = $baseLongitude";
    if ($conn->query($updateSql) === TRUE) {
        $response = array("status" => "success", "message" => "Οι συντεταγμένες ενημερώθηκαν επιτυχώς!");
        echo json_encode($response);
    } else {
        $response = array("status" => "error", "message" => "Σφάλμα κατά την ενημέρωση: " . $conn->error);
        echo json_encode($response);
    }
} else {
    // Αν δεν υπάρχει εγγραφή, εισάγουμε νέα εγγραφή
    $insertSql = "INSERT INTO Map (base_latitude, base_longitude, map_id) VALUES ($baseLatitude, $baseLongitude, 1)";
    if ($conn->query($insertSql) === TRUE) {
        $response = array("status" => "success", "message" => "Οι συντεταγμένες και το MAPID αποθηκεύτηκαν επιτυχώς!");
        echo json_encode($response);
    } else {
        $response = array("status" => "error", "message" => "Σφάλμα κατά την αποθήκευση: " . $conn->error);
        echo json_encode($response);
    }
}

$conn->close();
?>