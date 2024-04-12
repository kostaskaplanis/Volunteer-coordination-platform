<?php
session_start();

// Check if the user is not logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Redirect to the login page if the user is not logged in
    header("Location: ../index.php");
    exit();
}

// Check if the user is not an admin
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'rescuer') {
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
  <title>Χάρτης Διασώστη</title>
  <!-- Include Leaflet CSS and JS -->

  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
  <link rel="stylesheet" href="../styles/map.css" />
  <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
  <script src="https://unpkg.com/leaflet-polylinedecorator/dist/leaflet.polylineDecorator.js"></script>
  <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://unpkg.com/leaflet.markercluster/dist/leaflet.markercluster.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.Default.css" />
  <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster/dist/MarkerCluster.css" />
  <style>
        * {
                font-family: "Lato", sans-serif;
        }
  </style>
<script>
      var markersGroup = L.markerClusterGroup();
      var pendingRequests = [];
      var processedRequests = {};
      var existingMarker;
      var rescuerData;
      var vehiclesData;
     
  function formatCargoData(productName, quantity) {
    return `<strong>Προϊόν:</strong> ${productName} | <strong>Ποσότητα:</strong> ${quantity}`;
}

function fetchVehiclesData() {
var xhr = new XMLHttpRequest();
xhr.open('GET', '../actions/forVehicles.php', true);

xhr.onreadystatechange = function () {
if (xhr.readyState === XMLHttpRequest.DONE) {
if (xhr.status === 200) {
  var data = JSON.parse(xhr.responseText);
  console.log('Vehicles Data:', data);
   vehiclesData = data;
  // Create rescuerMarker using the data
  fetchCitizenRequestsData()
  fetchOffersData();
  createRescuerMarker(data);
  //createVehicleMarkersAndPolylines();
} else {
  console.error('Error fetching Vehicles Data:', xhr.statusText);
}
}
};

xhr.send();
}
function createRescuerMarker(vehiclesData) {


if (Array.isArray(vehiclesData) && vehiclesData.length > 0) {
  rescuerData = vehiclesData[0];
  var latitude = parseFloat(rescuerData.vehicle_latitude);
  var longitude = parseFloat(rescuerData.vehicle_longitude);

  if (!isNaN(latitude) && !isNaN(longitude)) {
      rescuerMarker = L.marker([latitude, longitude], {
          icon: L.icon({
              iconUrl: '../styles/car.png',
              iconSize: [32, 32],
              iconAnchor: [16, 32],
              popupAnchor: [0, -32]
          }),
          clickable: true,
          draggable: true
      }).addTo(map);

      var cargoPopupContent = `
        <div style="max-width: 300px;">
          <h3>Όχημα: ${rescuerData.VehicleName}</h3>
          <hr>
          <ul>`;

      for (var i = 1; i < vehiclesData.length; i++) {
        var productData = vehiclesData[i];
        var productName = productData.product_name;
        var quantity = productData.rescuer_quantity;
        cargoPopupContent += `<li>${formatCargoData(productName, quantity)}</li>`;
      }

      cargoPopupContent += '</ul></div>';

      rescuerMarker.bindPopup(cargoPopupContent);
      rescuerMarker.openPopup();
    }
      rescuerMarker.on('dragend', function (event) {
          console.log('Rescuer Marker Dragged to:', event.target.getLatLng());
          var updatedCoords = event.target.getLatLng();

          var isNearbyBase = isRescuerNearby(updatedCoords, { lat: baseLatitude, lng: baseLongitude }, 'base');

          if (isNearbyBase) {
              console.log('Rescuer is nearby the base.');
              document.getElementById('load-items-button').style.display = 'block';
          } else {
              document.getElementById('load-items-button').style.display = 'none';
          }

          for (var i = 0; i < trackedTasks.length; i++) {
              var task = trackedTasks[i];
              var isNearby = isRescuerNearby(updatedCoords, task, 'task');

              if (isNearby) {
                  console.log('Rescuer is nearby task:', task.title);
              }
          }
       
          updatePolylines(event.target.getLatLng());
          updateTasksPanel();

          // Καλέστε τη συνάρτηση για ανανέωση του περιεχομένου του popup με χρήση AJAX
          
      });
  }
}

// Καλέστε τη συνάρτηση updatePopupContent όταν αλλάξουν τα δεδομένα
$('#update-button').on('click', function () {
updatePopupContent();
});

// Συνάρτηση για ανανέωση δεδομένων στο popup με χρήση AJAX
function updatePopupContent() {
$.ajax({
url: '../actions/forVehicles.php',
method: 'GET',
dataType: 'json',
success: function (data) {

 var cargoPopupContent = `
        <div style="max-width: 300px;">
          <h3>Όχημα: ${rescuerData.VehicleName}</h3>
          <hr>
          <ul>`;

      for (var i = 1; i < data.length; i++) {
        var productData = data[i];
        var productName = productData.product_name;
        var quantity = productData.rescuer_quantity;
        cargoPopupContent += `<li>${formatCargoData(productName, quantity)}</li>`;
      }

      cargoPopupContent += '</ul></div>';

// Χρησιμοποιήστε το setPopupContent για να ενημερώσετε το περιεχόμενο του popup
if (rescuerMarker) {
  rescuerMarker.setPopupContent(cargoPopupContent);
}
},
error: function (error) {
console.error('Σφάλμα κατά τη φόρτωση των νέων δεδομένων:', error);
}
});
}
  
function fetchCitizenRequestsData() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '../actions/get_requests.php', true);

  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        var data = JSON.parse(xhr.responseText);

        // Accessing user_id from the first row of the response
        var user_id = data[0].user_id;
        console.log('User ID:', user_id);

        console.log('CitizenRequests Data:', data);
        // Filter out completed requests
        pendingRequests = data.filter(request => request.status ==='new' ||(request.status === 'in progress' && request.rescuer_id === user_id));
        console.log('pendingRequests: ', pendingRequests);
        requestsData = pendingRequests;
        

        // Check if there are requests in progress and add them to trackedTasks
        requestsData.forEach(request => {
    if (request.status === 'in progress') {
        var newTask = {
            request_id: request.request_id,
            title: `Αίτημα για ${request.citizen_name}`,
            lat: request.citizen_latitude,
            lng: request.citizen_longitude,
            citizenName: request.citizen_name,
            citizenPhone: request.citizen_phone,
            dateSubmitted: request.date_submitted,
            productName: request.product_name,
            number_of_people: request.number_of_people,
            handled: false,
            type: 'request',
            VehicleName: rescuerData.VehicleName,
            status: 'in progress'
        };

        // Push the new task to trackedTasks
        trackedTasks.push(newTask);
    }
    updateTasksPanel();
    showTrackedTasks();
});

        // Now that both vehicle and request data are fetched, call the functions
        //createVehicleMarkersAndPolylines(vehiclesData, requestsData, 'request');
       // createPolylines(vehiclesData, requestsData);

        // Additional: Create markers for citizen requests
        createMarkers(requestsData);
      } else {
        console.error('Error fetching CitizenRequests Data:', xhr.statusText);
      }
    }
  };

  xhr.send();
}

