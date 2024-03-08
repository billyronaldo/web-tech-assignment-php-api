<?php
include 'dbconnection.php';

header("Content-Type: application/json");

// GET request to retrieve all products
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $sql = "SELECT * FROM product";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {
        $products = array();
        while($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
        echo json_encode($products);
    } else {
        echo json_encode([]);
    }
}

$db->close();
?>
