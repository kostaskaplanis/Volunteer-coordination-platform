<!-- index.php -->
<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../index.php");
    exit();
}

// Check if the user is not a rescuer
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'rescuer') {
    // Redirect to an unauthorized page or show an error message for non-admin users
    header("Location: ../unauthorized.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Φόρτωση Ειδών</title>
    <link rel="icon" type="image/x-icon" href="../styles/civil_protection.png">
    <style>
        * {
                font-family: "Lato", sans-serif;
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
        
        input[type="number"] {
            width: 60px;
        }

        .content-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php
    require('../test_connection.php');
    require('../components/navbar.php');
    $within_distance = true;
    $rescuer_id = $_SESSION['user_id'];
    if ($within_distance) {
        // Fetch the available products from the base inventory (using the Products table)
        $sqlBaseInventory = "SELECT p.product_id, p.name, p.quantity AS available_quantity, ri.quantity
                             FROM Products p
                             LEFT JOIN RescuerInventory ri ON p.product_id = ri.product_id AND ri.rescuer_id = $rescuer_id";
        $resultBaseInventory = $conn->query($sqlBaseInventory);
    }
    ?>

    <?php if ($within_distance): ?>
        <div id="response-message"></div>
        <h1 class="title">Φόρτωση Ειδών</h1>
        <div class="content-container"> 
            <form action="../actions/handle-rescuer-load.php" method="post">
                <h2 class="title">Διαθέσιμα Είδη στη Βάση</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Προϊόν</th>
                            <th>Διαθέσιμη Ποσότητα</th>
                            <th>Ποσότητα</th>
                            <th>Επιλογή</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                    // Display available products from the base inventory (using the Products table)
                    if ($resultBaseInventory && $resultBaseInventory->num_rows > 0) {
                        while ($rowProduct = $resultBaseInventory->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>{$rowProduct['name']}</td>";
                            echo "<td>{$rowProduct['available_quantity']}</td>";
                            echo "<td><input type='number' name='quantity[{$rowProduct['product_id']}]' placeholder='Ποσότητα' max='{$rowProduct['available_quantity']}' min='0'></td>";
                            echo "<td><input type='checkbox' name='selected_products[]' value='{$rowProduct['product_id']}'></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>Δεν υπάρχουν διαθέσιμα είδη στη βάση.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
            
            <input type="submit" name="load" value="Φόρτωση Ειδών">
        </form>
    </div>

        <!-- Unload Form -->
        <form action="../actions/handle-unload.php" method="post">
            <h2 class="title">Εκφόρτωση Ειδών</h2>
            <div class="content-container"> 
                <input type="submit" name="unload" value="Εκφόρτωση Ειδών">
            </div>
        </form>

    <?php endif; ?>
</body>
</html>
