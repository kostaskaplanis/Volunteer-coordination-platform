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
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Ιστορικό Προσφορών</title>
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

        .title {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 20px;
            max-width: 1200px;
        }

        th,
        td {
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
    <?php include('../components/navbar.php'); ?>
    <h1 class="title">Ιστορικό Προσφορών</h1>

    <h2 class="title">Τρέχουσες Προσφορές</h2>
    <div class="content-container">
        <table border="1">
            <thead>
                <tr>
                    <th>Προϊόν</th>
                    <th>Κατάσταση</th>
                    <th>Ημερομηνία Υποβολής</th>
                    <th>Ημερομηνία Ολοκλήρωσης</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $current_user_id = $_SESSION['user_id'];
                include('../test_connection.php');

                // Query for current offers with product names
                $currentOffersQuery = "SELECT CitizenOffers.*, Products.name 
                                    FROM CitizenOffers 
                                    LEFT JOIN Products ON CitizenOffers.product_id = Products.product_id 
                                    WHERE CitizenOffers.citizen_id = $current_user_id AND (CitizenOffers.status = 'new' OR CitizenOffers.status = 'in progress')";
                $currentOffersResult = $conn->query($currentOffersQuery);

                if ($currentOffersResult->num_rows > 0) {
                    while ($row = $currentOffersResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["name"] . "</td>"; // Product name
                        echo "<td>" . $row["status"] . "</td>";
                        echo "<td>" . $row["date_submitted"] . "</td>";
                        echo "<td>" . $row["date_completed"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Δεν υπάρχουν τρέχουσες προσφορές</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <h2 class="title">Παρελθούσες Προσφορές</h2>
    <div class="content-container">
        <table border="1">
            <thead>
                <tr>
                    <th>Προϊόν</th>
                    <th>Κατάσταση</th>
                    <th>Ημερομηνία Υποβολής</th>
                    <th>Ημερομηνία Ολοκλήρωσης</th>
                </tr>
            </thead>
            <tbody>
                <?php
                require('../test_connection.php');

                // Query for past offers with product names
                $pastOffersQuery = "SELECT CitizenOffers.*, Products.name 
                                FROM CitizenOffers 
                                LEFT JOIN Products ON CitizenOffers.product_id = Products.product_id 
                                WHERE CitizenOffers.citizen_id = $current_user_id AND CitizenOffers.status = 'completed'";
                $pastOffersResult = $conn->query($pastOffersQuery);

                if ($pastOffersResult->num_rows > 0) {
                    while ($row = $pastOffersResult->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row["name"] . "</td>"; // Product name
                        echo "<td>" . $row["status"] . "</td>";
                        echo "<td>" . $row["date_submitted"] . "</td>";
                        echo "<td>" . $row["date_completed"] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>Δεν υπάρχουν παρελθούσες προσφορές</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

</body>

</html>
