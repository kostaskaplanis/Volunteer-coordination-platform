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
    <title>Ιστορικό Αιτημάτων</title>
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
<?php include('../components/navbar.php'); ?>
<h1 class="title">Ιστορικό Αιτημάτων</h1>

<h2 class="title">Τρέχοντα Αιτήματα</h2>
<div class="content-container">

    <table border="1">
        <thead>
            <tr>
                <th>Προιόν που ζήτησες</th>
                <th>Κατάσταση</th>
                <th>Ημερομηνία Υποβολής</th>
                <th>Ημερομηνία Αποδοχής</th>
                <th>Ημερομηνία Ολοκλήρωσης</th>
            </tr>
        </thead>
        <tbody>
            <?php 
    
    $current_user_id = $_SESSION['user_id'];
    
    // Κώδικας για σύνδεση με τη βάση δεδομένων και λήψη δεδομένων
    include('../test_connection.php');
    
    // Query για τα τρέχοντα αιτήματα με τα ονόματα των προϊόντων
    $currentRequestsQuery = "SELECT CitizenRequests.*, Products.name 
                         FROM CitizenRequests 
                         LEFT JOIN Products ON CitizenRequests.PRODUCT_ID = Products.PRODUCT_ID 
                         WHERE CitizenRequests.citizen_id = $current_user_id AND CitizenRequests.status = 'new'";
// Εμφάνιση των τρεχόντων αιτημάτων
$currentRequestsResult = $conn->query($currentRequestsQuery);

// Εμφάνιση των τρεχόντων αιτημάτων
if ($currentRequestsResult->num_rows > 0) {
    while ($row = $currentRequestsResult->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["name"] . "</td>"; // Προϊόν αιτήματος
        echo "<td>" . $row["status"] . "</td>";
        echo "<td>" . $row["date_submitted"] . "</td>";
        echo "<td>" . ($row["status"] === 'new' ? '' : $row["date_accepted"]) . "</td>";
        echo "<td>" . $row["date_completed"] . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>Δεν υπάρχουν τρέχοντα αιτήματα</td></tr>";
}
$conn->close();
?>
    </tbody>
</tbody>
</table>

</tbody>
</table>
</div>

<h2 class="title">Παρελθόντα Αιτήματα</h2>

<div class="content-container">

    <table border="1">
        <thead>
            <tr>
                <th>Αριθμός Αιτήματος</th>
                <th>Κατάσταση</th>
                <th>Ημερομηνία Υποβολής</th>
                <th>Ημερομηνία Αποδοχής</th>
                <th>Ημερομηνία Ολοκλήρωσης</th>
            </tr>
        </thead>
        <tbody>
            <?php  
    require('../test_connection.php');
    
    
    // Query για τα παρελθόντα αιτήματα με status=completed
    $pastRequestsQuery = "SELECT CitizenRequests.*, Products.name 
    FROM CitizenRequests 
    LEFT JOIN Products ON CitizenRequests.PRODUCT_ID = Products.PRODUCT_ID 
    WHERE CitizenRequests.citizen_id = $current_user_id AND CitizenRequests.status = 'completed'";
    $pastRequestsResult = $conn->query($pastRequestsQuery);

    if ($pastRequestsResult->num_rows > 0) {
        while ($row = $pastRequestsResult->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["name"] . "</td>"; // Προϊόν αιτήματος
            echo "<td>" . $row["status"] . "</td>";
            echo "<td>" . $row["date_submitted"] . "</td>";
            echo "<td>" .  $row["date_accepted"] . "</td>";
            echo "<td>" . $row["date_completed"] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>Δεν υπάρχουν παρελθόντα αιτήματα</td></tr>";
    }
    
    $conn->close();
    ?>
    </tbody>
</table>
</div>

</body>
</html>