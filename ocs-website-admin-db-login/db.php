<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "ocs_db";

$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

$conn->query("CREATE TABLE IF NOT EXISTS Customer (
  CID INT AUTO_INCREMENT PRIMARY KEY,
  FName VARCHAR(100),
  LName VARCHAR(100),
  Email VARCHAR(100),
  Password VARCHAR(255),
  Address TEXT,
  Phone VARCHAR(20),
  Status ENUM('Regular', 'Silver', 'Gold', 'Platinum')
)");

function check_login($email, $password) {
  global $conn;
  $res = $conn->query("SELECT * FROM Customer WHERE Email='$email' AND Password='$password'");
  return $res->num_rows === 1;
}

$conn->query("CREATE TABLE IF NOT EXISTS Admin (
  AID INT AUTO_INCREMENT PRIMARY KEY,
  Username VARCHAR(50) UNIQUE,
  Password VARCHAR(255)
)");
$conn->query("INSERT IGNORE INTO Admin (Username, Password) VALUES ('admin', 'admin123')");
?>