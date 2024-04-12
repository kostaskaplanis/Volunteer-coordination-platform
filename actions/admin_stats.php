<?php
require('../test_connection.php');

$data = [];

function addData(&$data, $key, $queryResult) {
    if ($row = $queryResult->fetch_assoc()) {
        $data[$key] = $row[$key];
    }
}

$countRequests = "SELECT COUNT(*) AS totalRequests FROM CitizenRequests WHERE status != 'completed'";
$resultRequests = $conn->query($countRequests);
addData($data, "totalRequests", $resultRequests);

$countOffers = "SELECT COUNT(*) AS totalOffers FROM CitizenOffers WHERE status != 'completed'";
$resultOffers = $conn->query($countOffers);
addData($data, "totalOffers", $resultOffers);

$countProducts = "SELECT COUNT(*) AS totalProducts FROM products";
$resultProducts = $conn->query($countProducts);
addData($data, "totalProducts", $resultProducts);

$countAnnouncementproducts = "SELECT COUNT(*) AS totalAnnouncementproducts FROM announcementproducts";
$resultAnnouncementproducts = $conn->query($countAnnouncementproducts);
addData($data, "totalAnnouncementproducts", $resultAnnouncementproducts);

$countAnnouncements = "SELECT COUNT(*) AS totalAnnouncements FROM Announcements";
$resultAnnouncements = $conn->query($countAnnouncements);
addData($data, "totalAnnouncements", $resultAnnouncements);

$countVehicles = "SELECT COUNT(*) AS totalVehicles FROM Vehicles";
$resultVehicles = $conn->query($countVehicles);
addData($data, "totalVehicles", $resultVehicles);

$countCitizens = "SELECT COUNT(*) AS totalCitizens FROM Citizens";
$resultCitizens = $conn->query($countCitizens);
addData($data, "totalCitizens", $resultCitizens);

$countRescuers = "SELECT COUNT(*) AS totalRescuers FROM Rescuers";
$resultRescuers = $conn->query($countRescuers);
addData($data, "totalRescuers", $resultRescuers);

header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>
