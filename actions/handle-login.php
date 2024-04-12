<?php
session_start(); // Start the session

include('../test_connection.php');

// Initialize error message
$errorMsg = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];
    $userType = $_POST["usertype"];

    switch ($userType) {
        case 'admin':
            $table = "Admins";
            $homePage = "../admin/home.php";
            break;
        case 'citizen':
            $table = "Citizens";
            $homePage = "../citizen/home.php";
            break;
        case 'rescuer':
            $table = "Rescuers";
            $homePage = "../rescuer/home.php";
            break;
        default:
            // Handle other cases if needed
            break;
    }

    if (!empty($table)) {
        // Protect against SQL injection using prepared statements
        $stmt = $conn->prepare("SELECT * FROM $table WHERE username = ?");
        $stmt->bind_param("s", $inputUsername);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();

            if ($row !== null && $inputPassword == $row['password']) {
                // Password is correct
                if (isset($row['admin_id'])) {
                    $_SESSION['user_id'] = $row['admin_id'];
                } elseif (isset($row['citizen_id'])) {
                    $_SESSION['user_id'] = $row['citizen_id'];
                } elseif (isset($row['rescuer_id'])) {
                    $_SESSION['user_id'] = $row['rescuer_id'];
                }
                $_SESSION['username'] = $inputUsername;
                $_SESSION['usertype'] = $userType;
                // Redirect to home page
                header("Location: $homePage");
                exit();
            } else {
                // Password is incorrect
                $errorMsg = "Login failed. Invalid password.";
            }
            // Close the result set
            $result->close();
        } else {
            // Username not found
            $errorMsg = "Login failed. Invalid username or password.";
        }
        // Close the prepared statement
        $stmt->close();
    }

    // Store the error message in a session variable
    $_SESSION['errorMsg'] = $errorMsg;

    // Close the database connection
    $conn->close();

    // Redirect back to index.php
    header("Location: ../index.php");
    exit();
}
?>
