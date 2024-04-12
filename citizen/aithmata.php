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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Διαχείριση Αιτημάτων</title>
  <link rel="icon" type="image/x-icon" href="../styles/civil_protection.png">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
  <style>
        .content-container {
            display: flex;
            justify-content: center;
            align-items: center;
        }

         .card {
            /* border: 1px solid #333; */
            width: 600px;
            margin: 40px auto;
            padding: 40px;
            border-radius: 10px;
            box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
        }
        
        .small-card {
            box-sizing: border-box;
        }

        .title {
          text-align: center;
        }

        .submit-button {
        width: 100%;
        color: #fff;
        background-color: #333;
        font-weight: bold;
        padding-top: 10px;
        padding-bottom: 10px;
        font-size: 16px;
        border-radius: 5px;
    }

    .submit-button:hover {
        cursor: pointer;
        color: orange;
        margin-top: 20px;
    }
    </style>
</head>
<body>
<?php include('../test_connection.php');
include('../components/navbar.php');
?>
<h1 style="text-align: center;">Διαχείριση Αιτημάτων</h1>

<div class="content-container">

<div class="card small-card">
<!-- Φόρμα για υποβολή αιτήματος κατηγορίας - Dropdown -->
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">

<div>
  <div>
    <label for="category"> Επέλεξε κατηγορία:</label>
    <select name="category" id="category" onchange="this.form.submit()">
      <option value="">--Παρακαλώ επιλέξτε μία κατηγορία--</option>
      <!-- ... κώδικας για την επιλογή κατηγορίας ... -->
      <?php
      $servername = "localhost";
      $username = "kostas";
      $password = "kostas1234";
      $dbname = "FYSIKES_KATASTROFES";
      // Σύνδεση με τη βάση δεδομένων
      $conn = new mysqli($servername, $username, $password, $dbname);
      
      if ($conn->connect_error) {
        die("Αδυναμία σύνδεσης στη βάση δεδομένων: " . $conn->connect_error);
      }
      
      // Επιλογή κατηγοριών από τον πίνακα Categories
      $sql = "SELECT category_id, category_name FROM Categories";
      $result = $conn->query($sql);
      
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
          echo "<option value='" . $row["category_id"] . "'";
          if (isset($_POST['category']) && $_POST['category'] == $row["category_id"]) {
            echo " selected";
          }
          echo ">" . $row["category_name"] . "</option>";
        }
      } else {
        echo "<option value=''>Δεν υπάρχουν κατηγορίες</option>";
      }
      
      $conn->close();
      ?>
    </select>
  </div>
</form>
<!-- Radio buttons για την επιλογή του τρόπου -->
<div style="margin-top: 20px;">
  <input type="radio" name="method" id="dropdownMethod" value="dropdown" checked="true">
  <label for="dropdownMethod">EΠΕΛΕΞΕ ΤΟ ΠΡΟΙΟΝ ΑΠΟ ΛΙΣΤΑ</label>
</div>

<div>
  <input type="radio" name="method" id="autocompleteMethod" value="autocomplete">
  <label for="autocompleteMethod">ΓΡΑΨΕ ΤΟ ΠΡΟΙΟΝ ΠΟΥ ΖΗΤΑΣ</label>
</div>
<!-- Φόρμα για υποβολή αιτήματος προϊόντος - Autocomplete -->
<form action="../actions/handle-aithmata.php" method="post" style="margin-top: 10px;">
  <label for="product">Επιλογη προϊόντος:</label>
  <select name="product" id="product">
    <!-- ... κώδικας για την επιλογή προϊόντων με dropdown ... -->
    <?php
      if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["category"])) {
        $selectedCategory = $_POST["category"];

        // Σύνδεση με τη βάση δεδομένων
        $conn = new mysqli($servername, $username, $password, $dbname);

        if ($conn->connect_error) {
          die("Αδυναμία σύνδεσης στη βάση δεδομένων: " . $conn->connect_error);
        }

        // Επιλογή των προϊόντων από τον πίνακα Products με βάση την επιλεγμένη κατηγορία
        $sql = "SELECT product_id, name FROM Products WHERE category_id = $selectedCategory";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
            echo "<option value='" . $row["product_id"] . "'>" . $row["name"] . "</option>";
          }
        } else {
          echo "<option value=''>Δεν υπάρχουν προϊόντα σε αυτήν την κατηγορία</option>";
        }

        $conn->close();
      }
      ?>



  </select>

  <!-- Πεδίο εισαγωγής για επιλογή προϊόντος με Autocomplete -->
  <label for="autocompleteProduct">Επιλογή Προϊόντος (Autocomplete):</label>
  <input type="text" id="autocompleteProduct" name="autocompleteProduct" onfocus="this.value=''" autocomplete="off">
  <div id="productSuggestions"></div>

<div style="margin-bottom: 20px;">
  <label for="numberOfPeople">Πλήθος Ατόμων:</label>
  <input type="number" name="numberOfPeople" id="numberOfPeople" min="1">
</div>
  
  <button type="submit" class="submit-button">Υποβολή Αιτήματος</button>
</form>

</div>
</div>




<script>
  $(function () {
    $("#autocompleteProduct").autocomplete({
      source: function (request, response) {
        $.ajax({
          url: "../actions/search_products.php",
          dataType: "json",
          data: {
            term: request.term,
            category: $("#category").val(),
          },
          success: function (data) {
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
      return $("<li>").append($("<div>").text(item.label)).appendTo(ul);
    };

    $("#autocompleteProduct").on("input", function () {
      var inputText = $(this).val();
      $.ajax({
        url: "../actions/search_products.php",
        dataType: "json",
        data: {
          term: inputText,
          category: $("#category").val(),
        },
        success: function (data) {
          var suggestions = $("#productSuggestions");
          suggestions.empty();
          $.each(data, function (index, product) {
            suggestions.append("<div>" + product.label + "</div>");
          });
        },
      });
    });
  });
</script>
<script>
  const dropdownMethod = document.getElementById('dropdownMethod');
  const autocompleteMethod = document.getElementById('autocompleteMethod');
  const productLabel = document.querySelector('label[for="product"]');
  const autocompleteLabel = document.querySelector('label[for="autocompleteProduct"]');
  const productDropdown = document.getElementById('product');
  const autocompleteProduct = document.getElementById('autocompleteProduct');

  dropdownMethod.addEventListener('change', function () {
    if (dropdownMethod.checked) {
      productLabel.style.display = 'block';
      productDropdown.style.display = 'block';
      autocompleteLabel.style.display = 'none';
      autocompleteProduct.style.display = 'none';
    }
  });

  autocompleteMethod.addEventListener('change', function () {
    if (autocompleteMethod.checked) {
      productLabel.style.display = 'none';
      productDropdown.style.display = 'none';
      autocompleteLabel.style.display = 'block';
      autocompleteProduct.style.display = 'block';
    }
  });

  // Αρχική κρυφή των πεδίων επιλογής ανάλογα με την αρχική επιλογή
  if (dropdownMethod.checked) {
    productLabel.style.display = 'block';
    productDropdown.style.display = 'block';
    autocompleteLabel.style.display = 'none';
    autocompleteProduct.style.display = 'none';
  } else if (autocompleteMethod.checked) {
    productLabel.style.display = 'none';
    productDropdown.style.display = 'none';
    autocompleteLabel.style.display = 'block';
    autocompleteProduct.style.display = 'block';
  }
</script>

</body>
</html>
