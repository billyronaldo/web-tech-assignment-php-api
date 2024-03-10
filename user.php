<?php
include 'dbconnection.php';

header("Content-Type: application/json");

// Check the request method
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'POST':
        // Endpoint for user registration
        if ($_POST['action'] == 'register') {

            $email = $_POST['email'];
            $password = $_POST['password'];
            $username = isset($_POST['username']) ? $_POST['username'] : '';
            $shipping_address = isset($_POST['shipping_address']) ? $_POST['shipping_address'] : '';

            // Validate and hash password
            $hashedPwd = password_hash($password, PASSWORD_DEFAULT);

            $sql = "INSERT INTO user (email, password, username, shipping_address) VALUES ('$email', '$hashedPwd', '$username',  '$shipping_address')";

            if ($db->query($sql) === TRUE) {
                echo json_encode(array("success" => "user registered!"));
            } else {
                echo json_encode(array("error" => "error: " . $sql . "<br>" . $db->error));
            }
        } elseif ($_POST['action'] == 'login') {
            // Login endpoint

            $email = $_POST['email'];
            $password = $_POST['password'];

            $sql = "SELECT * FROM user WHERE email='$email'";
            $result = $db->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    // Auth success
                    // Generate token
                    $token = generateToken($row['id']);
                    $userDetails = array(
                        "id" => $row['id'],
                        "email" => $row['email'],
                        "username" => $row['username'],
                        "purchase_history" => $row['purchase_history'],
                        "shipping_address" => $row['shipping_address'],
                        "token" => $token
                    );
                    echo json_encode(array("success" => "Login successful.", "user" => $userDetails));
                } else {
                    // Incorrect password
                    echo json_encode(array("error" => "Wrong password!"));
                }
            } else {
                // User not found (invalid email)
                echo json_encode(array("error" => "Wrong email!"));
            }
        }
        break;

    case 'GET':
        // Endpoint to get user profile

        if ($_GET['action'] == 'profile') {
            $userId = $_GET['id']; 
            $sql = "SELECT * FROM user WHERE id='$userId'";
            $result = $db->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $userDetails = array(
                    "id" => $row['id'],
                    "email" => $row['email'],
                    "username" => $row['username'],
                    "purchase_history" => $row['purchase_history'],
                    "shipping_address" => $row['shipping_address']
                );
                echo json_encode($userDetails);
            } else {
                echo json_encode(array("error" => "User not found."));
            }
        }
        break;

    default:
        // Invalid method
        echo json_encode(array("error" => "Invalid request method."));
}

$db->close();


function generateToken($userId) {
    return md5(uniqid($userId, true));
}
?>
