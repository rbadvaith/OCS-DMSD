<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
  echo "<div class='alert alert-danger'>Admin access only.</div>";
  echo "<a href='admin_login.php' class='btn btn-primary'>Login as Admin</a>";
  exit;
}

// Delete logic
// Safe Delete logic with cascading clean-up
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $del_cid = intval($_GET['delete']);

  // Delete from SILVER_AND_ABOVE
  $conn->query("DELETE FROM SILVER_AND_ABOVE WHERE CID = $del_cid");

  // Delete associated credit cards
  $conn->query("DELETE FROM CREDIT_CARD WHERE StoredCardCID = $del_cid");

  // Delete shipping addresses
  $conn->query("DELETE FROM SHIPPING_ADDRESS WHERE CID = $del_cid");

  // Handle basket and appears_in
  $baskets = $conn->query("SELECT BID FROM BASKET WHERE CID = $del_cid");
  while ($basket = $baskets->fetch_assoc()) {
      $bid = $basket['BID'];
      $conn->query("DELETE FROM APPEARS_IN WHERE BID = $bid");
      $conn->query("DELETE FROM TRANSACTION WHERE BID = $bid");
  }

  // Delete baskets
  $conn->query("DELETE FROM BASKET WHERE CID = $del_cid");

  // Finally, delete customer
  $conn->query("DELETE FROM CUSTOMER WHERE CID = $del_cid");

  echo "<div class='alert alert-success'>✅ Customer ID $del_cid and all related records deleted successfully.</div>";
}


