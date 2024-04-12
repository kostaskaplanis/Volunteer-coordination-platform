<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../index.php");
    exit();
}

// Check if the user is not an admin
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    // Redirect to an unauthorized page or show an error message for non-admin users
    header("Location: ../unauthorized.php");
    exit();
}

// Έλεγχος αν η φόρμα έχει υποβληθεί
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require('../test_connection.php');
    require('../components/navbar.php');

    // Λήψη των προϊόντων που επιλέγονται από τη φόρμα
    $selectedProducts = isset($_POST['selected_products']) ? $_POST['selected_products'] : [];

    // Λήψη των υπόλοιπων πεδίων από τη φόρμα
    $title = $_POST['title'];
    $content = $_POST['content'];

    // Έλεγχος αν υπάρχουν επιλεγμένα προϊόντα
    if (empty($selectedProducts)) {
        echo "Επιλέξτε τουλάχιστον ένα προϊόν.";
    } else {
        // Εισαγωγή της ανακοίνωσης στον πίνακα Announcements
        $insertAnnouncement = "INSERT INTO Announcements (title, content) VALUES ('$title', '$content')";
        if ($conn->query($insertAnnouncement) === TRUE) {
            // Λήψη του ID της τελευταίας εισαγωγής
            $announcementId = $conn->insert_id;

            // Εισαγωγή των προϊόντων στον πίνακα AnnouncementProducts
            foreach ($selectedProducts as $productId) {
                $insertProduct = "INSERT INTO AnnouncementProducts (announcement_id, product_id) VALUES ('$announcementId', '$productId')";
                $conn->query($insertProduct);
            }

            echo "Η ανακοίνωση δημιουργήθηκε επιτυχώς.";
        } else {
            echo "Error: " . $insertAnnouncement . "<br>" . $conn->error;
        }
    }

    // Κλείσιμο της σύνδεσης
    $conn->close();

}
?>