function createMarkers(requestsData) {
      requestsData.forEach(request => {
        var markerId = 'request_' + request.request_id;
        var existingMarker = markersGroup.getLayer(markerId);
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
          marker.request_id = request.request_id;

         
          marker.addTo(map);
          markersGroup.addLayer(marker);
        }
      });

      // Add the new markers to the map
      map.addLayer(markersGroup);
    }

// Function to fetch CitizenOffers data from the backend using XMLHttpRequest
function fetchOffersData() {
  var xhr = new XMLHttpRequest();
  xhr.open('GET', '../actions/get_offers.php', true);

  xhr.onreadystatechange = function () {
    if (xhr.readyState === XMLHttpRequest.DONE) {
      if (xhr.status === 200) {
        // Parse the response JSON
        var data = JSON.parse(xhr.responseText);
        var user_id = data[0].user_id;
        console.log('User Id s ',user_id);
        console.log('CitizenOffers Data:', data);
        pendingOffers = data.filter(offer => offer.status ==='new' ||(offer.status === 'in progress' && offer.rescuer_id === user_id) );
        offersData = pendingOffers;
        console.log('pending Offers, ',pendingOffers);
        
        offersData.forEach(offer => {
    if (offer.status === 'in progress') {
        var newTask = {
            offer_id: offer.offer_id,
            title: `Προσφορά για ${offer.citizen_name}`,
            lat: offer.citizen_latitude,
            lng: offer.citizen_longitude,
            citizenName: offer.citizen_name,
            citizenPhone: offer.citizen_phone,
            dateSubmitted: offer.date_submitted,
            productName: offer.product_name,
            quantity: offer.quantity,
            handled: false,
            type: 'offer',
            VehicleName: rescuerData.VehicleName,
            status: 'in progress'
        };

        // Push the new task to trackedTasks
        trackedTasks.push(newTask);
    }
    updateTasksPanel();
    showTrackedTasks();
});

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
    var existingMarker = markersGroup.getLayer(markerId);
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
      marker.offer_id = offer.offer_id;
      

      marker.addTo(map);
      markersGroup.addLayer(marker);
    }
  });

  // Add the new markers to the map
  map.addLayer(markersGroup);
}

