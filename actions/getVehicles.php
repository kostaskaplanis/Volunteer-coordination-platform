<?php
include('../test_connection.php');

// Εκτέλεση ερωτήματος SQL για την ανάκτηση οχημάτων και δεδομένων από τον πίνακα RescuerInventory
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Αν δεν είναι συνδεδεμένος, επιστροφή κενού JSON
    echo json_encode([]);
    exit();
}


$rescuer_id = $_SESSION['user_id'];
$sql = "SELECT Vehicles.*, Map.base_latitude, Map.base_longitude, RescuerInventory.product_id, RescuerInventory.quantity
        FROM Vehicles
        JOIN Map ON Vehicles.map_id = Map.map_id
        LEFT JOIN RescuerInventory ON Vehicles.VehicleID = RescuerInventory.vehicle_id
        ";
        
$stmt = $conn->prepare($sql);

$stmt->execute();
$result = $stmt->get_result();

// Επιστροφή δεδομένων ως JSON
$data = array();
while ($row = $result->fetch_assoc()) {
    // Τυχαίες συντεταγμένες εντός κύκλου 5 χιλιομέτρων από τη βάση
    $randomLat = $row['base_latitude'] + (rand() / getrandmax() - 0.5) * 0.09;
    $randomLng = $row['base_longitude'] + (rand() / getrandmax() - 0.5) * 0.09;

    $row['vehicle_latitude'] = $randomLat;
    $row['vehicle_longitude'] = $randomLng;

    $data[] = $row;
}

// Εκτέλεση ερωτήματος SQL για την ανάκτηση πληροφοριών από τον πίνακα RescuerInventory
$sqlRescuerInventory = "SELECT Products.product_id, Products.name AS product_name,  RescuerInventory.quantity AS rescuer_quantity
                        FROM Products
                        LEFT JOIN RescuerInventory ON Products.product_id = RescuerInventory.product_id AND RescuerInventory.rescuer_id = ?
                        WHERE RescuerInventory.rescuer_id IS NOT NULL;";
$stmtRescuerInventory = $conn->prepare($sqlRescuerInventory);
$stmtRescuerInventory->bind_param("i", $rescuer_id);
$stmtRescuerInventory->execute();
$resultRescuerInventory = $stmtRescuerInventory->get_result();

// Προσθήκη δεδομένων από τον πίνακα RescuerInventory στον ίδιο πίνακα δεδομένων
while ($rowRescuerInventory = $resultRescuerInventory->fetch_assoc()) {
    $data[] = $rowRescuerInventory;
}

// Εμφάνιση δεδομένων ως JSON
echo json_encode($data);

// Κλείσιμο σύνδεσης
$stmt->close();
$stmtRescuerInventory->close();
$conn->close();
?>
