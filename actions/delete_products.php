<?php
// Include your database connection code here if not included already
include('test_connection.php');

// Check if the product_id is set in the GET request
if (isset($_GET['product_id'])) {
    // Sanitize the input to prevent SQL injection
    $productId = mysqli_real_escape_string($conn, $_GET['product_id']);

    // Perform the deletion query
    $deleteQuery = "DELETE FROM Products WHERE product_id = $productId";

    if (mysqli_query($conn, $deleteQuery)) {
        // Deletion successful
        echo "Product deleted successfully";
    } else {
        // Error in deletion
        echo "Error deleting product: " . mysqli_error($conn);
    }
} else {
    // Handle case where product_id is not set in the GET request
    echo "Product ID not provided";
}

// Close the database connection
mysqli_close($conn);
?>
