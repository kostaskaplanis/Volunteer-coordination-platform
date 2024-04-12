<?php
session_start();

include('../test_connection.php');

// Fetch data from Citizens table
$sql = "SELECT full_name, phone_number, latitude, longitude FROM Citizens";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data as JSON
    $citizensData = array();
    while ($row = $result->fetch_assoc()) {
        $citizenId = $row['citizen_id'];
        $citizenData = $row;

        // Fetch citizenOffers data
        $offersSql = "SELECT 
                        CitizenOffers.offer_id,
                        Announcements.title AS announcement_title,
                        Products.name AS product_name,
                        CitizenOffers.quantity,
                        CitizenOffers.status,
                        CitizenOffers.date_submitted
                    FROM 
                        CitizenOffers
                    INNER JOIN 
                        Announcements ON CitizenOffers.announcement_id = Announcements.announcement_id
                    INNER JOIN 
                        Products ON CitizenOffers.product_id = Products.product_id
                    WHERE 
                        CitizenOffers.citizen_id = $citizenId
                        AND CitizenOffers.status = 'Pending'
                    ORDER BY 
                        CitizenOffers.date_submitted DESC";

        $offersResult = $conn->query($offersSql);

        if ($offersResult->num_rows > 0) {
            $offersData = array();
            while ($offerRow = $offersResult->fetch_assoc()) {
                $offersData[] = $offerRow;
            }
            $citizenData['offers'] = $offersData;
        } else {
            $citizenData['offers'] = array(); // No offers for this citizen
        }

        $citizensData[] = $citizenData;
    }

    echo json_encode($citizensData);
} else {
    echo "No citizens found";
}


// Close connection
$conn->close();
?>