function generatePopupContentForTasks(trackedTasks, task_type) {
  console.log('generatePopupContentForTasks', trackedTasks);
  let popupContent = '';
  console.log('PendingREQ;', pendingRequests);

  trackedTasks.forEach(task => {
    if ((task.type === 'request' && task.request_id === task_type) || (task.type === 'offer' && task.offer_id === task_type)) {
      popupContent += `
        <div>
          <b>Τίτλος</b>: ${task ? task.title : 'N/A'}<br>
          <b>Πολίτης</b>:${task ? task.citizenName : 'N/A'}<br>
          <b>Τηλέφωνο Πολίτη</b>: ${task ? task.citizenPhone : 'N/A'}<br>
          <b>Ημερομηνία Ανάληψης</b>: ${task ? task.date_accepted : 'N/A'}<br>
          <b>Κατάσταση</b>: ${task ? (task.handled ? 'Ολοκληρωμένο' : 'Εκκρεμεί') : 'N/A'}<br>
          <b>Όχημα</b>: ${task ? rescuerData.VehicleName : 'N/A'}<br>`;

      console.log('task.status: ', task.status);
      if (task.status === 'new') {
        popupContent += `<button onclick="handleTrack${task.type.charAt(0).toUpperCase() + task.type.slice(1)}(this, '${task.type === 'request' ? task.request_id : task.offer_id}', '${task.citizenName}', '${task.citizenPhone}', '${task.dateSubmitted}','${task.date_accepted}', '${task.productName}', ${task.type === 'request' ? task.number_of_people : task.quantity}, ${task.lat}, ${task.lng},'${rescuerData.VehicleName}')">Track ${task.type.charAt(0).toUpperCase() + task.type.slice(1)}</button>`;
      }

      popupContent += '</div><hr>';
    }
  });

  return popupContent;
}
function applyFilters() {
  showTrackedTasks();
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

  // Iterate through markersGroup and other layers in the map and show/hide based on the filter
  markersGroup.eachLayer(layer => {
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

  // Iterate through other layers in the map and show/hide based on the filter
  map.eachLayer(layer => {
    if (layer instanceof L.Polyline) {
      // Handle polylines directly added to the map
      if ((showRequests || showOffers || showTaskReq) && showStraightLines) {
        map.addLayer(layer);
      } else {
        // Remove both the polyline and its decorator
        map.removeLayer(layer);
        // Check if the layer has a decorator (arrow) and remove it
        if (layer._decorators) {
          layer._decorators.forEach(decorator => map.removeLayer(decorator));
        }
      }
    }
  });
}

function initMap() {
  
      // Set initial map view
      map = L.map('map').setView([38.246639, 21.734573], 12);

      // Add OpenStreetMap tile layer
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
      }).addTo(map);
      map.addLayer(markersGroup);
      // Add marker for base
      var baseMarker = L.marker([0, 0]).addTo(map); // Οι προκαθορισμένες συντεταγμένες είναι (0, 0)
      
      // Συνάρτηση για ανανέωση του marker με βάση τις συντεταγμένες από το PHP script
function updateMarker() {
  $.ajax({
    url: '../actions/base.php', // Το URL του PHP script που ανακτά τις συντεταγμένες
    method: 'GET',
    dataType: 'json',
    success: function(data) {
      // Επιτυχία: Ενημέρωση των συντεταγμένων του marker
      baseLatitude = data.base_latitude;
      baseLongitude = data.base_longitude;
      
      var newLatLng = L.latLng(baseLatitude, baseLongitude);
       baseMarker.setLatLng(newLatLng);
      baseMarker.setIcon(L.icon({ iconUrl: '../styles/warehouse.png', iconSize: [32, 32] }));
      baseMarker.bindPopup('Τοποθεσία Βάσης').openPopup();
      // Add a circle based on the baseMarker's location
      var circle = L.circle(baseMarker.getLatLng(), {
          color: 'red',
          fillColor: '#f03',
          fillOpacity: 0.5,
          radius: 100
      }).addTo(map);

      // Προσθήκη console.log για εμφάνιση των συντεταγμένων στην κονσόλα
      console.log('Νέες συντεταγμένες της βάσης:', baseLatitude, baseLongitude);
    },
    error: function() {
      // Σφάλμα: Αδυναμία ανάκτησης των συντεταγμένων
      console.log('Σφάλμα κατά την ανάκτηση των συντεταγμένων.');
    }
  });
}
      updateMarker();
      fetchVehiclesData();
}
    
  </script>
</head>
<body>
  <?php include('../components/navbar.php');?>
  <div id="container">
    <div id="map" class="map-rescuer"></div>
    <div id="filter-button"><i class="fa-solid fa-filter"></i></div>
    <div id="controls" class="show">
      <ul id="filters">
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
          <input type="checkbox" id="straightLinesFilter" onchange="applyFilters()" checked="true">
          <label for="straightLinesFilter">Ευθείες Γραμμές</label>
        </li>
      </ul>
    </div>
  </div>
  <div id="info-wrapper">
    <div id="tasksPanel"></div>
    <div id='load-items-button' style='display: none;'>
      <button onclick="redirectToFortio()">Φόρτωση Ειδών</button>
    </div>
  </div>
