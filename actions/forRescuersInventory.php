<?php
require('../test_connection.php');

// Έλεγχος αν ο χρήστης είναι συνδεδεμένος
session_start();
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Αν δεν είναι συνδεδεμένος, μπορείτε να ανακατευθύνετε ή να εμφανίσετε ένα μήνυμα σφάλματος
    header("Location: ../index.php");
    exit();
}

// Εκτέλεση ερωτήματος SQL για την ανάκτηση των προϊόντων που έχει πάρει ο διασώστης
$rescuer_id = $_SESSION['user_id'];

$sqlRescuerInventory = "SELECT Products.product_id, Products.name AS product_name,  rescuerinventory.quantity AS rescuer_quantity
                        FROM Products
                        LEFT JOIN rescuerinventory ON Products.product_id = rescuerinventory.product_id AND rescuerinventory.rescuer_id = ?
                        WHERE rescuerinventory.rescuer_id IS NOT NULL;";

$stmt = $conn->prepare($sqlRescuerInventory);
$stmt->bind_param("i", $rescuer_id);
$stmt->execute();
$resultRescuerInventory = $stmt->get_result();

// Αποθηκεύουμε τα προϊόντα του διασώστη σε έναν πίνακα
$rescuerInventory = array();
while ($rowRescuerInventory = $resultRescuerInventory->fetch_assoc()) {
    $rescuerInventory[] = $rowRescuerInventory;
}

// Επιστροφή δεδομένων ως JSON
echo json_encode($rescuerInventory);

// Κλείσιμο σύνδεσης
$stmt->close();
$conn->close();
?>