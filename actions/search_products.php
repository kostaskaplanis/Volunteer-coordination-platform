<?php
include('../test_connection.php');

$term = $_GET['term'];
$category = $_GET['category'];

$sql = "SELECT product_id, name FROM Products WHERE category_id = $category AND name LIKE '%$term%'";
$result = $conn->query($sql);

$data = array();
if ($result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $data[] = array(
      'label' => $row['name'], // Ολόκληρο όνομα προϊόντος
      'value' => $row['name'] // Επιλογή του ονόματος ως τιμή
    );
  }
}

$conn->close();

echo json_encode($data);
?>