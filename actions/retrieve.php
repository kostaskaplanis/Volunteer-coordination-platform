<?php
include("../test_connection.php");

// Retrieve form data
$username = $_POST['username'];
$password = $_POST['password'];
$fullName = $_POST['full_name'];
$phoneNumber = $_POST['phone'];
$latitude = $_POST['latitude'];
$longitude = $_POST['longitude'];
// // Retrieve latitude and longitude values from the AJAX request
// $latitude = isset($_POST['latitude']) ? $_POST['latitude'] : null;
// $longitude = isset($_POST['longitude']) ? $_POST['longitude'] : null;

// // Debugging: Output received values to check
 //echo "Latitude: $latitude, Longitude: $longitude";


// Use prepared statement to insert data into the database
$stmt = $conn->prepare("INSERT INTO Citizens (username, password, full_name, phone_number, latitude, longitude) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssdd", $username, $password, $fullName, $phoneNumber, $latitude, $longitude);

// Execute the statement
if ($stmt->execute()) {
    echo "Δημιουργία νέου χρήστη με επιτυχία!";
    echo"<br>";
    echo"<a href='../index.php'>Παρακαλώ Συνδεθείτε.</a>";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and database connection
$stmt->close();
$conn->close();
?>
