$(document).ready(function() {
  $('#submitRequestBtn').on('click', function(e) {
    e.preventDefault(); // Αποτρέπει την προεπιλεγμένη συμπεριφορά του κουμπιού υποβολής φόρμας

    // Συλλέγει τα δεδομένα από τη φόρμα
    var formData = {
      product: $('#product').val(),
      quantity: $('#quantity').val(),
      numberOfPeople: $('#numberOfPeople').val()
      // Πρόσθεσε άλλα πεδία αν χρειάζεσαι
    };

    // Αποστολή του αιτήματος με χρήση AJAX
    $.ajax({
      type: 'POST',
      url: 'handle-aithmata.php', // Το URL του backend αρχείου που διαχειρίζεται τα αιτήματα
      data: formData,
      dataType: 'html', // Αναμενόμενος τύπος δεδομένων που θα επιστραφεί
      encode: true
    })
    .done(function(response) {
      // Αν το αίτημα ολοκληρωθεί με επιτυχία
      $('#responseMessage').text(response); // Εμφανίζει το μήνυμα από τον server στο frontend
      // Κάποιες άλλες ενέργειες που θέλεις να πραγματοποιηθούν μετά την υποβολή του αιτήματος
    })
    .fail(function(error) {
      // Αν υπάρξει κάποιο σφάλμα
      $('#responseMessage').text('Υπήρξε κάποιο σφάλμα: ' + error); // Εμφανίζει το μήνυμα λάθους στο frontend
    });
  });
});