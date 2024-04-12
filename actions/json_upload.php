<?php
include('../test_connection.php');

if (isset($_POST['addCategory'])) {
    
    $category_name = $_POST['categoryName'];

    $sql = "INSERT INTO Categories (category_name) VALUES ('$category_name')";

    if ($conn->query($sql) === TRUE) {
        echo "Η κατηγορία προστέθηκε με επιτυχία!";
        header("Location: ../admin/apothiki.php");
    } else {
        echo "Σφάλμα κατά την προσθήκη κατηγορίας: " . $conn->error;
    }


    
}

if (isset($_POST['addProduct'])) {
    $product_name = $_POST['productName'];
    $quantity = $_POST['quantity'];
    $category_id = $_POST['categoryId'];

    $sql = "INSERT INTO Products (name, quantity, category_id) VALUES ('$product_name', $quantity, $category_id)";

    if ($conn->query($sql) === TRUE) {
        echo "Το προϊόν προστέθηκε με επιτυχία!";
        header("Location: ../admin/apothiki.php");
    } else {
        echo "Σφάλμα κατά την προσθήκη προϊόντος: " . $conn->error;
    }

}
if (isset($_POST['loadFromJson'])) {
    if ($_FILES['jsonFile']['error'] == 0) {
        $jsonFile = $_FILES['jsonFile']['tmp_name'];
        
        // Διαβάζουμε το περιεχόμενο του JSON αρχείου
        $jsonContent = file_get_contents($jsonFile);

        // Μετατρέπουμε το JSON σε πίνακα PHP
        $data = json_decode($jsonContent, true);

        // Εισάγουμε τις κατηγορίες στον πίνακα Categories
        foreach ($data['categories'] as $category) {
            $category_id = isset($category['id']) ? $category['id'] : null;
            $category_name = $category['category_name'];

            // Ελέγχουμε αν η κατηγορία υπάρχει ήδη στη βάση δεδομένων
            $checkCategoryQuery = "SELECT * FROM Categories WHERE category_name = '$category_name'";
            $checkCategoryResult = $conn->query($checkCategoryQuery);

            if ($checkCategoryResult->num_rows == 0) {
                // Η κατηγορία δεν υπάρχει, οπότε την προσθέτουμε
                $insertCategoryQuery = "INSERT INTO Categories (category_id, category_name) VALUES ('$category_id', '$category_name')";
                $conn->query($insertCategoryQuery);
            }
        }

        // Εισάγουμε τα προϊόντα στον πίνακα Products
        foreach ($data['items'] as $item) {
            $product_id = intval($item['id']);
            $product_name = $item['name'];
            $category_id = intval($item['category']);
            $quantity = isset($item['quantity']) ? intval($item['quantity']) : 0;

            // Ελέγχουμε αν το προϊόν υπάρχει ήδη στη βάση δεδομένων
            $checkProductQuery = "SELECT * FROM Products WHERE name = '$product_name' AND category_id = $category_id";
            $checkProductResult = $conn->query($checkProductQuery);

            if ($checkProductResult->num_rows == 0) {
                // Το προϊόν δεν υπάρχει, οπότε το προσθέτουμε
                $insertProductQuery = "INSERT INTO Products (product_id, name, category_id, quantity) VALUES ($product_id, '$product_name', $category_id, $quantity)";
                $conn->query($insertProductQuery);
            }
        }

        echo "Τα δεδομένα φορτώθηκαν επιτυχώς στη βάση.";
        header("Location: ../admin/apothiki.php");
    } else {
        echo "Σφάλμα κατά τη μεταφόρτωση του αρχείου JSON.";
    }
}

// Κλείσιμο σύνδεσης με τη βάση δεδομένων
$conn->close();
?>

