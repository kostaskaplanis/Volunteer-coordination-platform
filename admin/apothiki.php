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

<style>
    * {
        font-family: "Lato", sans-serif;
    }
    
  .filterDiv {
    float: left;
    background-color: #2196F3;
    color: #ffffff;
    width: 200px;
    line-height: 100px;
    text-align: center;
    margin: 2px;
    display: none;
  }

  .productRow {
    display: none;
  }

  .show {
    display: table-row;
  }

  .container {
    margin-top: 20px;
    overflow: hidden;
  }

  .filters-wrapper {
    margin-bottom: 20px;
  }

  .filters-wrapper h4 {
    margin-bottom: 5px;
  }

  /* Style the buttons */
  .filter-btn {
    border: none;
    border-radius: 15px;
    outline: none;
    padding: 5px 10px;
    background-color: #2196F3;
    color: #fff;
    cursor: pointer;
    margin-right: 5px;
  }

  .filter-btn:hover {
    background-color: #005397;
  }

  .filter-btn.active {
    background-color: #005397;
    color: white;
  }

    .card {
        /* border: 1px solid #333; */
        width: 400px;
        margin: 40px auto;
        padding: 40px;
        border-radius: 10px;
        box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
    }

    .big-card {
        width: auto;
        margin-left: 40px;
        margin-right: 40px;
    }
    
    @media (max-width: 400px) {
        .card {
            width: 300px;
        }
    }

    .card > h2 {
       margin-top: 0px;
       text-align: center;
    }

    .input-group {
        margin: 10px 0;
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    
    .input-group > label {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .input-group > input, .input-group > select {
        padding: 10px;
        margin: 5px 0;
        border: 1px solid #555;
        border-radius: 4px;
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
    }


  .main-title {
    text-align: center; 
    font-weight: bold;
  }

  table > td, table > th {
    border: "1px solid #ddd";
  }

  table > th {
    padding-top: 12px;
    padding-bottom: 12px;
  }

    .grid-wrapper {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 10px;
        grid-auto-rows: minmax(100px, auto);
    }   

    .small-card {
        box-sizing: border-box;
    }

    @media (min-width: 768px) { /* Tablet view */
        .grid-wrapper {
            grid-template-columns: repeat(2, 1fr); /* 2 columns layout */
        }
    }

    @media (min-width: 1024px) { /* Desktop view */
        .grid-wrapper {
            grid-template-columns: repeat(3, 1fr); /* 4 columns layout */
        }
    }

    .grid-wrapper-2 {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 10px;
        grid-auto-rows: minmax(100px, auto);
    }   

    @media (min-width: 1100px) { /* Tablet view */
        .grid-wrapper-2 {
            grid-template-columns: repeat(2, 1fr); /* 2 columns layout */
        }
    }
  
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px; margin-bottom: 20px;
        max-width: 1200px;
    }

    th, td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
    }

    th {
            background-color: #f2f2f2;
    }
