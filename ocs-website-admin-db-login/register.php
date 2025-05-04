<?php include 'db.php'; ?>
<!DOCTYPE html>
<html>
<head>
  <title>Register</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h3>Register New Customer</h3>
  <form method="POST" class="row g-3">
    <div class="col-md-6"><input type="text" class="form-control" name="fname" placeholder="First Name" required></div>
    <div class="col-md-6"><input type="text" class="form-control" name="lname" placeholder="Last Name" required></div>
    <div class="col-md-6"><input type="email" class="form-control" name="email" placeholder="Email" required></div>
    <div class="col-md-6"><input type="password" class="form-control" name="password" placeholder="Password" required></div>
    <div class="col-12"><input type="text" class="form-control" name="address" placeholder="Address" required></div>
    <div class="col-md-6"><input type="text" class="form-control" name="phone" placeholder="Phone" required></div>
    <div class="col-md-6">
      <select name="status" class="form-select">
        <option value="Regular">Regular</option>
        <option value="Silver">Silver</option>
        <option value="Gold">Gold</option>
        <option value="Platinum">Platinum</option>
      </select>
    </div>
    <div class="col-12">
      <button type="submit" name="submit" class="btn btn-primary">Register</button>
      <a href="index.php" class="btn btn-secondary">Back</a>
    </div>
  </form>
  <?php
  if (isset($_POST['submit'])) {
    $conn->query("INSERT INTO CUSTOMER (FName, LName, EMail, Password, Address, Phone, Status)
      VALUES ('{$_POST['fname']}', '{$_POST['lname']}', '{$_POST['email']}', '{$_POST['password']}', '{$_POST['address']}', '{$_POST['phone']}', '{$_POST['status']}')");
    echo '<div class="alert alert-success mt-3">✅ Registered successfully! You can now log in and manage your credit cards.</div>';
  }
  ?>
</body>
</html>