<script>
    function redirectToFortio() {
        window.location.href = 'fortio.php';
    }
</script>
  <script>
      var map;
      // Ορίστε τις μεταβλητές στο επίπεδο του κώδικα (global)
      var baseLatitude, baseLongitude;
      var rescuerMarker;
      var baseMarker;
      var vehiclesData = [];
      var taskMarkers = [];
      var trackedTasks = [];
      var maxTasks = 4;
      var rescuerData;
   
      function handleTrackRequest(button, request_id, citizenName, citizenPhone, dateSubmitted, dateAccepted, productName, number_of_people, latitude, longitude, status) {
  // Logic to track the request

  // Check if there are less than 4 unhandled tasks
  var unhandledTasks = trackedTasks.filter(task => !task.handled);
  if (unhandledTasks.length < 4) {
    alert(`Ο διασώστης αναλαμβάνει το αίτημα του πολίτη ${citizenName}.`);

    // Update the button visibility and set the flag
    button.style.display = 'none';

    // Create a new task excluding date_accepted
    var newTask = {
      request_id: request_id,
      title: `Αίτημα για ${citizenName}`,
      lat: latitude,
      lng: longitude,
      citizenName: citizenName,
      citizenPhone: citizenPhone,
      dateSubmitted: dateSubmitted,
      productName: productName,
      number_of_people: number_of_people,
      handled: false,
      type: 'request',
      VehicleName: rescuerData.VehicleName,
      status: 'in progress'
    };

    // Push the new task to trackedTasks
    trackedTasks.push(newTask);

    // Find the marker associated with the tracked task
    var marker = findMarkerById(request_id,'request');

    if (marker) {
      console.log('Tracked Tasks:', trackedTasks);

      // Update the marker's icon to the custom tracked-marker icon
      marker.setIcon(L.icon({
        iconUrl: '../styles/position.png', // Specify the path to your custom marker icon
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32],
      }));
    } else {
      console.warn('Marker not found for request_id:', request_id);
    }

    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();
    console.log('url update! Req_ID: ', request_id);

    // Set the request type and URL
    xhr.open('POST', '../actions/updateRequest.php', true);

    // Create a FormData object and append the request_id
    var formData = new FormData();
    formData.append('request_id', request_id);

    // Define the function to be called when the request is complete
    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        // After a successful request, execute additional code if needed
        console.log('Request updated successfully');
        console.log('Response:', xhr.responseText);

        // Parse the response to get the latest date_accepted
        var responseJson = JSON.parse(xhr.responseText);
        var latestDateAccepted = responseJson.date_accepted;

        // Update the date_accepted in the new task
        newTask.date_accepted = latestDateAccepted;
        console.log('TRACKED TASKS: ', trackedTasks);
        // Update the marker's popup content after date_accepted is obtained
        if (marker) {
          marker.bindPopup(generatePopupContentForTasks(trackedTasks, request_id));
        } else {
          console.warn('Marker not found for request_id:', request_id);
        }
        

      } else {
        console.error('Error updating request. Status:', xhr.status, 'Response:', xhr.responseText);
      }
    };

    // Define the function to be called in case of an error
    xhr.onerror = function () {
      console.error('Network error during the request');
    };

    // Send the request to update the request with the FormData
    xhr.send(formData);
  }
  showTrackedTasks();
  updateTasksPanel();
}




