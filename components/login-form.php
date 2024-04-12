<?php


// Check if an error message is set in the session
if (isset($_SESSION['errorMsg']) && !empty($_SESSION['errorMsg'])) {
    echo "<div id='error-message'>{$_SESSION['errorMsg']}</div>";
    // Clear the error message after displaying it
    $_SESSION['errorMsg'] = "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="styles/login-form.css">
    
</head>
<body>
    
    <!-- <div class="form-wrapper"> -->
        <form id="myForm" action="actions/handle-login.php" method="post">
                <h2>Συνδεθείτε</h3>
                <div class="input-group">
                    <label for="username">Όνομα χρήστη:</label>
                    <input type="text" name="username" required/>
                </div>
                <div class="input-group">
                    <label for="password">Κωδικός:</label>
                    <input type="password"  name="password" required/>
                </div>
                
                <div class="input-group">
                    <label for='usertype'>Είμαι: </label>
                    <select id='usertype' name='usertype' required>
                        
                        <option value='admin'>Διαχειριστής</option>
                        <option value='citizen'>Πολίτης</option>
                        <option value='rescuer'>Διασώστης</option>
                    </select>
                </div>
                
                <button class="submit-button" type="submit">Είσοδος</button>
                <?php
                    // Check if an error message is set
                    if (isset($errorMsg)) {
                        echo 'test';
                        // Display the error message
                        echo '<div id="error-message">' . $errorMsg . '</div>';
                    }
                ?>
                <h5>Δεν έχετε λογαριασμό; <a href="citizen_register.php">Δημιουργία νέου λογαρισμού</a></h5>
        </form>

        <script>
            var errorMessage = document.querySelector('#error-message');
            setTimeout(() => {
                errorMessage.style.display = "none";
            }, 5000);
        </script>
    </div>
    