<?php
    // Connect to the database
    $servername   = 'localhost'; 
    $username     = 'root';
    $password     = '';
    $dbname       = 'assignment';

    // create the connection
    $db = new mysqli($servername, $username, $password, $dbname);

    // check if connection has any issues
    if($db->connect_error){
        die('Error connecting to database!');
    }