</style>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Διαχείριση Αποθήκης</title>
    <link rel="icon" type="image/x-icon" href="../styles/civil_protection.png">

    <!-- Προσθήκη JavaScript -->
    <script>
        function showForm(formId) {
            var jsonFileForm = document.getElementById('jsonFileForm');
            var jsonUrlForm = document.getElementById('jsonUrlForm');

            if (formId === 'jsonFileForm') {
                jsonFileForm.style.display = 'block';
                jsonUrlForm.style.display = 'none';
            } else {
                jsonFileForm.style.display = 'none';
                jsonUrlForm.style.display = 'block';
            }
        }

        function toggleFormVisibility() {
            var fileRadio = document.getElementById('fileRadio');
            var urlRadio = document.getElementById('urlRadio');

            if (fileRadio.checked) {
                showForm('jsonFileForm');
            } else if (urlRadio.checked) {
                showForm('jsonUrlForm');
            }
        }
        // AJAX function to load categories and products dynamically
        // admin_home admin_apothiki oti links exeis sto navbar
        function loadCategoriesAndProducts() {
            var xmlhttp = new XMLHttpRequest();

            xmlhttp.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("categoriesAndProducts").innerHTML = this.responseText;
                }
            };

             xmlhttp.open("GET", "../actions/json_upload.php", true);
            xmlhttp.send();
        }
        
        function deleteProduct(productId) {
            var confirmDelete = confirm("Are you sure you want to delete this product?");
            
            if (confirmDelete) {
                var xmlhttp = new XMLHttpRequest();

                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        // Remove the row from the table
                        var row = document.getElementById("quantity_" + productId).parentNode.parentNode;
                        row.parentNode.removeChild(row);
                    }
                };

                xmlhttp.open("GET", "../actions/delete_products.php?product_id=" + productId, true);
                xmlhttp.send();
            }
        }



        function updateQuantity(productId) {
            var newQuantity = prompt("Enter the new quantity:");

            if (newQuantity !== null) {
                var xmlhttp = new XMLHttpRequest();

                xmlhttp.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        // Update the displayed quantity
                        document.getElementById("quantity_" + productId).innerHTML = newQuantity;
                    }
                };

                xmlhttp.open("GET", "../actions/quantity.php?product_id=" + productId + "&quantity=" + newQuantity, true);
                xmlhttp.send();
            }
        }

        function get() {
            var table = document.getElementById("product_table");
            table.innerHTML = "";

            var xmlhttp = new XMLHttpRequest();

            xmlhttp.open("GET", "../actions/fetch_products.php", true);
            xmlhttp.send();

            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var data = JSON.parse(this.responseText);
                    var header = table.createTHead();
                    var row = header.insertRow(0);
                    var cell1 = row.insertCell(0);
                    var cell2 = row.insertCell(1);
                    var cell3 = row.insertCell(2);
                    var cell4 = row.insertCell(3);
                    var cell5 = row.insertCell(4);

                    cell1.innerHTML = "Κατηγορία";
                    cell2.innerHTML = "Προϊόν";
                    cell3.innerHTML = "Ποσότητα";
                    cell4.innerHTML = "Διαγραφή";
                    cell5.innerHTML = "Ενημέρωση Ποσότητας";

                    var tbody = table.createTBody();

                    for (var i = 0; i < data.length; i++) {
                        var row = tbody.insertRow(-1);
                        row.className = "productRow " + data[i]['category_name'] + " show";

                        var cell1 = row.insertCell(0);
                        var cell2 = row.insertCell(1);
                        var cell3 = row.insertCell(2);
                        var cell4 = row.insertCell(3);
                        var cell5 = row.insertCell(4);

                        cell1.innerHTML = data[i]['category_name'];
                        cell2.innerHTML = data[i]['product_name'];
                        cell3.innerHTML = "<span id='quantity_" + data[i]['product_id'] + "'>" + data[i]['quantity'] + "</span>";
                        cell4.innerHTML = "<button onclick='deleteProduct(" + data[i]['product_id'] + ")'>Διαγραφή</button>";
                        cell5.innerHTML = "<button onclick='updateQuantity(" + data[i]['product_id'] + ")'>Ενημέρωση</button>";
                    }
                }
            };

             // Categories Table
             var categoryTable = document.getElementById("category_table");
            categoryTable.innerHTML = "";

            var categoryXmlhttp = new XMLHttpRequest();

            categoryXmlhttp.open("GET", "../actions/fetch_categories.php", true);
            categoryXmlhttp.send();

            categoryXmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    var categoryData = JSON.parse(this.responseText);
                    var categoryHeader = categoryTable.createTHead();
                    categoryHeader.style.backgroundColor = '#555';
                
                    var categoryRow = categoryHeader.insertRow(0);
                    var categoryCell1 = categoryRow.insertCell(0);

                    categoryCell1.innerHTML = "Κατηγορία";

                    var categoryTbody = categoryTable.createTBody();

                    for (var i = 0; i < categoryData.length; i++) {
                        var categoryRow = categoryTbody.insertRow(-1);
                        var x = categoryRow.getElementsByTagName('tr');
                        x.style.border = "1px solid #ddd";
                        var categoryCell1 = categoryRow.insertCell(0);
                        
                        categoryCell1.innerHTML = categoryData[i]['category_name'];
                        categoryRow.style.border = "1px solid #ddd";
                        categoryRow.style.padding = "8px";
                        categoryCell1.style.border = "1px solid #ddd";
                        categoryCell1.style.padding = "8px";
                    }
                }
            };
        }
    function filterCategories() {
        var filterCategory = document.getElementById('filterCategory').value;
        console.log('filter category', filterCategory);
        var tableRows = document.getElementById('category_table').getElementsByTagName('tr');

        for (var i = 1; i < tableRows.length; i++) {
            var rowCategory = tableRows[i].getElementsByTagName('td')[0].innerHTML;

            if (filterCategory === '' || rowCategory === filterCategory) {
                tableRows[i].style.display = '';
            } else {
                tableRows[i].style.display = 'none';
            }
        }
    }
    
        // Καλεί τη συνάρτηση get όταν η σελίδα φορτώνεται
        window.onload = function() {
            loadVehicleAndProducts();
            get();
        };

        function loadVehicleAndProducts() {
    var xmlhttp = new XMLHttpRequest();

    xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            var data = JSON.parse(this.responseText);
            var table = document.getElementById("vehicleAndProductsTable");
            table.innerHTML = "";

            var header = table.createTHead();
            var row = header.insertRow(0);
            var cell1 = row.insertCell(0);
            var cell2 = row.insertCell(1);
            var cell3 = row.insertCell(2);

            cell1.innerHTML = "Όνομα Οχήματος";
            cell2.innerHTML = "Προϊόν";
            cell3.innerHTML = "Ποσότητα";

            var groupedData = groupByVehicleName(data);

            for (var vehicleName in groupedData) {
                if (groupedData.hasOwnProperty(vehicleName)) {
                    var products = groupedData[vehicleName];
                    var isFirstRow = true;

                    for (var i = 0; i < products.length; i++) {
                        var productName = products[i]['product_name'];
                        var quantity = products[i]['quantity'];

                        var row = table.insertRow(-1);
                        var cell1 = row.insertCell(0);
                        var cell2 = row.insertCell(1);
                        var cell3 = row.insertCell(2);

                        // Display the vehicle name only in the first row of each group
                        cell1.innerHTML = isFirstRow ? vehicleName : "";
                        isFirstRow = false;

                        cell2.innerHTML = productName;
                        cell3.innerHTML = quantity;
                    }
                }
            }
        }
    };

    xmlhttp.open("GET", "../actions/getrescuerinventory.php", true);
    xmlhttp.send();
}

