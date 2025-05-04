<?php
session_start();
require_once 'db.php';

// ✅ Only allow access if admin is logged in
if (!isset($_SESSION['admin'])) {
    echo "<div class='alert alert-danger text-center mt-5'>Access denied. Admins only. Please <a href='admin_login.php'>login here</a>.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h1 class="mb-4">🛠️ Admin Dashboard</h1>

    <div class="list-group">
        <a href="admin_product.php" class="list-group-item list-group-item-action">📦 Manage Products</a>
        <a href="view_orders.php" class="list-group-item list-group-item-action">🧾 View Orders</a>
        <a href="stats.php" class="list-group-item list-group-item-action">📊 View Sales Statistics</a>
        <a href="modify_customers.php" class="list-group-item list-group-item-action">👥 Manage Customers</a>
        <a href="admin_logout.php" class="list-group-item list-group-item-action text-danger">🚪 Logout</a>
    </div>
</body>
</html>
