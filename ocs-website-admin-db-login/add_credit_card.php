<?php
session_start();
include 'db.php';

if (!isset($_SESSION['cid'])) {
  echo "<div class='alert alert-danger'>You must be logged in to access this page.</div>";
  echo "<a href='login.php' class='btn btn-primary'>Login</a>";
  exit;
}
$cid = $_SESSION['cid'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>Add Credit Card</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h3>💳 Add Credit Card</h3>
  <form method="POST" class="row g-3">
  <div class="col-md-6">
    <label class="form-label">Card Number</label>
    <input type="text" name="CCNumber" class="form-control" placeholder="Card Number" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Security Code (CVV)</label>
    <input type="text" name="SecNumber" class="form-control" placeholder="Security Code" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Owner Name</label>
    <input type="text" name="OwnerName" class="form-control" placeholder="Owner Name" required>
  </div>

  <div class="col-12">
    <label class="form-label">Billing Address</label>
    <input type="text" name="BilAddress" class="form-control" placeholder="Billing Address" required>
  </div>

  <div class="col-md-6">
    <label class="form-label">Card Type</label>
    <select name="CCType" class="form-select" required>
      <option value="">Select card type</option>
      <option value="VISA">VISA</option>
      <option value="MasterCard">MasterCard</option>
      <option value="Discover">Discover</option>
      <option value="Others">Others</option>
    </select>
  </div>

  <div class="col-md-6">
    <label class="form-label">Expiry Date</label>
    <input type="date" name="ExpDate" class="form-control" required>
  </div>

  <div class="col-12">
    <button type="submit" name="submit" class="btn btn-success">Add Card</button>
    <a href="index.php" class="btn btn-secondary">Back</a>
  </div>
</form>

  <?php
  if (isset($_POST['submit'])) {
    $sql = "INSERT INTO CREDIT_CARD (CCNumber, SecNumber, OwnerName, CCType, BilAddress, ExpDate, StoredCardCID)
            VALUES ('{$_POST['CCNumber']}', '{$_POST['SecNumber']}', '{$_POST['OwnerName']}', '{$_POST['CCType']}', '{$_POST['BilAddress']}', '{$_POST['ExpDate']}', $cid)";
    if ($conn->query($sql)) {
      echo '<div class="alert alert-success mt-3">✅ Credit card added!</div>';
    } else {
      echo '<div class="alert alert-danger mt-3">❌ Error: ' . $conn->error . '</div>';
    }
  }
  ?>
</body>
</html>