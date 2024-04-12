<?php
session_start();
include('../test_connection.php');

if (isset($_POST['loadFromUrl'])) {
    $jsonUrl = $_POST['jsonUrl'];
    $jsonContent = file_get_contents($jsonUrl);

    // Decode JSON data
    $data = json_decode($jsonContent, true);

    // Insert categories
    foreach ($data['categories'] as $category) {
        $category_id = isset($category['id']) ? $category['id'] : null;
        $category_name = $category['category_name'];

        // Check if the category exists
        $checkCategoryQuery = $conn->prepare("SELECT * FROM Categories WHERE category_name = ?");
        $checkCategoryQuery->bind_param("s", $category_name);
        $checkCategoryQuery->execute();
        $checkCategoryResult = $checkCategoryQuery->get_result();

        if ($checkCategoryResult->num_rows == 0) {
            // The category doesn't exist, so insert it
            $insertCategoryQuery = $conn->prepare("INSERT INTO Categories (category_id, category_name) VALUES (?, ?)");
            $insertCategoryQuery->bind_param("ss", $category_id, $category_name);
            $insertCategoryQuery->execute();
        }

        $checkCategoryQuery->close();
    }

    // Insert products
    foreach ($data['items'] as $item) {
        $product_id = intval($item['id']);
        $product_name = $item['name'];
        $category_id = intval($item['category']);
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;

        // Check if the product exists
        $checkProductQuery = $conn->prepare("SELECT * FROM Products WHERE name = ? AND category_id = ?");
        $checkProductQuery->bind_param("si", $product_name, $category_id);
        $checkProductQuery->execute();
        $checkProductResult = $checkProductQuery->get_result();

        if ($checkProductResult->num_rows == 0) {
            // The product doesn't exist, so insert it
            $insertProductQuery = $conn->prepare("INSERT INTO Products (product_id, name, category_id, quantity) VALUES (?, ?, ?, ?)");
            $insertProductQuery->bind_param("issi", $product_id, $product_name, $category_id, $quantity);
            $insertProductQuery->execute();
        }

        $checkProductQuery->close();
    }

    echo "Τα δεδομένα φορτώθηκαν επιτυχώς στη βάση.";
    header("Location: ../admin/apothiki.php");
} else {
    echo "Σφάλμα κατά τη μεταφόρτωση του αρχείου JSON.";
}

// Close the connection
$conn->close();
?>
