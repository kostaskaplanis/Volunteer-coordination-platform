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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Χάρτης Διαχειριστή</title>
  <link rel="icon" type="image/x-icon" href="../styles/civil_protection.png">

  <!-- Include Leaflet CSS and JS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-polylinedecorator/dist/leaflet.polylineDecorator.js"></script>
  <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
  <link rel="stylesheet" href="../styles/map.css" />
<style> * {
        font-family: "Lato", sans-serif;
    }
</style>
</head>
<body>
  <title>Χάρτης Leaflet</title>

  <?php include('../components/navbar.php'); ?>
  

<div id="container"> 
    <div id="map" class="map-admin"></div>
    <div id="filter-button"><i class="fa-solid fa-filter"></i></div>
    <div id="controls">
      <ul id="filters">
        <li>
          <button id="saveBaseLocation">Αποθήκευση Βάσης</button>
        </li>
        <li>
          <input type="checkbox" id="requestsFilter" onchange="applyFilters()" checked="true">
          <label for="requestsFilter">Eκκρεμή Αιτήματα</label>
        </li>
        <li>
          <input type="checkbox" id="offersFilter" onchange="applyFilters()"checked="true">
          <label for="offersFilter">Προσφορές</label>
        </li>      
        <li>
          <input type="checkbox" id="takenRequestsFilter" onchange="applyFilters()"checked="true">
          <label for="takenRequestsFilter">Aιτήματα που έχουν αναληφθεί</label>
        </li>      
        <li>
          <input type="checkbox" id="takenOffersFilter" onchange="applyFilters()" checked="true">
          <label for="takenOffersFilter">Προσφορές που έχουν αναληφθεί</label>
        </li>      
        <li>
          <input type="checkbox" id="activeCarFilter" onchange="applyFilters()" checked="true">
          <label for="activeCarFilter">Διαθέσιμα Οχήματα</label>
        </li>      
        <li>
          <input type="checkbox" id="inUseCarFilter" onchange="applyFilters()" checked="true">
          <label for="inUseCarFilter">Οχήματα με ενεργά  Tasks</label>
        </li>      
        <li>
          <input type="checkbox" id="straightLinesFilter" onchange="applyFilters()" checked="true">
          <label for="straightLinesFilter">Ευθείες Γραμμές</label>
        </li>    
      </ul>
    </div>
</div>

  <script>
    var offersData;
    var requestsData;
    var vehiclesData;
    var vehicleMarkers = [];
    var markers = L.markerClusterGroup();
    var baseLocation = [38.2466, 21.7346];
    var map = L.map('map').setView(baseLocation, 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
    }).addTo(map);

    var storageIcon = L.icon({
        iconUrl: '../styles/warehouse.png',
        iconSize: [30, 30],
        iconAnchor: [15, 30],
        popupAnchor: [0, -30],
        zIndexOffset: 1000
    });

    var storageMarker = L.marker(baseLocation, { icon: storageIcon, draggable: true }).addTo(map)
        .bindPopup("<b>Αποθήκη στην Πάτρα</b>").openPopup();

    storageMarker.on('click', function () {
        storageMarker.setZIndexOffset(1000);
        var newBaseLocation = event.target.getLatLng();
        baseLocation = [newBaseLocation.lat, newBaseLocation.lng];
        
    });

    function updateVehiclesStatus() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '../actions/updateVehicleStatus.php', true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        try {
          // Check if the response is not empty before parsing
          if (xhr.responseText.trim() !== '') {
            var data = JSON.parse(xhr.responseText);
            console.log('VehicleStatus Updated:', data);

            // Assuming fetchVehiclesData is defined somewhere
            fetchVehiclesData();
          } else {
            console.log('Empty response received.');
          }
        } catch (error) {
          console.error('Error parsing JSON:', error);
        }
      } else {
        console.error('Error updating vehicle status:', xhr.statusText);
      }
    }
  };
  xhr.send();
}

  
   
