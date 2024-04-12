<?php
include('../test_connection.php');


$sql = "
SELECT Citizens.full_name AS citizen_name, 
Citizens.phone_number AS citizen_phone, 
Citizens.latitude AS citizen_latitude, 
Citizens.longitude AS citizen_longitude, 
Announcements.title AS title, 
CitizenOffers.date_submitted, 
Products.name AS product_name, 
CitizenOffers.quantity, 
CitizenOffers.date_completed, 
CitizenOffers.offer_id, 
CitizenOffers.status, 
Vehicles.VehicleName 
FROM Citizens 
JOIN CitizenOffers ON Citizens.citizen_id = CitizenOffers.citizen_id 
JOIN Products ON CitizenOffers.product_id = Products.product_id 
JOIN Announcements ON CitizenOffers.announcement_id = Announcements.announcement_id 
LEFT JOIN Rescuers ON CitizenOffers.offer_id = Rescuers.rescuer_id 
LEFT JOIN Vehicles 
ON Rescuers.rescuer_id = Vehicles.rescuer_id;

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
