<?php
$env = parse_ini_file(".env");
$servername = $env['DB_HOST'];
$username   = $env['DB_USER'];
$password   = $env['DB_PASS'];
$dbname     = $env['DB_NAME'];

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    exit('Connection failed: ' . $conn->connect_error);
}