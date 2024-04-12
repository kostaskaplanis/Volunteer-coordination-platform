<?php
// Your database connection code here
include('../test_connection.php');

// Check if the vehicleName parameter is provided
if (isset($_GET['vehicleName'])) {
    $vehicleName = $_GET['vehicleName'];

    // Execute the SQL query with the filter for a specific vehicleName
    $sql = "
    SELECT 
        Vehicles.VehicleName,
        GROUP_CONCAT(
            CONCAT(Products.name, ': ', COALESCE(RescuerInventory.quantity, 0)) 
            ORDER BY Products.name 
            SEPARATOR ', '
        ) AS cargo_info
    FROM 
        Vehicles
    LEFT JOIN 
        RescuerInventory ON Vehicles.rescuer_id = RescuerInventory.rescuer_id
    LEFT JOIN 
        Products ON RescuerInventory.product_id = Products.product_id
    WHERE 
        Vehicles.VehicleName = ?
    GROUP BY 
        Vehicles.VehicleName;
";

    // Prepare the SQL statement using the correct connection object
    $stmt = $conn->prepare($sql);

    // Bind the parameter
    $stmt->bind_param('s', $vehicleName);

    // Execute the prepared statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Check if the query was successful
    if ($result) {
        // Fetch all rows as an associative array
        $data = $result->fetch_all(MYSQLI_ASSOC);

        // Encode the data as JSON and send the response
        header('Content-Type: application/json');
        echo json_encode($data);
    } else {
        // Handle the error
        echo json_encode(['status' => 'error', 'message' => $stmt->error]);
    }

    // Close the prepared statement
    $stmt->close();
} else {
    // Handle the case when vehicleName parameter is not provided
    echo json_encode(['status' => 'error', 'message' => 'vehicleName parameter is missing']);
}

// Close the database connection
$conn->close();
?>
