<?php
session_start();
include 'db.php';

if (!isset($_SESSION['admin'])) {
  header("Location: admin_login.php");
  exit;
}

if (!isset($_GET['pid']) || !is_numeric($_GET['pid'])) {
  echo "<div class='alert alert-danger'>Invalid product ID.</div>";
  exit;
}

$pid = intval($_GET['pid']);
$product = $conn->query("SELECT * FROM PRODUCT WHERE PID = $pid")->fetch_assoc();

if (!$product) {
  echo "<div class='alert alert-danger'>Product not found.</div>";
  exit;
}

$ptype = $product['PType'];
$extra = [];

if ($ptype === "Laptop") {
  $extra = $conn->query("SELECT * FROM LAPTOP WHERE PID = $pid")->fetch_assoc();
} elseif ($ptype === "Computer") {
  $extra = $conn->query("SELECT * FROM COMPUTER WHERE PID = $pid")->fetch_assoc();
} elseif ($ptype === "Printer") {
  $extra = $conn->query("SELECT * FROM PRINTER WHERE PID = $pid")->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $pname = $_POST['PName'];
  $pprice = $_POST['PPrice'];
  $pdesc = $_POST['Description'];
  $pqty = $_POST['PQuantity'];

  $conn->query("UPDATE PRODUCT SET PName='$pname', PPrice=$pprice, Description='$pdesc', PQuantity=$pqty WHERE PID=$pid");

  if ($ptype === "Laptop") {
    $btype = $_POST['BType'];
    $weight = $_POST['Weight'];
    $cputype = $_POST['CPUType'];
    $conn->query("UPDATE LAPTOP SET BType='$btype', Weight='$weight', CPUType='$cputype' WHERE PID=$pid");
  } elseif ($ptype === "Computer") {
    $cputype = $_POST['CPUType'];
    $conn->query("UPDATE COMPUTER SET CPUType='$cputype' WHERE PID=$pid");
  } elseif ($ptype === "Printer") {
    $pt = $_POST['PrinterType'];
    $res = $_POST['Resolution'];
    $conn->query("UPDATE PRINTER SET PrinterType='$pt', Resolution='$res' WHERE PID=$pid");
  }

  header("Location: admin_product.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Edit Product</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h3>Edit Product ID #<?= $pid ?></h3>
  <form method="POST" class="row g-3">
    <div class="col-md-4">
      <label class="form-label">Product ID (PID)</label>
      <input type="number" class="form-control" value="<?= $pid ?>" readonly>
    </div>
    <div class="col-md-4"><label class="form-label">Product Name</label><input name="PName" class="form-control" value="<?= htmlspecialchars($product['PName']) ?>" required></div>
    <div class="col-md-4"><label class="form-label">Price</label><input name="PPrice" type="number" class="form-control" value="<?= $product['PPrice'] ?>" required></div>
    <div class="col-md-4"><label class="form-label">Quantity</label><input name="PQuantity" type="number" class="form-control" value="<?= $product['PQuantity'] ?>" required></div>
    <div class="col-md-12"><label class="form-label">Description</label><input name="Description" class="form-control" value="<?= htmlspecialchars($product['Description']) ?>" required></div>

    <?php if ($ptype === "Laptop"): ?>
      <div class="col-md-4"><label class="form-label">BType</label><input name="BType" class="form-control" value="<?= $extra['BType'] ?>"></div>
      <div class="col-md-4"><label class="form-label">Weight</label><input name="Weight" class="form-control" value="<?= $extra['Weight'] ?>"></div>
      <div class="col-md-4"><label class="form-label">CPUType</label><input name="CPUType" class="form-control" value="<?= $extra['CPUType'] ?>"></div>
    <?php elseif ($ptype === "Computer"): ?>
      <div class="col-md-6"><label class="form-label">CPUType</label><input name="CPUType" class="form-control" value="<?= $extra['CPUType'] ?>"></div>
    <?php elseif ($ptype === "Printer"): ?>
      <div class="col-md-6"><label class="form-label">PrinterType</label><input name="PrinterType" class="form-control" value="<?= $extra['PrinterType'] ?>"></div>
      <div class="col-md-6"><label class="form-label">Resolution</label><input name="Resolution" class="form-control" value="<?= $extra['Resolution'] ?>"></div>
    <?php endif; ?>

    <div class="col-12 mt-3">
      <button class="btn btn-primary">Update</button>
      <a href="admin_product.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</body>
</html>
