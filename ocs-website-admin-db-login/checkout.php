<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['cid'])) {
    echo "<div class='alert alert-warning'>You must be logged in to checkout.</div>";
    exit;
}

$cid = $_SESSION['cid'];

// Find the latest basket
$result = $conn->query("SELECT BID FROM BASKET WHERE CID = $cid ORDER BY BID DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $bid = $result->fetch_assoc()['BID'];

    // Deduct stock
    $items = $conn->query("SELECT PID, Quantity FROM APPEARS_IN WHERE BID = $bid");
    while ($item = $items->fetch_assoc()) {
        $pid = $item['PID'];
        $qty = $item['Quantity'];
        $conn->query("UPDATE PRODUCT SET PQuantity = PQuantity - $qty WHERE PID = $pid");
    }

    // Insert transaction
    if (isset($_POST['credit_card']) && isset($_POST['shipping_address'])) {
        $creditCard = $_POST['credit_card'];
        $shippingAddress = $_POST['shipping_address'];
    

        $conn->query("INSERT INTO TRANSACTION (BID, CID, SAName, CCNumber, TDate, TTag)
                      VALUES ('$bid', '$cid', '$shippingAddress', '$creditCard', NOW(), 'In-Progress')");

        // ✅ After checkout, immediately create a new empty basket
        $new_bid_result = $conn->query("SELECT MAX(BID) AS max_bid FROM BASKET");
        $new_bid = 1;
        if ($new_bid_result && $row = $new_bid_result->fetch_assoc()) {
            $new_bid = $row['max_bid'] + 1;
        }
        $conn->query("INSERT INTO BASKET (CID, BID, CreatedDate) VALUES ($cid, $new_bid, NOW())");

        // Update Session with new Basket ID if needed
        $_SESSION['BID'] = $new_bid;

        // Clean old checkout selections
        unset($_SESSION['credit_card']);
        unset($_SESSION['shipping_address']);
    }
} else {
    echo "<div class='alert alert-warning'>No active basket found.</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Placed</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .thankyou-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 100px;
        }
        .thankyou-page h1 {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        .thankyou-page p {
            font-size: 1.2rem;
            color: #555;
        }
    </style>
</head>
<body>
<div class="container thankyou-page">
    <h1>🎉 Thank You!</h1>
    <p>Your order has been successfully placed.</p>
    <a href="catalog.php" class="btn btn-primary mt-4">🛒 Continue Shopping</a>
    <a href="index.php" class="btn btn-secondary mt-2">🏠 Back to Home</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