function handleTrackOffer(button, offer_id, citizenName, citizenPhone, dateSubmitted, dateAccepted, productName, quantity, latitude, longitude, status, vehicleName) {
  // Logic to track the offer

  
  var unhandledTasks = trackedTasks.filter(task => !task.handled);
  if (unhandledTasks.length < 4) {
    alert(`Ο διασώστης αναλαμβάνει την προσφορά για ${citizenName}.`);

    // Update the button visibility and set the flag
    button.style.display = 'none';

    // Create a new offer excluding date_accepted
    var newTask = {
      offer_id: offer_id, // Assuming you have an 'id' property in your task object
      title: `Προσφορά για ${citizenName}`,
      lat: latitude,
      lng: longitude,
      citizenName: citizenName,
      citizenPhone: citizenPhone,
      dateSubmitted: dateSubmitted,
      productName: productName,
      quantity: quantity,
      handled: false,
      type: 'offer',
      VehicleName: rescuerData.VehicleName,
      status: 'in progress'
    };

    // Push the new offer to trackedTasks
    trackedTasks.push(newTask);

    // Find the marker associated with the tracked offer
    var marker = findMarkerById(offer_id,'offer');

    if (marker) {
      console.log('Eimai mesa sthn allagh toy marker:', trackedTasks);

 
      // Update the marker's icon to the custom tracked-marker icon
      marker.setIcon(L.icon({
        iconUrl: '../styles/offer.png', // Specify the path to your custom marker icon for offers
        iconSize: [32, 32],
        iconAnchor: [16, 32],
        popupAnchor: [0, -32],
      }));
    } else {
      console.warn('Marker not found for offer_id:', offer_id);
    }

    // Create a new XMLHttpRequest object
    var xhr = new XMLHttpRequest();
    console.log('url update! Offer_ID: ', offer_id);

    // Set the request type and URL
    xhr.open('POST', '../actions/updateOffers.php', true);

    // Create a FormData object and append the offer_id
    var formData = new FormData();
    formData.append('offer_id', offer_id);

    // Define the function to be called when the request is complete
    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        // After a successful request, execute additional code if needed
        console.log('Offer updated successfully');
        console.log('Response:', xhr.responseText);

        // Parse the response to get the latest date_accepted
        var responseJson = JSON.parse(xhr.responseText);
        var latestDateAccepted = responseJson.date_accepted;

        // Update the date_accepted in the new offer
        newTask.date_accepted = latestDateAccepted;
        console.log('TRACKED OFFERS: ', trackedTasks);

        // Update the marker's popup content after date_accepted is obtained
        if (marker) {
          marker.bindPopup(generatePopupContentForTasks(trackedTasks, offer_id));
        } else {
          console.warn('Marker not found for offer_id:', offer_id);
        }
      }
    };

    // Define the function to be called in case of an error
    xhr.onerror = function () {
      console.error('Network error during the request');
    };

    // Send the request with the FormData
    xhr.send(formData);
  } else {
    alert('Ο διασώστης έφτασε το όριο των αναλαμβανόμενων tasks (3).');
  }
  showTrackedTasks();
  updateTasksPanel();
}



function showTrackedTasks() {
      // Clear existing polylines
         // Check if the filter checkbox is checked before updating polylines
  var straightLinesFilter = document.getElementById('straightLinesFilter');
  var showStraightLines = straightLinesFilter.checked;

  if (showStraightLines) {
      clearTrackedTasks();
     
      // Draw polylines for all tracked tasks using PolylineDecorator
      trackedTasks.forEach(function(task) {
        if (task.status === 'in progress') {
        var polyline = L.polyline([[rescuerMarker.getLatLng().lat, rescuerMarker.getLatLng().lng], [task.lat, task.lng]], {
          color: 'blue',
          weight: 5,
          opacity: 0.7,
          dashArray: [10, 5],
          lineCap: 'round',
          lineJoin: 'round'
        }).addTo(map);

       
      }
      });
    }
      // Update the tasks panel
      updateTasksPanel();
    }

    function clearTrackedTasks() {
      // Remove existing polylines and arrows
      map.eachLayer(function (layer) {
        if (layer instanceof L.Polyline || layer instanceof L.PolylineDecorator) {
          layer.remove();
        }
      });
    }


    function updateTasksPanel() {
      console.log('Updating tasks panel');
      console.log('Tracked tasks: ',trackedTasks);
  // Get the tasksPanel element
  var tasksPanel = document.getElementById('tasksPanel');

  // Clear existing content
  tasksPanel.innerHTML = '';

  // Create a list of tracked tasks
  var tasksList = document.createElement('ul');
  tasksList.id = 'tasksList';

  console.log(trackedTasks);
  // Filter and display only tasks with status === 'in progress'
  var inProgressTasks = trackedTasks.filter(function(task) {
        return task.status === 'in progress';
    });


  inProgressTasks.forEach(function (task) {
    
    var taskItem = document.createElement('li');
    taskItem.classList.add('taskItem');

    var taskTitle = document.createElement('div');
    taskTitle.classList.add('taskTitle');
    taskTitle.innerHTML = `<b>${task.title}</b>`;
    taskItem.appendChild(taskTitle);

    var taskInfo = document.createElement('div');
    taskInfo.classList.add('taskInfo');
    
    // Check the task type and display the appropriate information
    if (task.type === 'request') {
      taskInfo.innerHTML = `
        <p>Όνομα πολίτη: ${task.citizenName}</p>
        <p>Τηλέφωνο: ${task.citizenPhone}</p>
        <p>Ημερομηνία Υποβολής: ${task.dateSubmitted}</p>
        <p>Προϊόν: ${task.productName}</p>
        <p>Πλήθος ατόμων: ${task.number_of_people}</p>
      `;
    } else if (task.type === 'offer') {
      taskInfo.innerHTML = `
        <p>Όνομα πολίτη: ${task.citizenName}</p>
        <p>Τηλέφωνο: ${task.citizenPhone}</p>
        <p>Ημερομηνία Υποβολής: ${task.dateSubmitted}</p>
        <p>Προϊόν: ${task.productName}</p>
        <p>Ποσότητα: ${task.quantity}</p>
      `;
    }

    taskItem.appendChild(taskInfo);

    var taskStatus = document.createElement('div');
    taskStatus.classList.add('taskStatus');
    taskStatus.innerHTML = `Handled: ${task.handled ? 'Yes' : 'No'}`;
    taskItem.appendChild(taskStatus);

    var taskActions = document.createElement('div');
    taskActions.classList.add('taskActions');

    if (!task.handled) {
      // Create Complete button
      var completeButton = document.createElement('button');
      completeButton.classList.add('completeButton');
      completeButton.textContent = 'Complete';
      completeButton.style.display = isRescuerNearby(rescuerMarker.getLatLng(), task) ? 'block' : 'none';

      completeButton.addEventListener('click', function () {
          if (task.request_id) {
              completeTask(task.request_id, 'request');
          } else if (task.offer_id) {
              completeTask(task.offer_id, 'offer');
          }
      });


      // Append the button to the task element or wherever you want to add it
      taskItem.appendChild(completeButton);
      taskActions.appendChild(completeButton);

      // Create Cancel button
      var cancelButton = document.createElement('button');
      cancelButton.classList.add('cancelButton');
      cancelButton.textContent = 'Cancel';

      cancelButton.addEventListener('click', function () {
  // Assuming task object contains either request_id or offer_id and title properties
  if (task.request_id) {
    cancelTask(task.request_id, 'request', task.title);
    } else if (task.offer_id) {
    cancelTask(task.offer_id,'offer', task.title);
  }
});

taskActions.appendChild(cancelButton);
    }

    taskItem.appendChild(taskActions);

    tasksList.appendChild(taskItem);
  });

  // Append the list to the tasksPanel
  tasksPanel.appendChild(tasksList);
}