// Insert or update logic
if (isset($_POST['submit'])) {
  $cid = isset($_POST['CID']) ? (int)$_POST['CID'] : 0;
  $fname = $_POST['FName'];
  $lname = $_POST['LName'];
  $email = $_POST['Email'];
  $password = $_POST['Password'];
  $address = $_POST['Address'];
  $phone = $_POST['Phone'];
  $status = $_POST['Status'];

  if ($cid === 0) {
    // New customer
    $result = $conn->query("SELECT MAX(CID) as maxcid FROM CUSTOMER");
    $max = $result->fetch_assoc()['maxcid'];
    $new_cid = max(1001, $max + 1);
    $conn->query("INSERT INTO CUSTOMER (CID, FName, LName, Email, Password, Address, Phone, Status) 
                  VALUES ($new_cid, '$fname', '$lname', '$email', '$password', '$address', '$phone', '$status')");
    echo "<div class='alert alert-success mt-3'>✅ Customer added successfully!</div>";
  } else {
    // Update existing
    $conn->query("UPDATE CUSTOMER SET 
      FName='$fname', LName='$lname', Email='$email', Password='$password', 
      Address='$address', Phone='$phone', Status='$status' WHERE CID=$cid");
    header("Location: modify_customers.php?updated=1");
    exit;
  }
}

// Edit logic
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
  $cid_edit = intval($_GET['edit']);
  $result = $conn->query("SELECT * FROM CUSTOMER WHERE CID = $cid_edit");
  if ($result->num_rows > 0) {
    $edit_data = $result->fetch_assoc();
    $edit_mode = true;
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Modify Customers</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
<?php if (isset($_GET['updated'])): ?>
  <div class="alert alert-info">🔄 Customer updated successfully!</div>
<?php endif; ?>

<h3 class="mb-4"><?= $edit_mode ? "✏️ Edit Customer #{$edit_data['CID']}" : "👤 Add Customer" ?></h3>
<form method="POST" class="row g-3 border p-4 rounded bg-light shadow-sm">
  <?php if ($edit_mode): ?>
    <input type="hidden" name="CID" value="<?= $edit_data['CID'] ?>">
  <?php endif; ?>
  <div class="col-md-4"><label class="form-label">First Name</label><input name="FName" class="form-control" required value="<?= $edit_mode ? htmlspecialchars($edit_data['FName']) : '' ?>"></div>
  <div class="col-md-4"><label class="form-label">Last Name</label><input name="LName" class="form-control" required value="<?= $edit_mode ? htmlspecialchars($edit_data['LName']) : '' ?>"></div>
  <div class="col-md-4"><label class="form-label">Email</label><input type="email" name="Email" class="form-control" required value="<?= $edit_mode ? htmlspecialchars($edit_data['Email']) : '' ?>"></div>
  <div class="col-md-6"><label class="form-label">Password</label><input name="Password" class="form-control" required value="<?= $edit_mode ? htmlspecialchars($edit_data['Password']) : '' ?>"></div>
  <div class="col-md-6"><label class="form-label">Address</label><input name="Address" class="form-control" required value="<?= $edit_mode ? htmlspecialchars($edit_data['Address']) : '' ?>"></div>
  <div class="col-md-6"><label class="form-label">Phone</label><input name="Phone" class="form-control" required value="<?= $edit_mode ? htmlspecialchars($edit_data['Phone']) : '' ?>"></div>
  <div class="col-md-6">
  <label class="form-label">Status</label>
  <select name="Status" class="form-select" required>
    <option value="Regular" <?= ($edit_mode && $edit_data['Status'] == 'Regular') ? 'selected' : '' ?>>Regular</option>
    <option value="Silver" <?= ($edit_mode && $edit_data['Status'] == 'Silver') ? 'selected' : '' ?>>Silver</option>
    <option value="Gold" <?= ($edit_mode && $edit_data['Status'] == 'Gold') ? 'selected' : '' ?>>Gold</option>
    <option value="Platinum" <?= ($edit_mode && $edit_data['Status'] == 'Platinum') ? 'selected' : '' ?>>Platinum</option>
  </select>
  </div>

  <div class="col-12 mt-3">
    <button name="submit" class="btn btn-<?= $edit_mode ? 'primary' : 'success' ?>">
      <?= $edit_mode ? 'Update Customer' : 'Add Customer' ?>
    </button>
    <a href="admin_dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
  </div>
</form>

<hr class="my-5">
<h4>📋 All Customers</h4>

<form method="GET" class="row g-3 mb-4">
  <div class="col-md-4">
    <input type="text" name="search" class="form-control" placeholder="🔍 Search by Name, Email or Phone" value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
  </div>
  <div class="col-md-2">
    <button type="submit" class="btn btn-primary">Search</button>
    <a href="modify_customers.php" class="btn btn-secondary">Reset</a>
  </div>
</form>

<table class="table table-bordered">
  <thead class="table-light">
    <tr><th>CID</th><th>FName</th><th>LName</th><th>Email</th><th>Password</th><th>Address</th><th>Phone</th><th>Status</th><th>Action</th></tr>
  </thead>
  <tbody>
  <?php
    $where = "";
    if (isset($_GET['search']) && $_GET['search'] !== "") {
      $s = $conn->real_escape_string($_GET['search']);
      $where = "WHERE FName LIKE '%$s%' OR LName LIKE '%$s%' OR Email LIKE '%$s%' OR Phone LIKE '%$s%'";
    }
    $result = $conn->query("SELECT * FROM CUSTOMER $where ORDER BY CID DESC");
    while ($row = $result->fetch_assoc()) {
      echo "<tr>
              <td>{$row['CID']}</td>
              <td>{$row['FName']}</td>
              <td>{$row['LName']}</td>
              <td>{$row['Email']}</td>
              <td>{$row['Password']}</td>
              <td>{$row['Address']}</td>
              <td>{$row['Phone']}</td>
              <td>{$row['Status']}</td>
              <td>
                <a href='modify_customers.php?edit={$row['CID']}' class='btn btn-warning btn-sm'>Edit</a>
                <a href='modify_customers.php?delete={$row['CID']}' class='btn btn-danger btn-sm' onclick='return confirm('Delete this customer?')'>Delete</a>
              </td>
            </tr>";
    }
  ?>
  </tbody>
</table>
</body>
</html>
