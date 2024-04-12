<?php
include('../test_connection.php');


$sql = "SELECT co.offer_id, c.full_name, c.phone_number, co.date_submitted, co.status, 
        p.name AS product_name, cit.latitude AS request_latitude, cit.longitude AS request_longitude
        FROM CitizenOffers co
        INNER JOIN Citizens c ON co.citizen_id = c.citizen_id
        INNER JOIN Products p ON co.product_id = p.product_id
        INNER JOIN Citizens cit ON co.citizen_id = cit.citizen_id
        WHERE co.status = 'Pending'";
        
$result = $conn->query($sql);

if (!$result) {
    die("Σφάλμα κατά την εκτέλεση του SQL: " . $conn->error);
}

$rows = array();
while ($row = $result->fetch_assoc()) {
    // Προσθήκη πεδίου "display_status" με τη λέξη "ΕΚΡΕΜΕΙ" όταν το status είναι "Pending"
    $row['display_status'] = ($row['status'] === 'Pending') ? 'ΕΚΡΕΜΕΙ' : $row['status'];
    $rows[] = $row;
}

echo json_encode($rows);

$conn->close();
?>