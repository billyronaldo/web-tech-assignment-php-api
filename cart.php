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

        // GET cart items for a user
        if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $sql = "SELECT c.id, c.product_id, p.description, p.image, p.pricing, c.quantity 
                    FROM cart c
                    JOIN product p ON c.product_id = p.id
                    WHERE c.user_id = $user_id";
            $result = $db->query($sql);

            if ($result->num_rows > 0) {
                $cart_items = array();
                while ($row = $result->fetch_assoc()) {
                    // Convert BLOB image data to base64 for JSON encoding
                    $row['image'] = base64_encode($row['image']);
                    $cart_items[] = $row;
                }
                echo json_encode($cart_items);
            } else {
                echo json_encode(array("message" => "Cart is empty"));
            }
        } else {
            echo json_encode(array("error" => "Invalid action or missing user_id parameter for GET request"));
        }
        break;

    case 'POST':
        // Verify token for POST request
        $token = getBearerToken();

        if (!$token || !isValidToken($token, $db)) {
            echo json_encode(array("error" => "Invalid or expired token."));
            break;
        }

        // Add product to cart
        if (isset($_POST['action']) && $_POST['action'] === 'add') {
            $user_id = $_POST['user_id'];
            $product_id = $_POST['product_id'];
            $quantity = $_POST['quantity'];

            // Check if the item already exists in the cart
            $checkQuery = "SELECT * FROM cart WHERE user_id = $user_id AND product_id = $product_id";
            $checkResult = $db->query($checkQuery);

            if ($checkResult->num_rows > 0) {
                // Item already exists (update quantity)
                $updateQuery = "UPDATE cart SET quantity = quantity + $quantity 
                                WHERE user_id = $user_id AND product_id = $product_id";
                if ($db->query($updateQuery) === TRUE) {
                    echo json_encode(array("success" => "Quantity updated in the cart"));
                } else {
                    echo json_encode(array("error" => "Error updating quantity: " . $db->error));
                }
            } else {
                // Item does not exist in the cart (insert new item)
                $insertQuery = "INSERT INTO cart (user_id, product_id, quantity) 
                                VALUES ($user_id, $product_id, $quantity)";
                if ($db->query($insertQuery) === TRUE) {
                    echo json_encode(array("success" => "Item added to cart"));
                } else {
                    echo json_encode(array("error" => "Error adding item to cart: " . $db->error));
                }
            }
        }
        // Remove product from cart
        elseif (isset($_POST['action']) && $_POST['action'] === 'remove') {
            $user_id = $_POST['user_id'];
            $id = $_POST['id'];

            $deleteQuery = "DELETE FROM cart WHERE user_id = $user_id AND id = $id";
            if ($db->query($deleteQuery) === TRUE) {
                echo json_encode(array("success" => "Product removed from cart"));
            } else {
                echo json_encode(array("error" => "Error removing product from cart: " . $db->error));
            }
        } else {
            echo json_encode(array("error" => "Invalid action for POST request"));
        }
        break;

    default:
        // Invalid method
        echo json_encode(array("error" => "Invalid request method."));
}

$db->close();
?>
