
<?php
require_once 'db.php';
session_start();

if (isset($_POST['pid'], $_POST['quantity']) && isset($_SESSION['cid'])) {
    $pid = intval($_POST['pid']);
    $quantity = intval($_POST['quantity']);
    $cid = $_SESSION['cid'];

    // Get the latest BID for the current customer
    $result = $conn->query("SELECT BID FROM BASKET WHERE CID = $cid ORDER BY BID DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $bid = $result->fetch_assoc()['BID'];

        // Get product price
        $priceResult = $conn->query("SELECT PPrice FROM PRODUCT WHERE PID = $pid");
        $priceRow = $priceResult->fetch_assoc();
        $price = $priceRow['PPrice'];

        // Calculate new total for the item
        $priceSold = $price * $quantity;

        // Update quantity and price in APPEARS_IN
        $conn->query("UPDATE APPEARS_IN SET Quantity = $quantity, PriceSold = $priceSold WHERE BID = $bid AND PID = $pid");

        echo "Quantity updated successfully.";
    } else {
        echo "Basket not found.";
    }
} else {
    echo "Invalid request.";
}
?>
