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

  $conn->query("UPDATE SHIPPING_ADDRESS 
                SET SAName='$saname', RecepientName='$recipient', Street='$street', SNumber='$snumber',
                    City='$city', Zip='$zip', State='$state', Country='$country'
                WHERE CID=$cid");

  echo "<div class='alert alert-info'>Address updated.</div>";
}

if (isset($_GET['delete']) && $_GET['delete'] === 'yes') {
  $conn->query("DELETE FROM SHIPPING_ADDRESS WHERE CID = $cid");
  echo "<div class='alert alert-success'>Address deleted.</div>";
}
?>
<!DOCTYPE html>
<html>
<head><title>View Shipping Address</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="container mt-5">
<h3>Your Shipping Address</h3>
<br>
<?php
$result = $conn->query("SELECT * FROM SHIPPING_ADDRESS WHERE CID = $cid");
if ($row = $result->fetch_assoc()) {
  echo "<form method='POST' class='border p-3 rounded mb-3 row g-3'>
    <div class='col-md-3'><input name='SAName' value='" . htmlspecialchars($row['SAName']) . "' class='form-control' required></div>
    <div class='col-md-3'><input name='RecepientName' value='" . htmlspecialchars($row['RecepientName']) . "' class='form-control' required></div>
    <div class='col-md-4'><input name='Street' value='" . htmlspecialchars($row['Street']) . "' class='form-control' required></div>
    <div class='col-md-2'><input name='SNumber' value='" . htmlspecialchars($row['SNumber']) . "' class='form-control' required></div>
    <div class='col-md-2'><input name='City' value='" . htmlspecialchars($row['City']) . "' class='form-control' required></div>
    <div class='col-md-2'><input name='Zip' value='" . htmlspecialchars($row['Zip']) . "' class='form-control' required></div>
    <div class='col-md-2'><input name='State' value='" . htmlspecialchars($row['State']) . "' class='form-control' required></div>
    <div class='col-md-2'><input name='Country' value='" . htmlspecialchars($row['Country']) . "' class='form-control' required></div>
    <div class='col-md-4'>
      <button class='btn btn-warning btn-sm me-2'>Update</button>
      <a href='view_shipping.php?delete=yes' class='btn btn-danger btn-sm' onclick='return confirm('Delete this address?')'>Delete</a>
    </div>
  </form>";
} else {
  echo "<div class='alert alert-info'>No shipping address found.</div>";
}
?>
<a href="index.php" class="btn btn-secondary">⬅️ Back</a>
</body>
</html>
