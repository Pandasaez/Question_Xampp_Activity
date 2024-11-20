<?php
$host = 'localhost';
$user = 'root'; 
$pass = '';
$dbname = 'questionnaire';
$port = '3307';


$conn = mysqli_connect($host, $user, $pass, $dbname, $port);

if (!$conn) {
    die("Connection Failed: " .mysqli_connect_error());
}
echo "Connected Successfully!";
?>