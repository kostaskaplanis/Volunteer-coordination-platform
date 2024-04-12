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
?>
<!DOCTYPE html>
<html lang="el">

    <head>
        <meta http-equiv="content-type" content="text/html;charset=utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="home.css">
        <title>Αρχική σελίδα </title>
        <link rel="icon" type="image/x-icon" href="../styles/civil_protection.png">
        <style>
             * {
                font-family: "Lato", sans-serif;
            }
            .page-wrapper {
                margin: 40px 40px;
            }

            .page-wrapper .page-title {
                text-align: center;
            }
            
            .tiles-wrapper {
                display: flex;
                flex-wrap: wrap;
                /* grid-template-columns: repeat(1, 1fr); */
                gap: 10px;
                /* grid-auto-rows: minmax(100px, auto); */
            }

            .tile {
                box-sizing: border-box;
                margin: 40px auto;
                padding: 40px;
                border-radius: 10px;
                box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
                text-align: center;
                font-weight: bold;
                background: linear-gradient(#555, #000);
                color: #fff;
                width: 250px;
                height: 200px;
            }

            .title h2 {

            }

            @media (min-width: 768px) { /* Tablet view */
                .tiles-wrapper {
                    grid-template-columns: repeat(2, 1fr); /* 2 columns layout */
                }

                .page-wrapper {
                    margin: 40px 40px;
                }
            }

            @media (min-width: 1024px) { /* Desktop view */
                .tiles-wrapper {
                    grid-template-columns: repeat(5, 1fr); /* 4 columns layout */
                }

                .page-wrapper {
                    margin: 40px 200px;
                }
            }

            @media (min-width: 1200px) { /* Desktop view */
                .tiles-wrapper {
                    grid-template-columns: repeat(5, 1fr); /* 4 columns layout */
                }

                .page-wrapper {
                    margin: 40px 400px;
                }
            }

            .username {
                color: red;
            }
        </style>
    </head>
    <body>
    <?php include('../components/navbar.php') ?>
    <?php include('../test_connection.php') ?>

    <div class="page-wrapper">
        <h1 class="page-title"> Καλωσορίσατε <span class="username"><?php echo $_SESSION['username'] ?></span> </h1>

        <div class="tiles-wrapper" > 
            <div class="tile">
                <i class="fa-solid fa-bullhorn fa-2x"></i>
                <h2>Ανακοινώσεις</h2>
                <h2 class="announcements-num"></h2>
            </div>
        </div>
    </div>
       
    <script>
        async function fetchStats() {
            try {
                var res = await fetch('../actions/citizen_stats.php');
                console.log('res', res); 
                if (res.ok) {
                    var data = await res.json();
                    console.log('data', data);
                    
                    const announcementsNum = document.querySelector('.announcements-num');
                    announcementsNum.innerHTML = data.totalAnnouncements;
                }
            } catch (err) {
                console.log('err', err);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            fetchStats();
        });
    </script>

    </body>
    </html>
    
    