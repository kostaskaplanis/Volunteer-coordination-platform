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
?>
<!DOCTYPE html>
<html lang="el">

    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="../styles/acc.css">
        <link rel="icon" type="image/x-icon" href="../styles/civil_protection.png">
        <title>Εγγραφές </title>
    <style>
        * {
            font-family: "Lato", sans-serif;
        }
        </style>
    </head>
    <body>
        <?php include('../components/navbar.php') ?>
        <div></div>
        <div class="Sign in">
            <form action="../actions/accounts.php" method="post">
                <h1 >Δημιουργήστε λογαριασμό διασώστη εδώ </h1>
         <hr>
                <label for="username"><b>Όνομα:</b></label>
                <input type="text" placeholder="Εισάγετε όνομα χρήστη" name="username" id="username" required/>
                <br>
                <br>
                <label for="password"><b>Κωδικός:</b></label>
                <input type="password" placeholder="Εισάγετε κωδικό" name="password" id="password" required/>
                <br>
                <br>
                <button type="submit" id="signButton">Eγγραφή</button>               
            </form>
        </div>
    </body>
 </html>
    