// Function to check if the rescuer is nearby a task or the base
function isRescuerNearby(rescuerLatLng, target, targetType) {
  // Check if both coordinates are valid
  if (!rescuerLatLng || !target || (!target.lat && !target.lng)) {
    console.error('Invalid coordinates or target:', rescuerLatLng, target);
    return false;
  }

  // Set the default distance threshold to 50 meters
  let distanceThreshold = 50;

  // Check the targetType and update the distance threshold accordingly
  if (targetType === 'base') {
    distanceThreshold = 100;
  }

  // Use the distanceTo method to get the distance between the rescuer and the target
  let distance = rescuerLatLng.distanceTo(L.latLng(target.lat, target.lng));

  // Check if the distance is within the threshold
  if (distance <= distanceThreshold) {
    console.log('Rescuer is nearby the target.');
    // Perform additional actions here

    // Return true since the rescuer is nearby the target
    return true;
  } else {
    console.log('Rescuer is not nearby the target.');
    // Perform other actions here

    // Return false since the rescuer is not nearby the target
    return false;
  }
}

function updateButtonVisibilityInPanel(title, isVisible) {
  console.log(`Update visibility for ${title}. Is visible: ${isVisible}`);

  // Find the task by title in the tasks panel
  var taskButton = document.querySelector(`.completeButton[data-task-title="${title}"]`);

  // Update button visibility
  if (taskButton) {
    console.log(`Button for ${title} found.`);
    taskButton.style.display = isVisible ? 'block' : 'none';
  }
}
function completeTask(task_id, task_type) {
    console.log(`Complete task called for ${task_type}:`, task_id);

    // Find the task by type and id in the trackedTasks array
    var task = trackedTasks.find(task => task[`${task_type}_id`] === task_id);

    console.log('TASK: ', task);

    // Check if the task is found and not already completed
    if (task && !task.handled) {
        // Logic to mark the task as completed
        task.handled = true;
        task.status = 'completed';

        // Make AJAX call to mark the task as complete on the server
        var xhr = new XMLHttpRequest();
        var url;

        if (task_type === 'request') {
            url = '../actions/completeRequest.php';
        } else if (task_type === 'offer') {
            url = '../actions/completeOffer.php';
        } else {
            console.error('Invalid task_type:', task_type);
            return;
        }

        xhr.open('POST', url, true);

        xhr.onload = function () {
            if (xhr.status >= 200 && xhr.status < 300) {
                console.log('Task marked as complete successfully');
                console.log('Response:', xhr.responseText);

                // Additional logic after marking the task as complete, if needed
                updateVehicleCargo(task, task_type);
                updateTasksPanel();
                removeMarkers(task[`${task_type}_id`], task_type);
                updatePopupContent();
                showTrackedTasks();
            } else {
                console.error('Error marking task as complete. Status:', xhr.status, 'Response:', xhr.responseText);
            }
        };

        xhr.onerror = function () {
            console.error('Network error during the request');
        };

        var formData = new FormData();
        formData.append(`${task_type}_id`, task[`${task_type}_id`]);

        xhr.send(formData);
    }
}

