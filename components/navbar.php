
<!DOCTYPE html>
<html lang="en">
<head>
    
    <link rel="stylesheet" href="styles/navbar.css">
    <link rel="stylesheet" href="../styles/navbar.css">

    <link rel="icon" type="image/x-icon" href="civil_protection.png">
    <link rel="icon" type="image/x-icon" href="../styles/civil_protection.png">
   
</head>
<body>
    <nav class="navbar-1">
        <?php
            $uri = $_SERVER['REQUEST_URI'];
            $segments = explode('/', $uri);
            if ($segments[2] === "admin" || $segments[2] === "citizen" || $segments[2] === "rescuer" || $segments[2] === "actions" ) {
                echo '<img src="./../styles/civil_protection.png" />';
            } else {
                echo '<img src="./styles/civil_protection.png" />';
            }
        ?>
        <h1>CEID Rescue Team</h1>
    </nav>
    <nav class="navbar-2">
        <!-- Toggle button -->
        <button class="navbar-toggle" onclick="toggleNavbar()">☰</button>
        <ul id="navbar-links">
        <?php
            // Check if the user is logged in
            if (isset($_SESSION['username'])) {
                // Display user-specific links based on user type
                switch ($_SESSION['usertype']) {
                    case 'admin':
                        echo '<a href="/WEB-24/admin/home.php">Αρχική</a>';
                        echo '<a href="/WEB-24/admin/apothiki.php">Προβολή Αποθήκης</a>';
                        echo '<a href="/WEB-24/admin/anakoinwseis.php">Ανακοινώσεις</a>';
                        echo '<a href="/WEB-24/admin/map.php">Χάρτης</a>';
                        echo '<a href="/WEB-24/admin/new_account.php">Δημιουργία Λογαριασμού Διασώστη</a>';

                        break;
                    case 'citizen':
                        echo '<a href="/WEB-24/citizen/home.php">Αρχική</a>';
                        echo '<a href="/WEB-24/citizen/anakoinwseis.php">Ανακοινώσεις</a>';
                        echo '<a href="/WEB-24/citizen/aithmata.php">Αιτήματα</a>';
                        echo '<a href="/WEB-24/citizen/istoriko.php">Ιστορικό Αιτημάτων</a>';
                        echo '<a href="/WEB-24/citizen/prosfores.php">Ιστορικό Προσφορών</a>';
                        
                        break;
                    case 'rescuer':
                        echo '<a href="/WEB-24/rescuer/home.php">Αρχική</a>';
                        echo '<a href="/WEB-24/rescuer/map.php">Χάρτης</a>';

                        break;
                    // Add more cases for other user types if needed
                }

                // Common links for all logged-in users
                echo '<a href="../actions/handle-logout.php">Αποσύνδεση</a>';
            } else {
                // Display links for users who are not logged in
                echo '<a href="/WEB-24/index.php">Login</a>';
                
            }
            ?>
        </ul>
    </nav>
    <script>
        function toggleNavbar() {
            var x = document.getElementById("navbar-links");
            if (x.style.display === "block") {
                x.style.display = "none";
            } else {
                x.style.display = "block";
            }
        }
    </script>
</body>
</html>
