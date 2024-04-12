<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/login-form.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <title>Εγγραφή</title>
    <link rel="icon" type="image/x-icon" href="styles/civil_protection.png">
    <style>
        #map {
            height: 400px;
        }
    </style>
</head>
<body>
    <?php include('components/navbar.php')?>
    
    <!-- Your existing form with hidden input fields -->
    <form id="myForm" action="actions/retrieve.php" method="post">
        <h2>Εγγραφή Πολίτη</h2>        
        
        <div class="input-group">
            <label for="full_name"><b>Όνοματεπώνυμο</b></label>
            <input type="text" placeholder="Εισάγετε Όνοματεπώνυμο" name="full_name" id="full_name" required>
        </div>
        
        <div class="input-group">
        <label for="phone"><b>Κινητό</b></label>
        <input type="text" id="phone" name="phone" placeholder="123-456-7890" pattern="[0-9]{10}" required>
        </div>

        <div class="input-group">
            <label for="email"><b>Email</b></label>
            <input type="text" placeholder="Εισάγετε Email" name="email" id="email" required>
        </div>

        <div class="input-group">
          <label for="username"><b>Όνομα χρήστη</b></label>
          <input type="text" placeholder="Εισάγετε όνομα χρήστη" name="username" id="username" required>
        </div>

        <div class="input-group">
          <label for="password"><b>Κωδικός</b></label>
          <input type="password" placeholder="Εισάγετε κωδικό" name="password" id="password" required>
        </div>
         
         
         <input type="hidden" name="latitude" id="hiddenLatitude">
        <input type="hidden" name="longitude" id="hiddenLongitude">
        
      
<!-- Other form fields go here -->

    <input class="submit-button" type="submit" value="Εγγραφή">
    <h5>Έχετε ήδη λογαριασμό; <a href="index.php">Συνδεθείτε εδώ!</a></h5>
</form>
<div id="map"></div>

    <p>Latitude: <span id="latitude">0</span></p>
    <p>Longitude: <span id="longitude">0</span></p>


    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        var map = L.map('map').setView([0, 0], 2); // Default view at (0, 0) with zoom level 2

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        var marker;

        // Function to handle map click event
        function onMapClick(e) {
            // If a marker exists, remove it before adding a new one
            if (marker) {
                map.removeLayer(marker);
            }

            // Add a new marker at the clicked location
            marker = L.marker(e.latlng).addTo(map);

            // Update the latitude and longitude values on the page
            document.getElementById('latitude').textContent = e.latlng.lat;
            document.getElementById('longitude').textContent = e.latlng.lng;

            // Update the hidden input fields with latitude and longitude values
            document.getElementById('hiddenLatitude').value = e.latlng.lat;
            document.getElementById('hiddenLongitude').value = e.latlng.lng;
        }

        // Set up the click event listener on the map
        map.on('click', onMapClick);

        // Get the user's location using the Geolocation API
        if ('geolocation' in navigator) {
            navigator.geolocation.getCurrentPosition(
                function (position) {
                    var latitude = position.coords.latitude;
                    var longitude = position.coords.longitude;

                    // Set the map view to the user's location
                    map.setView([latitude, longitude], 15); // Zoom level: 15

                    // Add a marker at the user's location
                    marker = L.marker([latitude, longitude]).addTo(map)
                        .bindPopup('Your Location').openPopup();

                    // Update the latitude and longitude values on the page
                    document.getElementById('latitude').textContent = latitude;
                    document.getElementById('longitude').textContent = longitude;

                    // Update the hidden input fields with latitude and longitude values
                    document.getElementById('hiddenLatitude').value = latitude;
                    document.getElementById('hiddenLongitude').value = longitude;
                },
                function (error) {
                    console.error('Error getting geolocation:', error.message);
                    // Default view if geolocation is not available or denied
                    map.setView([0, 0], 2); // Default view at (0, 0) with zoom level 2
                }
            );
        } else {
            console.error('Geolocation is not supported by your browser');
        }

        // Optional: Submit the form programmatically if needed
        function submitForm() {
            document.getElementById('myForm').submit();
        }
    </script>
    
</body>
</html>