function fetchVehiclesData() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '../actions/getVehicles.php', true);
  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        var data = JSON.parse(xhr.responseText);
        console.log('Vehicles Data:', data);
        vehiclesData = data;

        // Once vehicle data is fetched, fetch citizen requests data
        fetchCitizenRequestsData();
        fetchOffersData();
      } else {
        console.error('Error fetching Vehicles Data:', xhr.statusText);
      }
    }
  };

  xhr.send();
}
 // Function to fetch CitizenOffers data from the backend using XMLHttpRequest
 function fetchOffersData() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '../actions/getOf.php', true);

  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        // Parse the response JSON
        var data = JSON.parse(xhr.responseText);
        console.log('CitizenOffers Data:', data);
        pendingOffers = data.filter(offer => offer.status !=='completed' );
        offersData = pendingOffers;
        console.log('pending Offers, ',pendingOffers);
        createVehicleMarkersAndPolylines(vehiclesData, offersData, "offer");
        createPolylines(vehiclesData, offersData ,"offer");
        createOfferMarkers(offersData);
      } else {
        console.error('Error fetching CitizenOffers Data:', xhr.statusText);
      }
    }
  };

  xhr.send();
}


function createOfferMarkers(offersData) {
  console.log('offerData IN createOfferMarkers=> ',offersData);
  offersData.forEach(offer => {
    var markerId = 'offer_' + offer.product_id; // Assuming offer has a unique identifier
    var existingMarker = markers.getLayer(markerId);
    // Define the default icon URL
    var iconUrl = '../styles/red-offer.png';

    if (offer.status === 'in progress') {
      iconUrl = '../styles/offer.png';
    }

    if (!existingMarker) {
      var marker = L.marker([parseFloat(offer.citizen_latitude), parseFloat(offer.citizen_longitude)], {
        icon: L.icon({
          iconUrl: iconUrl,
          iconSize: [32, 32],
          iconAnchor: [16, 32],
          popupAnchor: [0, -32]
        })
      });

      marker.bindPopup(generatePopupContent(offer, 'offer'));

      // Check if the offer is completed
      if (offer.status !== 'completed') {
        // Additional logic for incomplete offers, if needed
      }

      marker.addTo(map);
      markers.addLayer(marker);
    }
  });

  // Add the new markers to the map
  map.addLayer(markers);
}

