<?php
$host = 'localhost';
$dbname = 'login';
$username = 'root';
$port = 8888;
$password = 'root';

// Create connection
$conn = mysqli_connect($host, $username, $password, $dbname);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());// check if the connection is successful
}
?> 