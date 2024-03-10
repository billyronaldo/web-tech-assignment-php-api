<?php
include 'dbconnection.php';
include 'token_functions.php';

header("Content-Type: application/json");

// Check the HTTP request method
$method = $_SERVER['REQUEST_METHOD'];

// Handle the request
switch ($method) {
    case 'GET':
        // Verify token for GET request
        $token = getBearerToken();

        if (!$token || !isValidToken($token, $db)) {
            echo json_encode(array("error" => "Invalid or expired token."));
            break;
        }

        // GET all products
        $sql = "SELECT * FROM product";
        $result = $db->query($sql);

        if ($result === false) {
            // error failed query
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
        // Verify token for POST request
        $token = getBearerToken();

        if (!$token || !isValidToken($token, $db)) {
            echo json_encode(array("error" => "Invalid or expired token."));
            break;
        }

        // POST add new product
        $description = $_POST['description'];
        $pricing = $_POST['pricing'];
        $shipping_cost = $_POST['shipping_cost'];

        // Check if image file is uploaded
        if (isset($_FILES['image'])) {
            // Read the image file as binary data
            $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));

            // Insert the image as a BLOB
            $sql = "INSERT INTO product ( description, image, pricing, shipping_cost) VALUES ( '$description', '$image', '$pricing', '$shipping_cost')";

            if ($db->query($sql) === TRUE) {
                echo json_encode(array("success" => "New product added"));
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