function groupByVehicleName(data) {
    var groupedData = {};

    for (var i = 0; i < data.length; i++) {
        var vehicleName = data[i]['VehicleName'];

        if (!groupedData.hasOwnProperty(vehicleName)) {
            groupedData[vehicleName] = [];
        }

        groupedData[vehicleName].push(data[i]);
    }

    return groupedData;
}
async function fetchData(url) {
  try {
    const response = await fetch(url);
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching data:', error);
    return [];
  }
}

async function loadCategoriesFilterButtons() {
  const categories = await fetchData('../actions/fetch_categories.php');
  const container = document.getElementById("filterButtonsList");

  // Add "Show all" button
  const showAllButton = document.createElement("button");
  showAllButton.className = "filter-btn active";
  showAllButton.textContent = "Όλες οι Κατηγορίες";
  showAllButton.addEventListener("click", function() {
    const buttons = document.querySelectorAll('.filter-btn');
    buttons.forEach(button => {
        button.classList.remove('active');
    });
    this.classList.add('active'); 
    filterSelection('all');
  });
  container.appendChild(showAllButton);

  // Add buttons for each category
  categories.forEach(category => {
    const categoryButton = document.createElement("button");
    categoryButton.className = "filter-btn";
    categoryButton.textContent = category.category_name; // Assuming category_name is the correct property
    categoryButton.addEventListener("click", function() {
        const buttons = document.querySelectorAll('.filter-btn');
        buttons.forEach(button => {
            button.classList.remove('active');
        });
        this.classList.add('active'); 
        filterSelection(category.category_name);
    });
    container.appendChild(categoryButton);
  });
}

async function loadProducts() {
  const baseData = await fetchData('../actions/fetch_products.php');
  const rescuerInventoryData = await fetchData('../actions/getrescuerinventory.php');
  const combinedData = baseData.concat(rescuerInventoryData);
  displayProducts(combinedData);
}

async function filterSelection(c) {
  var x, i;
  x = document.getElementsByClassName("productRow");

  // Load all products before filtering
  await loadProducts();

  for (i = 0; i < x.length; i++) {
    x[i].classList.remove("show");
    if (c === "all" || x[i].classList.contains(c)) {
      console.log(`Product ${i} belongs to category ${c}`);
      x[i].classList.add("show");
    }
  }
}


function addClass(element, name) {
  var arr = element.className.split(" ");
  if (arr.indexOf(name) == -1) {
    element.className += " " + name;
  }
}

function removeClass(element, name) {
  var arr = element.className.split(" ");
  while (arr.indexOf(name) > -1) {
    arr.splice(arr.indexOf(name), 1);
  }
  element.className = arr.join(" ");
}

