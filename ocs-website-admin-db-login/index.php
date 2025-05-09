<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
  <title>OCS Home</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h2 class="mb-4">Online Computer Store</h2>

  <?php
if (isset($_SESSION['cid'])) {
    require_once 'db.php';
    $cid = $_SESSION['cid'];

    // Fetch total purchase only for Delivered orders
    $totalPurchaseQuery = $conn->query("
        SELECT COALESCE(SUM(P.PPrice * AI.Quantity), 0) AS TotalPurchase
        FROM APPEARS_IN AI
        JOIN BASKET B ON AI.BID = B.BID
        JOIN PRODUCT P ON AI.PID = P.PID
        JOIN TRANSACTION T ON B.BID = T.BID
        WHERE B.CID = $cid AND T.TTag = 'Delivered'
    ");

    $totalPurchaseRow = $totalPurchaseQuery->fetch_assoc();
    $totalPurchase = (float)$totalPurchaseRow['TotalPurchase'];

    // Determine new status
    $new_status = 'Regular';
    if ($totalPurchase > 21000) {
        $new_status = 'Platinum';
    } elseif ($totalPurchase > 14000) {
        $new_status = 'Gold';
    } elseif ($totalPurchase > 7000) {
        $new_status = 'Silver';
    }

    // Update CUSTOMER status in DB
    $conn->query("UPDATE CUSTOMER SET Status = '$new_status' WHERE CID = $cid");
}
?>


  <?php if (isset($_SESSION['cid'])): ?>
    <p class="alert alert-success">Welcome, Customer #<?php echo $_SESSION['cid']; ?>!</p>
  <?php else: ?>
    <p class="alert alert-warning">Welcome, Guest! Please log in to access all features.</p>
  <?php endif; ?>

  <div class="list-group">
    <?php if (!isset($_SESSION['cid'])): ?>
      <a href="register.php" class="list-group-item list-group-item-action">Register Customer</a>
      <a href="login.php" class="list-group-item list-group-item-action">Login</a>
      <a href="admin_login.php" class="list-group-item list-group-item-action list-group-item-danger">🔐 Admin Login</a>
    <?php else: ?>
      <a href="logout.php" class="list-group-item list-group-item-action text-danger">Logout</a>
      <a href="profile.php" class="list-group-item list-group-item-action">Profile</a>
      <a href="add_credit_card.php" class="list-group-item list-group-item-action">Add Credit Card</a>
      <a href="view_cards.php" class="list-group-item list-group-item-action">View My Cards</a>
      <a href="view_cart.php" class="list-group-item list-group-item-action">View My Cart</a>
      <a href="view_user_orders.php" class="list-group-item list-group-item-action">🛒 View My Orders</a>
    <?php endif; ?>

    <a href="catalog.php" class="list-group-item list-group-item-action">Browse Products</a>
  </div>

<?php if (isset($_SESSION['cid'])): ?>
  <div class="mt-3">
    <a href="add_shipping.php" class="btn btn-outline-primary me-2">📬 Add Shipping Address</a>
    <a href="view_shipping.php" class="btn btn-outline-success me-2">📦 View Shipping Address</a>
  </div>
<?php endif; ?>

</body>
</html>