<?php

$hostname = "localhost";
$username = "root";
$password = "";
$database = "rasa_db";

$db = mysqli_connect($hostname, $username, $password, $database);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

?>