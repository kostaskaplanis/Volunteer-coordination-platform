
<?php
session_start();
include ('../components/navbar.php');
// Check if the user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../index.php");
    exit();
}

// Check if the user is a citizen
if (isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'citizen') {
    // The user is a citizen
    // Your citizen dashboard code here
} else {
    // Redirect to a different page or show an error message for non-citizen users
    header("Location: ../unauthorized.php");
    exit();
}

$servername = "localhost";
$username = "kostas";
$password = "kostas1234";
$dbname = "FYSIKES_KATASTROFES";

// Σύνδεση με τη βάση δεδομένων
$conn = new mysqli($servername, $username, $password, $dbname);

// Έλεγχος σύνδεσης
if ($conn->connect_error) {
    die("Αποτυχία σύνδεσης: " . $conn->connect_error);
}

// Έλεγχος αν υπάρχουν δεδομένα που αποστάλθηκαν από τη φόρμα
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Λήψη των δεδομένων από τη φόρμα
    $product = $_POST["product"];
    // Check if 'numberOfPeople' is set in $_POST and use it as 'quantity'
    $quantity = isset($_POST["numberOfPeople"]) ? $_POST["numberOfPeople"] : 1;

    // Εύρεση του επόμενου διαθέσιμου ID ή επιλογή ID=1 αν δεν υπάρχει άλλο διαθέσιμο ID
    $nextIdQuery = "SELECT MIN(request_id + 1) AS next_id FROM CitizenRequests WHERE NOT EXISTS (SELECT * FROM CitizenRequests cr2 WHERE cr2.request_id = CitizenRequests.request_id + 1)";
    $nextIdResult = $conn->query($nextIdQuery);
    $row = $nextIdResult->fetch_assoc();
    $nextId = ($row['next_id'] != null) ? $row['next_id'] : 1;

    // Προετοιμασία του SQL ερωτήματος για την εισαγωγή δεδομένων στον πίνακα
    $sql = "INSERT INTO CitizenRequests (request_id, citizen_id, product_id, number_of_people, status) VALUES (?, ?, ?, ?,'new')";

    // Έλεγχος αν ο χρήστης έχει συνδεθεί και έχει αποθηκευμένο το citizen_id
    if (isset($_SESSION['user_id'])) {
        $citizen_id = $_SESSION['user_id'];

        // Προετοιμασία και εκτέλεση του prepared statement
        if ($stmt = $conn->prepare($sql)) {
            // Ορισμός παραμέτρων και binding των placeholders με τις τιμές, συμπεριλαμβάνοντας το request_id
            // $nextId είναι το επόμενο διαθέσιμο ID που βρέθηκε παραπάνω
            $stmt->bind_param("iiii", $nextId, $citizen_id, $product, $quantity);

            // Εκτέλεση του statement
            if ($stmt->execute()) {
                echo "Η αίτηση αποθηκεύτηκε με επιτυχία.";
            } else {
                echo "Υπήρξε κάποιο πρόβλημα κατά την αποθήκευση της αίτησης: " . $conn->error;
            }

            // Κλείσιμο του statement
            $stmt->close();
        } else {
            echo "Υπήρξε κάποιο πρόβλημα με την προετοιμασία του ερωτήματος.";
        }
    } else {
        echo "Παρακαλώ συνδεθείτε πρώτα για να υποβάλετε αίτηση.";
    }
}

$conn->close();
?>
