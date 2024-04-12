<?php
require('../test_connection.php');

$data = [];

function addData(&$data, $key, $queryResult) {
    if ($row = $queryResult->fetch_assoc()) {
        $data[$key] = $row[$key];
    }
}

$count2 = "SELECT COUNT(*) AS totalAnnouncements FROM Announcements";
$result2 = $conn->query($count2);
addData($data, "totalAnnouncements", $result2);

header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>
