<?php
include('../test_connection.php');

$sql = "
SELECT
    cr.request_id,
    cr.citizen_id,
    c.full_name AS citizen_name,
    c.phone_number AS citizen_phone,
    c.latitude AS citizen_latitude,
    c.longitude AS citizen_longitude,
    cr.product_id,
    p.name AS product_name, -- Change 'p.product_name' to 'p.name'
    cr.quantity,
    cr.number_of_people,
    cr.status,
    cr.date_submitted,
    cr.date_accepted,
    cr.date_completed,
    cr.rescuer_id,
    (
        SELECT v.VehicleName
        FROM Vehicles v
        WHERE v.rescuer_id = cr.rescuer_id
        LIMIT 1
    ) AS VehicleName
FROM
    CitizenRequests cr
JOIN
    Citizens c ON cr.citizen_id = c.citizen_id
JOIN
    Products p ON cr.product_id = p.product_id;

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