<?php
include('../test_connection.php');

if (isset($_GET['request_id'])) {
    $request_id = $_GET['request_id'];

    $sql = "
    SELECT
    Citizens.full_name AS citizen_name,
    Citizens.phone_number AS citizen_phone,
    Citizens.latitude AS citizen_latitude,
    Citizens.longitude AS citizen_longitude,
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
    Vehicles ON Rescuers.rescuer_id = Vehicles.rescuer_id
    WHERE CitizenRequests.request_id = $request_id;
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
} else {
    echo json_encode(array('error' => 'Missing request_id parameter'));
}

$conn->close();
?>
