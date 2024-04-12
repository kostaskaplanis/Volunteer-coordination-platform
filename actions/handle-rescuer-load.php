
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

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if products are selected
    if (isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {
        $rescuer_id = $_SESSION['user_id']; // Replace this with the actual rescuer's ID

        // Insert or update offer in the RescuerInventory table using ON DUPLICATE KEY UPDATE
        $sqlInsertInventory = "INSERT INTO RescuerInventory (rescuer_id, product_id, quantity)
                               VALUES ";

        $params = array(); // Array to store the parameters for the prepared statement

        // Construct the SQL query for each selected product
        foreach ($_POST['selected_products'] as $product_id) {
            $quantity = $_POST['quantity'][$product_id];
            $sqlInsertInventory .= "($rescuer_id, $product_id, $quantity),";
        }

        // Remove the last ',' and execute the query
        $sqlInsertInventory = rtrim($sqlInsertInventory, ',');

        // Append the ON DUPLICATE KEY UPDATE part
        $sqlInsertInventory .= " ON DUPLICATE KEY UPDATE quantity = VALUES(quantity)";

        // Execute the query
        $result = $conn->query($sqlInsertInventory);

        // Update the Products table with the new quantity
        $sqlUpdateProducts = "UPDATE Products SET quantity = GREATEST(0, quantity - ?) WHERE product_id = ?";
        $stmtUpdateProducts = $conn->prepare($sqlUpdateProducts);

        // Iterate through selected products and update the Products table
        foreach ($_POST['selected_products'] as $product_id) {
            $quantity = $_POST['quantity'][$product_id];

            // Execute the UPDATE
            $stmtUpdateProducts->bind_param('ii', $quantity, $product_id);
            $stmtUpdateProducts->execute();
        }

        if ($result && $stmtUpdateProducts) {
            echo "<div style='text-align: center; margin-top: 10px; font-size: 24px; font-weight: bold;'>Η φόρτωση προϊόντων υποβλήθηκε με επιτυχία.</div>";
            
        } else {
            echo "Υπήρξε ένα πρόβλημα κατά την φόρτωση των προϊόντων.";
        }
    } else {
        echo "Επιλέξτε τουλάχιστον ένα προϊόν.";
    }
} else {
    // If the form is not submitted, return to the homepage
    header('Location: index.php');
    exit();
}

// Close the connection
$conn->close();
?>
