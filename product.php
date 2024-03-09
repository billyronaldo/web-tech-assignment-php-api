<?php
include 'dbconnection.php';

header("Content-Type: application/json");

// Check the HTTP request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Endpoint to retrieve all products
        $sql = "SELECT * FROM product";
        $result = $db->query($sql);

        if ($result === false) {
            // Error handling for failed query
            $error = $db->error;
            echo json_encode(array("error" => $error));
        } else {
            $products = array();
            while ($row = $result->fetch_assoc()) {
                // Convert BLOB image data to base64 for JSON encoding
                $row['image'] = base64_encode($row['image']);
                $products[] = $row;
            }
            echo json_encode($products);
        }
        break;

    case 'POST':
        // Endpoint to add a new product
        // Assuming form fields are submitted via POST
        $name = $_POST['name'];
        $description = $_POST['description'];

        // Check if image file is uploaded
        if (isset($_FILES['image'])) {
            // Read the image file as binary data
            $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));

            // Insert the image as a BLOB into the database
            $sql = "INSERT INTO product (name, description, image) VALUES ('$name', '$description', '$image')";

            if ($db->query($sql) === TRUE) {
                echo json_encode(array("success" => "New product added successfully."));
            } else {
                echo json_encode(array("error" => "Error: " . $sql . "<br>" . $db->error));
            }
        } else {
            // Image file not uploaded
            echo json_encode(array("error" => "Image file not uploaded."));
        }
        break;

    default:
        // Invalid method
        echo json_encode(array("error" => "Invalid request method."));
}

$db->close();
?>
