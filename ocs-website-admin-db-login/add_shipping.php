<?php
session_start();
include 'db.php';

if (!isset($_SESSION['cid'])) {
  header("Location: login.php");
  exit;
}
$cid = $_SESSION['cid'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $saname = $_POST['SAName'];
  $recipient = $_POST['RecepientName'];
  $street = $_POST['Street'];
  $snumber = $_POST['SNumber'];
  $city = $_POST['City'];
  $zip = $_POST['Zip'];
  $state = $_POST['State'];
  $country = $_POST['Country'];

  $conn->query("INSERT INTO SHIPPING_ADDRESS (CID, SAName, RecepientName, Street, SNumber, City, Zip, State, Country)
                VALUES ($cid, '$saname', '$recipient', '$street', '$snumber', '$city', '$zip', '$state', '$country')");

  echo "<div class='alert alert-success'>Shipping address added successfully.</div>";
}
?>
<!DOCTYPE html>
<html>
<head><title>Add Shipping Address</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="container mt-5">
<h3>Add Shipping Address</h3>
<form method="POST" class="row g-3 border p-4 rounded bg-light shadow-sm">
  <div class="col-md-4"><label class="form-label">SAName</label><input name="SAName" class="form-control" required></div>
  <div class="col-md-4"><label class="form-label">Recepient Name</label><input name="RecepientName" class="form-control" required></div>
  <div class="col-md-6"><label class="form-label">Street</label><input name="Street" class="form-control" required></div>
  <div class="col-md-2"><label class="form-label">Street Number</label><input name="SNumber" class="form-control" required></div>
  <div class="col-md-3"><label class="form-label">City</label><input name="City" class="form-control" required></div>
  <div class="col-md-2"><label class="form-label">Zip</label><input name="Zip" class="form-control" required></div>
  <div class="col-md-2"><label class="form-label">State</label><input name="State" class="form-control" required></div>
  <div class="col-md-2"><label class="form-label">Country</label><input name="Country" class="form-control" required></div>
  <div class="col-12 mt-2">
    <button class="btn btn-success">Add Address</button>
    <a href="index.php" class="btn btn-secondary">⬅️ Back</a>
  </div>
</form>
</body>
</html>
