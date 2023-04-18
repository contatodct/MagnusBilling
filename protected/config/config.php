<?php

$dbConfig = array(
    'hostname' => "localhost",
    'username' => "root",
    'password' => "Wn6FJbNvhs4uUk90",
    'database' => "mbilling"
);

$conn = new mysqli($dbConfig['hostname'], $dbConfig['username'], $dbConfig['password'], $dbConfig['database']) or die("Could not connect database");

if ($conn->connect_errno) {
    printf("Connect failed: %s\n", $db->connect_error);
    exit();
}

if (!$conn->set_charset("utf8mb4")) {
    printf("Error loading character set utf8mb4: %s\n", $mysqli->error);
    exit();
}


?>
