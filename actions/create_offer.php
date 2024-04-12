<!-- create_offer.php -->

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
require('../test_connection.php');
require('../components/navbar.php');

// Επιλεγμένη ανακοίνωση από το URL
if (isset($_GET['announcement_id'])) {
    $announcement_id = $_GET['announcement_id'];

    // Εκτέλεση του SQL query για τα προϊόντα της επιλεγμένης ανακοίνωσης
    $sqlProducts = "SELECT 
                        Products.product_id,
                        Products.name
                    FROM 
                        AnnouncementProducts
                    INNER JOIN 
                        Products ON AnnouncementProducts.product_id = Products.product_id
                    WHERE 
                        AnnouncementProducts.announcement_id = $announcement_id";
    $resultProducts = $conn->query($sqlProducts);
} else {
    // Αν δεν υπάρχει επιλεγμένη ανακοίνωση, επιστροφή στη σελίδα διαχείρισης ανακοινώσεων
    header('Location: announcements.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
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
    <h1>Δημιουργία Προσφοράς</h1>

    <form action="process_offer.php" method="post">
        <h2>Επιλογή Προϊόντων</h2>

        <?php
        // Εμφάνιση προϊόντων ως επιλογές
        if ($resultProducts->num_rows > 0) {
            echo '<table>';
            echo '<tr><th>Επιλογή</th><th>Προϊόν</th><th>Ποσότητα</th></tr>';

            while ($rowProduct = $resultProducts->fetch_assoc()) {
                $productId = $rowProduct['product_id'];

                echo "<tr>";
                echo "<td><input type='checkbox' name='selected_products[{$productId}]' value='{$productId}'></td>";
                echo "<td>{$rowProduct['name']}</td>";
                echo "<td><label for='quantities[{$productId}]'>Ποσότητα:</label>";
                echo "<input type='number' name='quantities[{$productId}]'></td>";
                echo "</tr>";
            }

            echo '</table>';
        } else {
            echo "<p>Δεν υπάρχουν προϊόντα για αυτήν την ανακοίνωση.</p>";
        }
        ?>

        <!-- Κρυφό πεδίο για να περάσει το announcement_id -->
        <input type="hidden" name="announcement_id" value="<?php echo $announcement_id; ?>">

        <input type="submit" value="Υποβολή Προσφοράς">
    </form>
    
</body>
</html>