<?php
include('../test_connection.php');

if (isset($_GET['offer_id'])) {
    $offer_id = $_GET['offer_id'];

    $sql = "
    SELECT
    Citizens.full_name AS citizen_name,
    Citizens.phone_number AS citizen_phone,
    Citizens.latitude AS citizen_latitude,
    Citizens.longitude AS citizen_longitude,
    Products.name AS product_name,
    CitizenOffers.quantity,  -- Assuming there is a column named quantity in CitizenOffers
    CitizenOffers.date_submitted,
    CitizenOffers.date_accepted,
    CitizenOffers.date_completed,
    Vehicles.VehicleName,
    CitizenOffers.offer_id,
    CitizenOffers.status
    FROM
        Citizens
    JOIN
        CitizenOffers ON Citizens.citizen_id = CitizenOffers.citizen_id
    JOIN
        Products ON CitizenOffers.product_id = Products.product_id
    LEFT JOIN
        Rescuers ON CitizenOffers.rescuer_id = Rescuers.rescuer_id
    LEFT JOIN
        Vehicles ON Rescuers.rescuer_id = Vehicles.rescuer_id
    WHERE CitizenOffers.offer_id = $offer_id;
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
    echo json_encode(array('error' => 'Missing offer_id parameter'));
}

$conn->close();
?>