function fetchCitizenRequestsData() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '../actions/getReq.php', true);

  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        var data = JSON.parse(xhr.responseText);
        console.log('CitizenRequests Data:', data);
        // Filter out completed requests
        pendingRequests = data.filter(request => request.status !== 'completed');
        console.log('pendingRequests: ', pendingRequests);
        requestsData = pendingRequests;

        // Now that both vehicle and request data are fetched, call the functions
        createVehicleMarkersAndPolylines(vehiclesData, requestsData, 'request');
        createPolylines(vehiclesData, requestsData);

        // Additional: Create markers for citizen requests
        createMarkers(requestsData);
      } else {
        console.error('Error fetching CitizenRequests Data:', xhr.statusText);
      }
    }
  };

  xhr.send();
}
    // Function to create vehicle markers on the map
    // Function to create vehicle markers and draw polylines on the map
     function createVehicleMarkersAndPolylines(vehiclesData, data, dataType) {
     vehiclesData.forEach(function (vehicleData) {
    var latitude = parseFloat(vehicleData.vehicle_latitude);
    var longitude = parseFloat(vehicleData.vehicle_longitude);
    
    if (!isNaN(latitude) && !isNaN(longitude)) {
     var vehicleMarker = L.marker([latitude, longitude], {
        icon: L.icon({
          iconUrl: '../styles/car.png',
          iconSize: [32, 32],
          iconAnchor: [16, 32],
          popupAnchor: [0, -32],
          vehicleStatus: vehicleData.Status
        }),
        draggable: false
      }).addTo(map);
      vehicleMarkers.push(vehicleMarker);

      fetchCargoInfo(vehicleData.VehicleName, vehicleMarker);

      // Find the matching data for the current vehicle based on dataType
      var matchingData = data.filter(function (item) {
        return item.VehicleName === vehicleData.VehicleName && item.status === 'in progress';
      });

      // Draw polylines for each matching data
      matchingData.forEach(function (matchingItem) {
        var polyline = L.polyline([vehicleMarker.getLatLng(), { lat: parseFloat(matchingItem.citizen_latitude), lng: parseFloat(matchingItem.citizen_longitude) }], {
          color: 'blue',
          weight: 5,
          opacity: 0.7,
          dashArray: [10, 5],
          lineCap: 'round',
          lineJoin: 'round'
        }).addTo(map);

       });
    }
    console.log('vehicleMarkers:', vehicleMarkers);

  });
}
function fetchCargoInfo(vehicleName, vehicleMarker) {
  // Fetch cargo information from the server using your PHP backend
  fetch('../actions/getVehicleCargo.php?vehicleName=' + encodeURIComponent(vehicleName))
    .then(response => response.json())
    .then(cargoData => {
      // Extract cargo information from cargoData
      const cargoInfo = cargoData[0];

      // Create cargo popup content
      var cargoPopupContent = `
        <div style="max-width: 300px;">
          <h3>Όχημα: ${cargoInfo.VehicleName}</h3>
          <hr>
          <ul>`;

      if (cargoInfo.cargo_info) {
        // Split cargo_info into individual items
        const cargoItems = cargoInfo.cargo_info.split(', ');

        cargoItems.forEach(function (cargoItem) {
          // Split each item into product name and quantity
          const [productName, quantity] = cargoItem.split(': ');
          cargoPopupContent += `<li><strong>Προϊόν</strong> ${productName} | <strong>Ποσότητα</strong>: ${quantity}</li>`;
        });
      } else {
        cargoPopupContent += '<li>Δεν υπάρχει διαθέσιμο φορτίο</li>';
      }

      cargoPopupContent += '</ul></div>';

      // Bind cargo popup to the vehicle marker
      vehicleMarker.bindPopup(cargoPopupContent);
      vehicleMarker.openPopup();
    })
    .catch(error => console.error('Error fetching cargo information:', error));
}

 // Function to create polylines connecting vehicles with requests or offers
