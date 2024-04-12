<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../index.php");
    exit();
}

// Check if the user is not a rescuer
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'rescuer') {
    // Redirect to an unauthorized page or show an error message for non-admin users
    header("Location: ../unauthorized.php");
    exit();
}

// Start the session
require('../test_connection.php');
require('../components/navbar.php');

// Check if the user is logged in
// (You might want to add more authentication and session handling)

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['unload'])) {

    $rescuer_id = $_SESSION['user_id'];
    // Unload all products from RescuerInventory to Products
    $sqlUnloadProducts = "
    UPDATE Products p
    JOIN RescuerInventory ri ON p.product_id = ri.product_id
    SET p.quantity = IFNULL(p.quantity, 0) + ri.quantity
    WHERE ri.rescuer_id = $rescuer_id;
    ";

    $resultUnload = $conn->query($sqlUnloadProducts);

    // Check the result and inform the user
    if ($resultUnload) {
        // Successfully unloaded

        // Delete the unloaded products from RescuerInventory
        $sqlDeleteFromRescuerInventory = "DELETE FROM RescuerInventory WHERE rescuer_id = 1"; // Replace with the actual rescuer's ID
        $resultDelete = $conn->query($sqlDeleteFromRescuerInventory);

        if ($resultDelete) {
            // Successfully deleted from RescuerInventory
            echo "Η εκφόρτωση ολοκληρώθηκε με επιτυχία.";
        } else {
            // Failed to delete from RescuerInventory
            echo "Υπήρξε ένα πρόβλημα κατά τη διαγραφή από τον πίνακα RescuerInventory.";
        }

    } else {
        // Failed to unload
        echo "Υπήρξε ένα πρόβλημα κατά την εκφόρτωση.";
    }

} else {
    // If the form is not submitted, return to the homepage or handle accordingly
    header('Location: index.php');
    exit();
}

// Close the connection
$conn->close();
?>
