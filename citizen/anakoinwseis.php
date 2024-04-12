<!-- announcements.php -->
<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../index.php");
    exit();
}

// Check if the user is not an admin
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'citizen') {
    // Redirect to an unauthorized page or show an error message for non-admin users
    header("Location: ../unauthorized.php");
    exit();
}
?>
<?php
require('../test_connection.php');
include('../components/navbar.php');

// Εκτέλεση του SQL query για ανακοινώσεις
$sqlAnnouncements = "SELECT 
                        Announcements.announcement_id,
                        Announcements.title,
                        Announcements.content,
                        Announcements.date_created,
                        GROUP_CONCAT(Products.name) AS product_names
                    FROM 
                        Announcements
                    LEFT JOIN 
                        AnnouncementProducts ON Announcements.announcement_id = AnnouncementProducts.announcement_id
                    LEFT JOIN 
                        Products ON AnnouncementProducts.product_id = Products.product_id
                    GROUP BY 
                        Announcements.announcement_id";
$resultAnnouncements = $conn->query($sqlAnnouncements);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Ανακοινώσεων</title>
    <link rel="icon" type="image/x-icon" href="../styles/civil_protection.png">
    <style>
        * {
                font-family: "Lato", sans-serif;
        }
        .content-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px; margin-bottom: 20px;
            max-width: 1200px;
        }

        th, td {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
        }

        th {
                background-color: #f2f2f2;
        }
    </style>
</head>
<body>

        <h1 style="text-align: center;">Διαχείριση Ανακοινώσεων</h1>
        
        <div class="content-container">

        <?php
        // Εμφάνιση ανακοινώσεων
        if ($resultAnnouncements->num_rows > 0) {
            echo "<table border='1'>
            <tr>
            <th>Ανακοίνωση ID</th>
            <th>Τίτλος</th>
            <th>Περιεχόμενο</th>
            <th>Ημερομηνία Δημιουργίας</th>
            <th>Προϊόντα</th>
            <th>Εκδήλωση Προσφοράς</th>
            </tr>";
            
            while ($row = $resultAnnouncements->fetch_assoc()) {
                echo "<tr>
                <td>{$row['announcement_id']}</td>
                <td>{$row['title']}</td>
                <td>{$row['content']}</td>
                <td>{$row['date_created']}</td>
                <td>{$row['product_names']}</td>
                <td><a href='/WEB-24/actions/create_offer.php?announcement_id={$row['announcement_id']}'>Εκδήλωση Προσφοράς</a></td>
                </tr>";
            }
            
                echo "</table>";
            } else {
                echo "Δεν υπάρχουν ανακοινώσεις.";
            }
            
            // Κλείσιμο της σύνδεσης
            $conn->close();
            ?>
        </div>
</body>
</html>
