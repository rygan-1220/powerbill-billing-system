<?php
$serverName = "localhost";
$database = "ElectricityBilling";
$username = "web";
$password = "Web#123";

$connectionInfo = array(
    "Database" => $database,
    "UID" => $username,
    "PWD" => $password,
    "CharacterSet" => "UTF-8"
);

// Create connection 
$conn = @sqlsrv_connect($serverName, $connectionInfo);

echo "Connection successful! ✅<br>";

?>