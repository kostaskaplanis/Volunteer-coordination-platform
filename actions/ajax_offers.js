// ajax_offers.js
$(document).ready(function() {
    // Function to fetch and display offers using Ajax
    function displayOffers(type) {
        // Make an Ajax request to the PHP script
        $.ajax({
            type: "GET",
            url: "get_offers.php", // Create this PHP script to fetch offers
            data: { type: type }, // Send the offer type (current or past)
            dataType: "json",
            success: function(response) {
                // Handle the JSON response and display offers in the respective container
                var containerId = (type === "current") ? "currentOffersContainer" : "pastOffersContainer";
                displayOffersInContainer(response, containerId);
            },
            error: function(error) {
                console.error("Error fetching offers:", error);
            }
        });
    }

    // Function to display offers in a specified container
    function displayOffersInContainer(offers, containerId) {
        var container = $("#" + containerId);
        container.empty(); // Clear the container

        if (offers.length > 0) {
            // Build HTML to display offers
            var html = "<table border='1'>" +
                "<tr><th>Προσφορά ID</th><th>Ανακοίνωση</th><th>Προϊόν</th><th>Ποσότητα</th><th>Κατάσταση</th><th>Ημερομηνία Υποβολής</th>";

            for (var i = 0; i < offers.length; i++) {
                var offer = offers[i];
                html += "<tr><td>" + offer.offer_id + "</td><td>" + offer.announcement_title + "</td><td>" + offer.product_name +
                    "</td><td>" + offer.quantity + "</td><td>" + offer.status + "</td><td>" + offer.date_submitted + "</td></tr>";
            }

            html += "</table>";
            container.html(html);
        } else {
            container.text("Δεν υπάρχουν προσφορές.");
        }
    }

    // Fetch and display current offers on page load
    displayOffers("current");

    // Fetch and display past offers when a button is clicked (you can add a button for this)
    $("#fetchPastOffersBtn").on("click", function() {
        displayOffers("past");
    });
});
