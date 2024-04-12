<?php
require('../test_connection.php');

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['product_id']) && isset($_GET['quantity'])) {
    $productId = $_GET['product_id'];
    $newQuantity = $_GET['quantity'];

    // Ensure the product exists in the database
    $sql_check_product = "SELECT * FROM Products WHERE product_id = $productId";
    $result = $conn->query($sql_check_product);

    if ($result->num_rows > 0) {
        // Calculate the new quantity
        $row = $result->fetch_assoc();
        $currentQuantity = $row['quantity'];
        $newQuantity = ($_GET['addProduct'] === "Αποθήκευση") ? $currentQuantity + $newQuantity : $newQuantity;

        // Ensure the new quantity is not negative
        $newQuantity = max(0, $newQuantity);

        // Update the quantity in the database
        $sql_update_quantity = "UPDATE Products SET quantity = $newQuantity WHERE product_id = $productId";

        if ($conn->query($sql_update_quantity) === TRUE) {
            echo "Η ποσότητα ανανεώθηκε επιτυχώς!";
        } else {
            echo "Σφάλμα κατά την ανανέωση της ποσότητας: " . $conn->error;
        }
    } else {
        echo "Το προϊόν δεν βρέθηκε στη βάση δεδομένων!";
    }
} else {
    echo "Invalid request.";
}

$conn->close();
?>
