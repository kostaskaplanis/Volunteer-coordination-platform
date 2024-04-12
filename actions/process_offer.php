<!-- process_offer.php -->

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
// Έναρξη της συνεδρίας
require('../test_connection.php');
require('../components/navbar.php');
// Έλεγχος αν ο χρήστης έχει συνδεθεί


// Έλεγχος αν υποβλήθηκε η φόρμα
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Έλεγχος αν επιλέχθηκαν προϊόντα
    if (isset($_POST['selected_products']) && is_array($_POST['selected_products'])) {
        $citizen_id = $_SESSION['user_id'];// Αντικατάσταση το με τον πραγματικό κωδικό πολίτη
        
        // Πληροφορίες προσφοράς
        $announcement_id = $_POST['announcement_id'];
        
        
                // Εισαγωγή προσφοράς στον πίνακα CitizenOffers
        $sqlInsertOffer = "INSERT INTO CitizenOffers (citizen_id, announcement_id, product_id, quantity)
        VALUES ";

        // Κατασκευή του SQL query για κάθε επιλεγμένο προϊόν
        foreach ($_POST['selected_products'] as $product_id) {
        $quantity = $_POST['quantities'][$product_id];
        $sqlInsertOffer .= "($citizen_id, $announcement_id, $product_id, $quantity),";
        }

        // Αφαίρεση του τελευταίου ',' και εκτέλεση του query
        $sqlInsertOffer = rtrim($sqlInsertOffer, ',');
        $resultInsertOffer = $conn->query($sqlInsertOffer);

        if ($resultInsertOffer) {
            echo "<b>Η προσφορά υποβλήθηκε με επιτυχία.</b>";
        } else {
            echo "Υπήρξε ένα πρόβλημα κατά την υποβολή της προσφοράς.";
        }
    } else {
        echo "Επιλέξτε τουλάχιστον ένα προϊόν.";
    }

} else {
    // Αν η φόρμα δεν υποβλήθηκε, επιστροφή στην αρχική σελίδα
    header('Location: index.php');
    exit();
}

// Κλείσιμο της σύνδεσης
$conn->close();
?>
