<?php
require('../test_connection.php');

// Fetch products
$sqlProducts = "SELECT Products.product_id, Products.name AS product_name, Categories.category_name, Products.quantity
                FROM Products
                INNER JOIN Categories ON Products.category_id = Categories.category_id;";
$resultProducts = $conn->query($sqlProducts);

// Store products in an array
$products = array();
while ($rowProduct = $resultProducts->fetch_assoc()) {
    $products[] = $rowProduct;
}

// Return products as JSON
header('Content-Type: application/json');
echo json_encode($products);

$conn->close();
?>
