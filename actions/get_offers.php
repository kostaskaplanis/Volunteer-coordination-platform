<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../index.php");
    exit();
}

// Check if the user is not an admin
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'rescuer') {
    // Redirect to an unauthorized page or show an error message for non-admin users
    header("Location: ../unauthorized.php");
    exit();
}

include('../test_connection.php');

// Assuming $_SESSION['user_id'] is set with the user ID
$user_id = $_SESSION['user_id'];

$sql = "
SELECT
    co.offer_id,
    co.citizen_id,
    c.full_name AS citizen_name,
    c.phone_number AS citizen_phone,
    c.latitude AS citizen_latitude,
    c.longitude AS citizen_longitude,
    co.product_id,
    p.name AS product_name, -- Change 'p.product_name' to 'p.name'
    co.quantity,
    co.status,
    co.date_submitted,
    co.date_accepted,
    co.date_completed,
    co.rescuer_id,
    (
        SELECT v.VehicleName
        FROM Vehicles v
        WHERE v.rescuer_id = co.rescuer_id
        LIMIT 1
    ) AS VehicleName,
    '$user_id' AS user_id  -- Add this line to include user_id in the result set
FROM
    CitizenOffers co
JOIN
    Citizens c ON co.citizen_id = c.citizen_id
JOIN
    Products p ON co.product_id = p.product_id;

";

$result = $conn->query($sql);

if (!$result) {
    die("Error running the SQL query: " . $conn->error);
}

$rows = array();
while ($row = $result->fetch_assoc()) {
    $rows[] = $row;
}

echo json_encode($rows);

$conn->close();
?>
