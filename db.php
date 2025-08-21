<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "warehouse_simulator";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
  die("Conexiunea a eșuat: " . $conn->connect_error);
}
?>
