<?php
include('../test_connection.php');

$sql = "
SELECT DISTINCT
Citizens.full_name AS citizen_name,
Citizens.phone_number AS citizen_phone,
Citizens.latitude AS citizen_latitude, -- Add latitude field
Citizens.longitude AS citizen_longitude, -- Add longitude field
Products.name AS product_name,
CitizenRequests.number_of_people,
CitizenRequests.date_submitted,
CitizenRequests.date_accepted,
CitizenRequests.date_completed,
Vehicles.VehicleName,
CitizenRequests.request_id,
CitizenRequests.status
FROM
Citizens
JOIN
CitizenRequests ON Citizens.citizen_id = CitizenRequests.citizen_id
JOIN
Products ON CitizenRequests.product_id = Products.product_id
LEFT JOIN
CitizenOffers ON CitizenRequests.request_id = CitizenOffers.announcement_id
LEFT JOIN
Rescuers ON CitizenOffers.citizen_id = Rescuers.rescuer_id
LEFT JOIN
Vehicles ON Rescuers.rescuer_id = Vehicles.rescuer_id;
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