function createPolylines(vehiclesData, data, dataType) {
  vehiclesData.forEach(function (vehicleData) {
    var matchingData = data.filter(function (item) {
      return item.VehicleName === vehicleData.VehicleName && item.status === 'in progress';
    });

    matchingData.forEach(function (matchingItem) {
      var vehicleLatLng = L.latLng(parseFloat(vehicleData.vehicle_latitude), parseFloat(vehicleData.vehicle_longitude));
      var itemLatLng = L.latLng(parseFloat(matchingItem.citizen_latitude), parseFloat(matchingItem.citizen_longitude));

      var polyline = L.polyline([vehicleLatLng, itemLatLng], {
        color: 'blue',
        weight: 5,
        opacity: 0.7,
        dashArray: [10, 5],
        lineCap: 'round',
        lineJoin: 'round'
      }).addTo(map);

    //   var arrow = L.polylineDecorator(polyline, {
    //     patterns: [
    //       { offset: 25, repeat: 50, symbol: L.Symbol.arrowHead({ pixelSize: 15, pathOptions: { fillOpacity: 1, weight: 0 } }) }
    //     ]
    //   }).addTo(map);
    });
  });
}

    // Example cargo data formatting function
    function formatCargoData(productName, quantity) {
    return `<strong>Προϊόν:</strong> ${productName} | <strong>Ποσότητα:</strong> ${quantity}`;
}

    function createMarkers(requestsData) {
      requestsData.forEach(request => {
        var markerId = 'request_' + request.request_id;
        var existingMarker = markers.getLayer(markerId);
         // Define the default icon URL
        var iconUrl = '../styles/location-pin.png';

        if (request.status === 'in progress') {
          iconUrl = '../styles/position.png';
        }
        if (!existingMarker) {
          var marker = L.marker([parseFloat(request.citizen_latitude), parseFloat(request.citizen_longitude)], {
            icon: L.icon({
              iconUrl: iconUrl,
              iconSize: [32, 32],
              iconAnchor: [16, 32],
              popupAnchor: [0, -32]
            })
          })
          marker.bindPopup(generatePopupContent(request, 'request'));

          // Check if the request is completed
          if (request.status !== 'completed') {
          }
          marker.addTo(map);
          markers.addLayer(marker);
        }
      });

      // Add the new markers to the map
      map.addLayer(markers);
    }

    function generatePopupContent(data, type) {
      // Create HTML content for the popup based on the updated request data
      var popupContent = `<strong>${data.citizen_name}</strong><br>`;
      popupContent += `Τηλέφωνο: ${data.citizen_phone}<br>`;
      popupContent += '<ul>';
      popupContent += `
          <li>
            <b>Προϊόν</b>: ${data.product_name}<br>
            ${type === 'offer' ? '<b>Ποσότητα</b>' : '<b>Πλήθος Ατόμων</b>'}: ${type === 'offer' ? data.quantity : data.number_of_people}<br>
            <b>Ημερομηνία Υποβολής</b>: ${data.date_submitted}<br>
            <b>Ημερομηνία Ανάληψης</b>: ${data.date_accepted ? data.date_accepted : 'Δεν έχει αναληφθεί'}<br>
            <b>Όχημα</b>: ${data.VehicleName || 'Δεν έχει ανατεθεί'}<br>
          </li><hr>`;

      popupContent += '</ul>';
      return popupContent;
    }
    
    document.getElementById('saveBaseLocation').addEventListener('click', saveBaseLocation);

// Συνάρτηση για αποθήκευση των συντεταγμένων του marker στη βάση δεδομένων
function saveBaseLocation() {
    var markerLatLng = storageMarker.getLatLng();

    // Εκτέλεση αιτήματος προς το back-end για αποθήκευση των συντεταγμένων
    fetch('../actions/saveBaseLocation.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            baseLatitude: markerLatLng.lat,
            baseLongitude: markerLatLng.lng,
        }),
    })
        .then(response => response.json())
        .then(data => {
            console.log('Αποθήκευση επιτυχής:', data);
            alert('Οι συντεταγμένες αποθηκεύτηκαν επιτυχώς!');
            // Αποθήκευση των συντεταγμένων στο localStorage
            localStorage.setItem('baseLocation', JSON.stringify(markerLatLng));
            storageMarker.setLatLng(markerLatLng);
            // Ενημέρωση του χάρτη
            window.location.reload(); // Reload the page

            
        })
        .catch(error => {
            console.error('Σφάλμα κατά την αποθήκευση:', error);
            alert('Σφάλμα κατά την αποθήκευση. Προσπαθήστε ξανά.');
        });
}

// Ενημέρωση του χάρτη
function updateMap() {
    // Remove all markers from the map
    clearVehicleMarkers();
    updateVehiclesStatus();
    fetchVehiclesData(); 
}

function clearVehicleMarkers() {
  vehicleMarkers.forEach(function (marker) {
    map.removeLayer(marker);
  });

  map.eachLayer(function (layer) {
    if (layer instanceof L.Polyline && layer.options.className === 'vehiclePolyline') {
      map.removeLayer(layer);
    }
  });
  // Clear the vehicleMarkers array
  vehicleMarkers = [];
}

