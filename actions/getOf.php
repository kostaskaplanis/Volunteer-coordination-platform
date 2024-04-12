<?php
include('../test_connection.php');

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
    ) AS VehicleName
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