function displayProducts(data) {
  var container = document.getElementById("productContainer");
  container.innerHTML = '';

  data.forEach(function (item) {
    if(item.category_name){
        var div = document.createElement("div");
        var categoryClassName = item.category_name.replace(/\s+/g, '-'); // Convert spaces to hyphens for class names
        div.className = "filterDiv " + categoryClassName + " show";
        div.innerHTML = item.product_name + " - Quantity: " + item.quantity;
        container.appendChild(div);
    }
    });
}


loadProducts(); // Display all products by default    
loadCategoriesFilterButtons(); // Load categories and create Filter buttons


    </script>

    <!-- Τέλος προσθήκης JavaScript -->
</head>
<body>
<?php include('../test_connection.php')?>
<?php include('../components/navbar.php')?>
    
    <h1 class="main-title">Διαχείριση Αποθήκης</h1>

    <div class="grid-wrapper">
            <div class="card small-card">
                <h2>Προσθήκη Κατηγορίας</h2>
                
                <form method="post" action="../actions/json_upload.php">
                    <div class="input-group">
                        <label for="categoryName">Όνομα Κατηγορίας:</label>
                        <input type="text" id="categoryName" name="categoryName" required />
                    </div>
                    <input class="submit-button" type="submit" name="addCategory" value="Προσθήκη Κατηγορίας" />
                </form>
            </div>
            
            <div class="card small-card">
            <h2>Προσθήκη Προϊόντος</h2>
            
            <form id="addProductForm" method="post" action="../actions/json_upload.php">
                <div class="input-group">
                    <label for="productName">Όνομα Προϊόντος:</label>
                    <input type="text" id="productName" name="productName" required>
                </div>
                <div class="input-group">
                    <label for="categoryId">Κατηγορία:</label>
                    <select id="categoryId" name="categoryId" required>
                        
                        <?php
                        // Επιλογή όλων των κατηγοριών από τη βάση δεδομένων
                        $sql = "SELECT * FROM Categories";
                        $result = $conn->query($sql);
                        
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="quantity">Ποσότητα:</label>
                    <input type="number" id="quantity" name="quantity" required>
                </div>
                <input class="submit-button" type="submit" name="addProduct" value="Προσθήκη Προϊόντος">
            </form>
        </div>
        
        <div class="card small-card">
            <h2>Φόρτωση Κατηγοριών και Προϊόντων</h2>
            
            <div>
                <input type="radio" id="fileRadio" name="source" value="file" checked onclick="toggleFormVisibility()">
                <label for="fileRadio">Φόρτωση από αρχείο JSON</label>
            </div>
            <div>
                <input type="radio" id="urlRadio" name="source" value="url" onclick="toggleFormVisibility()">
                <label for="urlRadio">Φόρτωση από URL</label>
            </div>
                
            <div style="margin-top: 20px;">
                <form id="jsonFileForm" method="post" action="../actions/json_upload.php" enctype="multipart/form-data">
                    <div class="input-group">
                        <label for="file">Επιλέξτε αρχείο:</label>
                        <input type="file" id="jsonFile" name="jsonFile" accept=".json" required>
                    </div>
                    <input class="submit-button" type="submit" name="loadFromJson" value="Φόρτωση από JSON" onclick="showForm('jsonFileForm')">
                </form>
                
                <form id="jsonUrlForm" method="post" action="../actions/url_insert.php" style="display:none;">
                    <div class="input-group">
                        <label for="jsonUrl">Εισάγετε το URL:</label>
                        <input type="text" id="jsonUrl" name="jsonUrl" placeholder="Εισάγετε το URL" required>
                    </div>
                    <input class="submit-button" type="submit" name="loadFromUrl" value="Φόρτωση από URL" onclick="showForm('jsonUrlForm')">
                </form>
            </div>
        </div>
    </div>
    
    <div class="grid-wrapper-2">
        <div class="card big-card">
            <h2>Λίστα Προϊόντων</h2>
            <table border="1" id="product_table">.
                <div class="filters-wrapper">
                    <h4>Φίλτρα ανα Κατηγορία:</h4>
                    <div id="filterButtonsList"></div>
                </div>
            </table>
        </div>
        
        <div class="card">
            <h2>Εμφάνιση Προϊόντων ανά Όχημα</h2>
            <table border="1" id="vehicleAndProductsTable"></table>
        </div>
    </div>

    <div style="display: none;" id="productContainer"></div>

</body>
</html>