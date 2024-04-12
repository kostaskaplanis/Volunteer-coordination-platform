<?php
require('../test_connection.php');

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Αν δεν είναι συνδεδεμένος, μπορείτε να ανακατευθύνετε ή να εμφανίσετε ένα μήνυμα σφάλματος
    header("Location: ../index.php");
    exit();
}

$sqlRescuerInventory = "SELECT 
                            V.VehicleName,
                            P.product_id,
                            P.name AS product_name,
                            C.category_name,
                            RI.quantity
                        FROM Vehicles V
                        LEFT JOIN RescuerInventory RI ON V.VehicleID = RI.rescuer_id
                        LEFT JOIN Products P ON RI.product_id = P.product_id
                        LEFT JOIN Categories C ON P.category_id = C.category_id
                        WHERE V.rescuer_id IS NOT NULL;";

$stmt = $conn->prepare($sqlRescuerInventory);
$stmt->execute();
$resultRescuerInventory = $stmt->get_result();

// Αποθηκεύουμε τα δεδομένα σε έναν πίνακα
$rescuerData = array();
while ($rowRescuerData = $resultRescuerInventory->fetch_assoc()) {
    $rescuerData[] = $rowRescuerData;
}

// Επιστροφή δεδομένων ως JSON
echo json_encode($rescuerData);

// Κλείσιμο σύνδεσης
$stmt->close();
$conn->close();
?>