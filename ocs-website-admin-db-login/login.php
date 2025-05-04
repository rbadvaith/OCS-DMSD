<?php
session_start();
include 'db.php';

if (isset($_POST['submit'])) {
  $email = $conn->real_escape_string($_POST['email']);
  $password = $conn->real_escape_string($_POST['password']);
  $res = $conn->query("SELECT CID FROM CUSTOMER WHERE EMail='$email' AND Password='$password'");
  if ($res->num_rows === 1) {
    $row = $res->fetch_assoc();
    $_SESSION['cid'] = $row['CID'];
    header("Location: index.php");
    exit;
  } else {
    $error = "Invalid email or password.";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Customer Login</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h3>Customer Login</h3>
  <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
  <form method="POST" class="row g-3">
    <div class="col-md-6"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
    <div class="col-md-6"><input type="password" name="password" class="form-control" placeholder="Password" required></div>
    <div class="col-12">
      <button type="submit" name="submit" class="btn btn-primary">Login</button>
      <a href="index.php" class="btn btn-secondary">Back</a>
    </div>
  </form>
</body>
</html>