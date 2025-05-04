<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['cid'])) {
    echo "<div class='alert alert-warning'>Please log in to view your profile.</div>";
    exit;
}

$cid = $_SESSION['cid'];

// Fetch customer info
$customerQuery = $conn->query("SELECT * FROM CUSTOMER WHERE CID = $cid");
$customer = $customerQuery->fetch_assoc();

// Fetch total purchase amount for this customer
$totalPurchaseQuery = $conn->query("
    SELECT COALESCE(SUM(P.PPrice * AI.Quantity), 0) AS TotalPurchase
    FROM APPEARS_IN AI
    JOIN BASKET B ON AI.BID = B.BID
    JOIN PRODUCT P ON AI.PID = P.PID
    WHERE B.CID = $cid
");

$totalPurchaseRow = $totalPurchaseQuery->fetch_assoc();
$totalPurchase = $totalPurchaseRow['TotalPurchase'];

// Set customer status from CUSTOMER table directly
$customerStatus = $customer['Status'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $new_fname = $conn->real_escape_string($_POST['fname']);
    $new_lname = $conn->real_escape_string($_POST['lname']);
    $new_email = $conn->real_escape_string($_POST['email']);
    $new_phone = $conn->real_escape_string($_POST['phone']);
    $new_address = $conn->real_escape_string($_POST['address']);

    $conn->query("UPDATE CUSTOMER SET FName = '$new_fname', LName = '$new_lname', Email = '$new_email', Phone = '$new_phone', Address = '$new_address' WHERE CID = $cid");
    header("Location: profile.php");
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg p-3 mb-5 bg-body rounded">
                <div class="card-header bg-primary text-white">
                    <h3 class="text-center">Customer Profile</h3>
                </div>
                <div class="card-body">

                <?php if ($action === 'edit') : ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">First Name</label>
                            <input type="text" name="fname" class="form-control" value="<?= htmlspecialchars($customer['FName']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Last Name</label>
                            <input type="text" name="lname" class="form-control" value="<?= htmlspecialchars($customer['LName']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['Email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['Phone']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" required><?= htmlspecialchars($customer['Address']) ?></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" name="update_profile" class="btn btn-success">Update Profile</button>
                            <a href="profile.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>

                <?php else : ?>
                    <p class="fs-5"><strong>Name:</strong> <?= htmlspecialchars($customer['FName'] . ' ' . $customer['LName']) ?></p>
                    <p class="fs-5"><strong>Email:</strong> <?= htmlspecialchars($customer['Email']) ?></p>
                    <p class="fs-5"><strong>Phone:</strong> <?= htmlspecialchars($customer['Phone']) ?></p>
                    <p class="fs-5"><strong>Address:</strong> <?= htmlspecialchars($customer['Address']) ?></p>

                    <p class="fs-5">
                        <strong>Customer Status:</strong> 
                        <span class="badge bg-info">
                            <?= htmlspecialchars($customerStatus) ?>
                        </span>
                    </p>

                    <p class="fs-5">
                        <strong>Total Purchase:</strong> 
                        <span class="badge bg-warning text-dark">
                            $<?= number_format($totalPurchase, 2) ?>
                        </span>
                    </p>

                    <div class="text-center mt-4">
                        <a href="profile.php?action=edit" class="btn btn-outline-primary me-2">✏️ Edit My Profile</a>
                        <a href="view_user_orders.php" class="btn btn-outline-success">🛒 View My Orders</a>
                        <a href="index.php" class="btn btn-outline-primary me-2">Back to Main</a>
                    </div>
                <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>