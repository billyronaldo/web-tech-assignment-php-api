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

        // GET all comments for a product
        if (isset($_GET['product_id'])) {
            $product_id = $_GET['product_id'];
            $sql = "SELECT * FROM comments WHERE product_id = $product_id";
            $result = $db->query($sql);

            if ($result === false) {
                // error failed query
                $error = $db->error;
                echo json_encode(array("error" => $error));
            } else {
                $comments = array();
                while ($row = $result->fetch_assoc()) {
                    // Convert BLOB image data to base64 for JSON encoding
                    $row['image'] = base64_encode($row['image']);
                    $comments[] = $row;
                }
                echo json_encode($comments);
            }
        } else {
            echo json_encode(array("error" => "Missing product_id parameter for GET request"));
        }
        break;

    case 'POST':
        // Verify token for POST request
        $token = getBearerToken();

        if (!$token || !isValidToken($token, $db)) {
            echo json_encode(array("error" => "Invalid or expired token."));
            break;
        }

        // POST add new comment
        $product_id = $_POST['product_id'];
        $user_id = $_POST['user_id'];
        $rating = $_POST['rating'];
        $text = $_POST['text'];

        // Check if image file is uploaded (optional)
        if (isset($_FILES['image'])) {
            // Read the image file as binary data
            $image = addslashes(file_get_contents($_FILES['image']['tmp_name']));
        } else {
            $image = null;
        }

        // Insert the comment into the database
        $sql = "INSERT INTO comments (product_id, user_id, rating, text, image) 
                VALUES ('$product_id', '$user_id', '$rating', '$text', '$image')";

        if ($db->query($sql) === TRUE) {
            echo json_encode(array("success" => "New comment added"));
        } else {
            echo json_encode(array("error" => "Error adding comment: " . $db->error));
        }
        break;

    default:
        // Invalid method
        echo json_encode(array("error" => "Invalid request method."));
}

$db->close();
?>
