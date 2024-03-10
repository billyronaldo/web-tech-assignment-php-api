<?php

// Validate Bearer Token
function isValidToken($token, $db) {
    $sql = "SELECT id FROM user WHERE token = '$token' AND token_expires_at > NOW()";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        return true;
    }
    return false;
}

// Get Bearer Token from Authorization header
function getBearerToken() {
    $headers = apache_request_headers();
    if (!empty($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        $bearer = explode(' ', $authHeader);
        if (count($bearer) === 2 && $bearer[0] === 'Bearer') {
            return $bearer[1];
        }
    }
    return null;
}

?>