function applyFilters() {
    console.log('vehicleMarkers inside applyFilters:', vehicleMarkers);

  // Logic to show/hide markers and polylines based on the checkbox state
  var requestsFilter = document.getElementById('requestsFilter');
  var offersFilter = document.getElementById('offersFilter');
  var takenRequestsFilter = document.getElementById('takenRequestsFilter');
  var straightLinesFilter = document.getElementById('straightLinesFilter');
  var takenOffersFilter = document.getElementById('takenOffersFilter');
  
  var showRequests = requestsFilter.checked;
  var showOffers = offersFilter.checked;
  var showTaskReq = takenRequestsFilter.checked;
  var showTaskOf = takenOffersFilter.checked;
  var showStraightLines = straightLinesFilter.checked;
  var showActiveCar = activeCarFilter.checked;
  var showInUseCar = inUseCarFilter.checked;
  // Iterate through markersGroup and other layers in the map and show/hide based on the filter
  markers.eachLayer(layer => {
    if (layer instanceof L.Marker) {
      // Handle markers in markersGroup
      var iconUrl = layer.options.icon.options.iconUrl;
      
      if ((showRequests && iconUrl === '../styles/location-pin.png') ||
          (showOffers && iconUrl === '../styles/red-offer.png') ||
          (showTaskReq && iconUrl === '../styles/position.png') ||
          (showTaskOf && iconUrl === '../styles/offer.png')) {
        map.addLayer(layer);
      } else {
        map.removeLayer(layer);
      }
    }
  });
  vehicleMarkers.forEach(marker => {
        if (marker instanceof L.Marker) {
            var iconUrl = marker.options.icon.options.iconUrl;
            var vehicleStatus = marker.options.icon.options.vehicleStatus; // Check this property

            console.log('Car status=>', vehicleStatus);

            if ((showActiveCar && iconUrl === '../styles/car.png' && vehicleStatus === 'Available') ||
                (showInUseCar && iconUrl === '../styles/car.png' && vehicleStatus === 'In Use')) {
                // Set marker opacity to 1 to show it
                marker.setOpacity(1);
                
            } else {
                // Set marker opacity to 0 to hide it
                marker.setOpacity(0);
                marker.closePopup();
            }
        }
    });
  // Iterate through other layers in the map and show/hide based on the filter
  map.eachLayer(layer => {
    if (layer instanceof L.Polyline) {
      // Handle polylines directly added to the map
      if ((showRequests || showOffers || showTaskReq) && showStraightLines) {
        // Show the polyline by resetting its style
        layer.setStyle({ opacity: 0.7, weight:5, lineCap:'round', lineJoin:'round',dashArray: [10, 5], color: 'blue' });
       
        // Check if the layer has a decorator (arrow) and show it
        if (layer._decorators) {
          layer._decorators.forEach(decorator => decorator.addTo(map));
        }
      } else {
        // Hide the polyline by resetting its style
        layer.setStyle({ opacity: 0, dashArray: [0, 0], color: 'transparent' });

        // Check if the layer has a decorator (arrow) and hide it
        if (layer._decorators) {
          layer._decorators.forEach(decorator => map.removeLayer(decorator));
        }
      }
    }
  });
}

// Ενημέρωση του χάρτη κατά την φόρτωση της σελίδας
document.addEventListener('DOMContentLoaded', function () {
    // Ανάκτηση των συντεταγμένων από το localStorage
    var storedLocation = localStorage.getItem('baseLocation');
    if (storedLocation) {
        var parsedLocation = JSON.parse(storedLocation);
        // Εφαρμογή των συντεταγμένων στον marker
        storageMarker.setLatLng(parsedLocation);
        // Ενημέρωση του χάρτη μετά τη φόρτωση της σελίδας
        updateMap();
    }
});

// Toggle filter menu view
     function toggleFiltersView() {
      var filters = document.getElementById("filters");
      filters.style.display = (filters.style.display === "none" || filters.style.display === "") ? "block" : "none";
    }

    const enableFilterBtn = document.querySelector('#filter-button');
    enableFilterBtn.addEventListener('click', toggleFiltersView)
  
   
  </script>
</body>
</html>
