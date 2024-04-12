$(function () {
    $("#autocompleteProduct").autocomplete({
      source: function (request, response) {
        $.ajax({
          url: "search_products.php",
          dataType: "json",
          data: {
            term: request.term.substring(0, 2),
            category: $("#category").val(),
          },
          success: function (data) {
            // Περιορίζουμε την εμφάνιση των προτάσεων μόνο μέσα στο autocomplete
            response(data);
          },
        });
      },
      minLength: 2,
      select: function (event, ui) {
        $("#autocompleteProduct").val(ui.item.label);
        return false;
      },
      focus: function (event, ui) {
        return false;
      },
    }).data("ui-autocomplete")._renderItem = function (ul, item) {
      // Εδώ δημιουργούμε τις προτάσεις που θα εμφανίζονται στο autocomplete
      return $("<li>").append($("<div>").text(item.label)).appendTo(ul);
    };
  
    // Δεν αλλάζει η λειτουργία του input event handler
    $("#autocompleteProduct").on("input", function () {
      var inputText = $(this).val();
      $.ajax({
        url: "search_products.php",
        dataType: "json",
        data: {
          term: inputText,
          category: $("#category").val(),
        },
        success: function (data) {
          var suggestions = $("#productSuggestions");
          // Αφαιρούμε αυτό το κομμάτι κώδικα που προσθέτει εκτός του autocomplete
          suggestions.empty();
        },
      });
    });
  });