function updateVehicleCargo(task, task_type) {
    console.log('updateVehicleCargo function is called.');

    // Check if the task has a corresponding citizen request
    if (task.type === 'request' || task.type === 'offer') {

        // Calculate the updated quantity based on task type
        var updatedQuantity = (task_type === 'request') ? task.number_of_people : task.quantity;

        // Construct the data to be sent in the request body
        var requestData = {
            product_name: task.productName,
            updatedQuantity: updatedQuantity,
            type: task_type  // Add the 'type' property to the payload
        };
        
        console.log('Data sent to updateVehicleCargo:', requestData);

        // Make an AJAX request to update the RescuerInventory quantity
        var xhr = new XMLHttpRequest();

        // Adjust the URL based on the task type
        var url = (task_type === 'request') ? '../actions/updateVehicleCargoRequest.php' : '../actions/updateVehicleCargoOffer.php';

        xhr.open('POST', url, true); // Use the dynamic URL
        xhr.setRequestHeader('Content-Type', 'application/json');

        // Convert the data to JSON format
        var jsonData = JSON.stringify(requestData);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                if (xhr.status === 200) {
                    // Successfully updated RescuerInventory
                    console.log(`RescuerInventory updated for completed task: ${task.title}`);
                    console.log('Response from updateVehicleCargo.php:', xhr.responseText);
                    // Hide the "Complete" and "Cancel" buttons
                    var completeButton = document.querySelector(`.completeButton[data-task-title="${task.title}"]`);
                    var cancelButton = document.querySelector(`.cancelButton[data-task-title="${task.title}"]`);

                    // Hide the buttons if found
                    if (completeButton) {
                        completeButton.style.display = 'none';
                    }

                    if (cancelButton) {
                        cancelButton.style.display = 'none';
                    }

                    // Update the tasks panel
                    updateTasksPanel();
                } else {
                    // Handle error
                    console.error('Error updating RescuerInventory:', xhr.statusText);
                }
            }
        };

        // Send the AJAX request with the JSON data
        xhr.send(jsonData);
    }
}


function removeMarkers(id, type) {
    console.log(`You called removeMarkers for ${type} id: ${id}`);

    // Iterate through the map layers and log information for debugging
    map.eachLayer(function (layer) {
        if (layer instanceof L.Marker) {
            // Check if the layer has a custom property 'request_id' or 'offer_id'
            if ((type === 'request' && layer.request_id === id) || (type === 'offer' && layer.offer_id === id)) {
                // Remove the marker from the cluster group
                markersGroup.unbindPopup();
                markersGroup.removeLayer(layer);

                // Remove the marker from the map
                layer.remove();

                // Log information for debugging
                console.log(`Removed marker for ${type} id: ${id}`);
            }
        }
    });
}

function cancelTask(id, idType, title) {
  console.log('CancelTask called');
  // Find the task by title in the trackedTasks array
  var taskIndex = trackedTasks.findIndex(task => task.title === title && task[`${idType}_id`] === id);

  // Check if the task is found
  if (taskIndex !== -1) {
    trackedTasks.splice(taskIndex, 1);

    // Logic to handle task cancellation
    var xhr = new XMLHttpRequest();

    xhr.open('POST', `../actions/cancel${idType.charAt(0).toUpperCase() + idType.slice(1)}.php`, true);

    xhr.onload = function () {
      if (xhr.status >= 200 && xhr.status < 300) {
        console.log(`${idType.charAt(0).toUpperCase() + idType.slice(1)} canceled successfully`);
        console.log('Response:', xhr.responseText);

        // Find the marker using the id
        var marker = findMarkerById(id, idType);
        if (marker) {
          // Remove the previous popup
          console.log('Marker found!');

          marker.unbindPopup();
          map.closePopup(); 
          // Update the marker icon with the provided configuration
          marker.setIcon(L.icon({
            iconUrl: `../styles/${idType === 'request' ? 'location-pin' : 'red-offer'}.png`,
            iconSize: [32, 32],
            iconAnchor: [16, 32],
            popupAnchor: [0, -32]
          }));

          // Fetch updated status using AJAX
          updateStatus(id, idType, marker);
        }
      } else {
        console.error(`Error canceling ${idType}. Status:`, xhr.status, 'Response:', xhr.responseText);
      }
      // Update the tasks panel
      updateTasksPanel();
      showTrackedTasks();
    };

    xhr.onerror = function () {
      console.error('Network error during the request');
    };

    // Create a FormData object and append the id
    var formData = new FormData();
    formData.append(`${idType}_id`, id);

    xhr.send(formData);
  }
}


