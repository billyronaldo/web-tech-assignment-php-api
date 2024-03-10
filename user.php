<?php
include 'dbconnection.php';

header("Content-Type: application/json");

// Check the HTTP request method
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
                echo json_encode(array("success" => "User registered!"));
            } else {
                echo json_encode(array("error" => "Error: " . $sql . "<br>" . $db->error));
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
                    // Generate token with expiration
                    $tokenData = generateToken($row['id']);
                    $token = $tokenData['token'];
                    $expireTime = date('Y-m-d H:i:s', $tokenData['expires_at']);

                    // Store token and expiration time in the database
                    $updateTokenSql = "UPDATE user SET token='$token', token_expires_at='$expireTime' WHERE id={$row['id']}";
                    $db->query($updateTokenSql);

                    $userDetails = array(
                        "id" => $row['id'],
                        "email" => $row['email'],
                        "username" => $row['username'],
                        "shipping_address" => $row['shipping_address'],
                        "token" => $token,
                        "expires_at" => $expireTime
                    );
                    echo json_encode(array("success" => "Login successful.", "user" => $userDetails));
                } else {
                    // Incorrect password
                    echo json_encode(array("error" => "Wrong password!"));
                }
            } else {
                // User not found
                echo json_encode(array("error" => "Wrong email!"));
            }
        }
        break;

    case 'GET':
        // Endpoint to get user profile
        if (isset($_GET['token'])) {
            $token = $_GET['token'];

            if (isValidToken($token, $db)) {
                $userId = $_GET['id']; 
                $sql = "SELECT * FROM user WHERE id='$userId'";
                $result = $db->query($sql);

                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    $userDetails = array(
                        "id" => $row['id'],
                        "email" => $row['email'],
                        "username" => $row['username'],
                        "shipping_address" => $row['shipping_address']
                    );
                    echo json_encode($userDetails);
                } else {
                    echo json_encode(array("error" => "User not found."));
                }
            } else {
                echo json_encode(array("error" => "Invalid or expired token."));
            }
        } else {
            echo json_encode(array("error" => "Token not provided."));
        }
        break;

    default:
        // Invalid method
        echo json_encode(array("error" => "Invalid request method."));
}

$db->close();

// Validate Token Function
function isValidToken($token, $db) {
    $currentTime = time();
    $sql = "SELECT * FROM user WHERE token='$token' AND token_expires_at > $currentTime";
    $result = $db->query($sql);
    return $result->num_rows > 0;
}

// Generate Token Function
function generateToken($userId) {
    $expireTime = time() + (24 * 60 * 60); // 24 hours expiration
    $token = md5(uniqid($userId, true));
    return array("token" => $token, "expires_at" => $expireTime);
}
?>
