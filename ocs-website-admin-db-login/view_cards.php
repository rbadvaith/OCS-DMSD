<?php
session_start();
include 'db.php';
if (!isset($_SESSION['cid'])) {
  header("Location: login.php");
  exit;
}
$cid = $_SESSION['cid'];

// DELETE card
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $del_card = intval($_GET['delete']);
  $conn->query("DELETE FROM CREDIT_CARD WHERE CCNumber = $del_card AND StoredCardCID = $cid");
  echo "<div class='alert alert-success'>Card deleted successfully.</div>";
}

// UPDATE card
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_card'])) {
  $old_card = $_POST['old_card'];
  $new_card = $_POST['CardNumber'];
  $cvv = $_POST['CVV'];
  $own = $_POST['own'];
  $adr = $_POST['BilAddress'];
  $cty = $_POST['CCType'];
  $exp = $_POST['ExpiryDate'];
  $conn->query("UPDATE CREDIT_CARD SET CCNumber='$new_card', SecNumber='$cvv', OwnerName='$own', BilAddress='$adr', CCType='$cty', ExpDate='$exp' WHERE CCNumber='$old_card' AND StoredCardCID=$cid");
  echo "<div class='alert alert-info'>Card updated successfully.</div>";
}

// DISPLAY cards
$result = $conn->query("SELECT * FROM CREDIT_CARD WHERE StoredCardCID = $cid");
?>
<!DOCTYPE html>
<html>
<head><title>Manage Credit Cards</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"></head>
<body class="container mt-5">
<h3>💳 Your Saved Credit Cards</h3>
<br>
<?php while ($row = $result->fetch_assoc()): ?>
  <form method="POST" class="row g-3 border p-3 mb-3 rounded">
    <input type="hidden" name="old_card" value="<?= htmlspecialchars($row['CCNumber']) ?>">

    <div class="col-md-3">
      <label class="form-label">Card Number</label>
      <input name="CardNumber" class="form-control" 
             value="<?= htmlspecialchars($row['CCNumber']) ?>" required>
    </div>

    <div class="col-md-2">
      <label class="form-label">Card Type</label>
      <select name="CCType" class="form-select" required>
        <option value="VISA"       <?= $row['CCType']==='VISA'       ? 'selected' : '' ?>>VISA</option>
        <option value="MasterCard" <?= $row['CCType']==='MasterCard' ? 'selected' : '' ?>>MasterCard</option>
        <option value="Discover"   <?= $row['CCType']==='Discover'   ? 'selected' : '' ?>>Discover</option>
        <option value="Others"     <?= $row['CCType']==='Others'     ? 'selected' : '' ?>>Others</option>
      </select>
    </div>

    <div class="col-md-2">
      <label class="form-label">CVV</label>
      <input name="CVV" class="form-control" 
             value="<?= htmlspecialchars($row['SecNumber']) ?>" required>
    </div>

    <div class="col-md-3">
      <label class="form-label">Owner Name</label>
      <input name="own" class="form-control" 
             value="<?= htmlspecialchars($row['OwnerName']) ?>" required>
    </div>

    <div class="col-md-4">
      <label class="form-label">Billing Address</label>
      <input name="BilAddress" class="form-control" 
             value="<?= htmlspecialchars($row['BilAddress'] ?? '') ?>" required>
    </div>

    <div class="col-md-3">
      <label class="form-label">Expiry Date</label>
      <input name="ExpiryDate" class="form-control" 
             value="<?= htmlspecialchars($row['ExpDate']) ?>" required>
    </div>

    <div class="col-12">
      <button name="update_card" class="btn btn-warning btn-sm me-2">Update</button>
      <a href="view_cards.php?delete=<?= urlencode($row['CCNumber']) ?>"
         class="btn btn-danger btn-sm"
         onclick="return confirm('Delete this card?')">Delete</a>
    </div>
  </form>
<?php endwhile; ?>

<a href="index.php" class="btn btn-secondary">⬅️ Back</a>
</body>
</html>