function updateStatus(id, idType, marker) {
  var url;

  if (idType === 'request') {
    url = `../actions/get_requests_status.php?request_id=${id}`;
  } else if (idType === 'offer') {
    url = `../actions/get_offers_status.php?offer_id=${id}`;
  } else {
    console.error('Invalid idType:', idType);
    return;
  }

  // Make an AJAX request to get the updated status
  var statusXhr = new XMLHttpRequest();
  statusXhr.open('GET', url, true);

  statusXhr.onload = function () {
    if (statusXhr.status >= 200 && statusXhr.status < 300) {
      console.log('Status updated successfully');
      console.log('Updated Status:', statusXhr.responseText);

      // Parse the response and create a new marker
      var updatedStatus = JSON.parse(statusXhr.responseText);
      createMarker(updatedStatus[0], idType); // Pass the idType to createMarker

      // Remove the canceled task from trackedTasks
      trackedTasks = trackedTasks.filter(t => t[idType + '_id'] !== id || t.status !== 'canceled');

      // Update the tasks panel
      updateTasksPanel();
      showTrackedTasks();
    } else {
      console.error('Error getting updated status. Status:', statusXhr.status, 'Response:', statusXhr.responseText);
    }
  };

  statusXhr.onerror = function () {
    console.error('Network error during the status update request');
  };

  statusXhr.send();
}


  function createMarker(data, type) {
  var newMarker = findMarkerById(data[type + '_id'], type);

  // Define the default icon URL
  var iconUrl = '../styles/location-pin.png';

  // Check if the type is 'offer' and update the icon URL accordingly
  if (type === 'offer') {
    iconUrl = '../styles/red-offer.png';
  }

  // Update the marker icon with the provided configuration
  newMarker.setIcon(L.icon({
    iconUrl: iconUrl,
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32]
  }));

  // Add a popup with the updated content
  //newMarker.bindPopup(generatePopupContent(data, type));

  // Add the new marker to the map
  newMarker.addTo(map); // Replace 'yourMap' with the actual variable representing your Leaflet map
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
        <b>Όχημα</b>: ${data.status === 'in progress' ? data.VehicleName : 'Δεν έχει ανατεθεί'}<br>
        ${data.status === 'new' ? `<button onclick="handleTrack${type.charAt(0).toUpperCase() + type.slice(1)}(this, '${data[type + '_id']}', '${data.citizen_name}', '${data.citizen_phone}', '${data.date_submitted}','${data.date_accepted}', '${data.product_name}', ${type === 'offer' ? data.quantity : data.number_of_people}, ${data.citizen_latitude}, ${data.citizen_longitude},'${data.status}', '${data.vehicleName}')">Track ${type.charAt(0).toUpperCase() + type.slice(1)}</button>` : ''}
      </li><hr>`;

  popupContent += '</ul>';
  return popupContent;
}
function findMarkerById(id, idType) {
  console.log('You called findMarkerById with id:', id, 'and idType:', idType);
  var layers = markersGroup.getLayers();

  for (var i = 0; i < layers.length; i++) {
    var layer = layers[i];

    // Check if the layer's id matches the provided id and idType
    if (layer[idType + '_id'] === id) {
      console.log('Found matching layer:', layer);
      return layer;
    }
  }

  // Return null if no matching marker is found
  console.log('No matching layer found');
  return null;
}

var vehicleMarkers = [];


// Function to update polylines with the new rescuer location
function updatePolylines(newRescuerLocation) {
     // Check if the filter checkbox is checked before updating polylines
  var straightLinesFilter = document.getElementById('straightLinesFilter');
  var showStraightLines = straightLinesFilter.checked;

  if (showStraightLines) {
  // Clear existing polylines
  clearTrackedTasks();

  // Draw polylines for all tracked tasks using PolylineDecorator
  trackedTasks.forEach(function(task) {
    if (!task.handled) {
    var polyline = L.polyline([newRescuerLocation, { lat: task.lat, lng: task.lng }], {
      color: 'blue',
      weight: 5,
      opacity: 0.7,
      dashArray: [10, 5],
      lineCap: 'round',
      lineJoin: 'round'
    }).addTo(map);

    // var arrow = L.polylineDecorator(polyline, {
    //   patterns: [
    //     { offset: 25, repeat: 50, symbol: L.Symbol.arrowHead({ pixelSize: 15, pathOptions: { fillOpacity: 1, weight: 0 } }) }
    //   ]
    // }).addTo(map);
  }
  });
}
}
    // Call the initMap function when the page loads
    document.addEventListener('DOMContentLoaded', function() {
      initMap();
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
