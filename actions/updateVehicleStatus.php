<?php

include('../test_connection.php');

function updateVehicleStatus($conn) {
    try {

        $vehiclesQuery = "SELECT VehicleID, rescuer_id FROM Vehicles";
        $result = $conn->query($vehiclesQuery);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $vehicleId = $row["VehicleID"];
                $rescuerId = $row["rescuer_id"];

                $isInUse = isRescuerInUse($conn, $rescuerId);

                updateVehicleStatusInDB($conn, $vehicleId, $isInUse);
            }
        } 
    } catch (Exception $e) {
         // Handle exceptions here
    }
}

function isRescuerInUse($conn, $rescuerId) {
    $requestExistsQuery = "SELECT 1 FROM CitizenRequests WHERE rescuer_id = $rescuerId LIMIT 1";
    $requestExistsResult = $conn->query($requestExistsQuery);

    $offerExistsQuery = "SELECT 1 FROM CitizenOffers WHERE rescuer_id = $rescuerId LIMIT 1";
    $offerExistsResult = $conn->query($offerExistsQuery);

    return ($requestExistsResult->num_rows > 0) || ($offerExistsResult->num_rows > 0);
}

function updateVehicleStatusInDB($conn, $vehicleId, $inUse) {
    $status = $inUse ? 'In Use' : 'Available';
    $updateQuery = "UPDATE Vehicles SET Status = '$status' WHERE VehicleID = $vehicleId";

    if ($conn->query($updateQuery) === FALSE) {
        // Handle errors if needed
    }
}

updateVehicleStatus($conn);

$conn->close();

?>
