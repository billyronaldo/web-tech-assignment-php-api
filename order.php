<?php
include 'dbconnection.php';

header("Content-Type: application/json");

// Check the HTTP request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // POST place a new order
        if (isset($_POST['user_id']) && isset($_POST['product_id']) && isset($_POST['quantity'])) {
            $user_id = $_POST['user_id'];
            $product_id = $_POST['product_id'];
            $quantity = $_POST['quantity'];
            $order_date = date('Y-m-d H:i:s'); // current backend time

            // Get product price
            $productQuery = "SELECT pricing FROM product WHERE id = $product_id";
            $productResult = $db->query($productQuery);

            if ($productResult->num_rows > 0) {
                $row = $productResult->fetch_assoc();
                $productPrice = $row['pricing'];

                // Calculate total amount
                $total_amount = $quantity * $productPrice;

                // Insert the order into the database
                $sql = "INSERT INTO `orders` (user_id, product_id, quantity, total_amount, order_date) 
                        VALUES ('$user_id', '$product_id', '$quantity', '$total_amount', '$order_date')";

                if ($db->query($sql) === TRUE) {
                    echo json_encode(array("success" => "Order successfull"));
                } else {
                    echo json_encode(array("error" => "Error order: " . $db->error));
                }
            } else {
                echo json_encode(array("error" => "Product not found"));
            }
        } else {
            echo json_encode(array("error" => "Missing parameters"));
        }
        break;

    case 'GET':
        // GET all orders for a user
        if (isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            $sql = "SELECT * FROM `orders` WHERE user_id = $user_id";
            $result = $db->query($sql);

            if ($result->num_rows > 0) {
                $orders = array();
                while ($row = $result->fetch_assoc()) {
                    $orders[] = $row;
                }
                echo json_encode($orders);
            } else {
                echo json_encode(array("message" => "No orders found for this user"));
            }
        } else {
            echo json_encode(array("error" => "Missing user_id parameter"));
        }
        break;

    default:
        // Invalid method
        echo json_encode(array("error" => "Invalid request method."));
}

$db->close();
?>
