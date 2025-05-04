<?php
session_start();
include 'db.php';

function generateNewBid($conn) {
    $result = $conn->query("SELECT MAX(BID) AS max_bid FROM BASKET");
    if ($result && $row = $result->fetch_assoc()) {
        return $row['max_bid'] + 1;
    }
    return 1;
}

if (!isset($_SESSION['cid'])) {
    echo "<div class='alert alert-warning'>Please log in to add items to your cart.</div>";
    echo "<a href='login.php' class='btn btn-primary'>Login</a>";
    exit;
}

$cid = $_SESSION['cid'];

if (isset($_POST['submit'])) {
    $pid = (int)$_POST['pid'];
    $qty = (int)$_POST['qty'];

    // 🛒 Handling Basket (BID)
    if (!isset($_SESSION['BID'])) {
        // Try to find existing basket
        $find_existing = $conn->query("SELECT BID FROM BASKET WHERE CID = $cid ORDER BY CreatedDate DESC LIMIT 1");

        if ($find_existing->num_rows > 0) {
            $existing_bid = $find_existing->fetch_assoc()['BID'];

            // Check if existing basket is already used in TRANSACTION
            $check_transaction = $conn->query("SELECT * FROM TRANSACTION WHERE BID = $existing_bid");

            if ($check_transaction && $check_transaction->num_rows > 0) {
                // Already checked out ➔ Create new basket
                $new_bid = generateNewBid($conn);
                $conn->query("INSERT INTO BASKET (CID, BID, CreatedDate) VALUES ($cid, $new_bid, NOW())");
                $_SESSION['BID'] = $new_bid;
            } else {
                // Still active ➔ Use same basket
                $_SESSION['BID'] = $existing_bid;
            }
        } else {
            // No basket found ➔ Create new
            $new_bid = generateNewBid($conn);
            $conn->query("INSERT INTO BASKET (CID, BID, CreatedDate) VALUES ($cid, $new_bid, NOW())");
            $_SESSION['BID'] = $new_bid;
        }
    }

    $bid = $_SESSION['BID'];


    $check_transaction = $conn->query("SELECT * FROM TRANSACTION WHERE BID = $bid");
    if ($check_transaction && $check_transaction->num_rows > 0) {
        // 🚀 Create new basket since old one was already checked out
        $new_bid = generateNewBid($conn);
        $conn->query("INSERT INTO BASKET (CID, BID, CreatedDate) VALUES ($cid, $new_bid, NOW())");
        $_SESSION['BID'] = $new_bid;
        $bid = $new_bid; // Update bid
    }
    

    // 🛒 Now adding product into basket
    $exists = $conn->query("SELECT * FROM APPEARS_IN WHERE BID = $bid AND PID = $pid");

    if ($exists->num_rows > 0) {
        // Already exists ➔ just update quantity
        $conn->query("UPDATE APPEARS_IN SET Quantity = Quantity + $qty WHERE BID = $bid AND PID = $pid");
    } else {
        // Not exists ➔ insert new row
        // Fetch customer status
        $cid = $_SESSION['cid'];
        $status_result = $conn->query("SELECT Status FROM CUSTOMER WHERE CID = $cid");
        $status = $status_result->fetch_assoc()['Status'] ?? '';

        // Decide price
        if ($status == 'Gold' || $status == 'Platinum') {
            // Check if OfferPrice exists
            $offer_result = $conn->query("SELECT OfferPrice FROM OFFER_PRODUCT WHERE PID = $pid");
            if ($offer_result && $offer_result->num_rows > 0) {
                $price = $offer_result->fetch_assoc()['OfferPrice'];
            } else {
                $price_result = $conn->query("SELECT PPrice FROM PRODUCT WHERE PID = $pid");
                $price = $price_result->fetch_assoc()['PPrice'];
            }
        } else {
            // Normal price for Silver or Others
            $price_result = $conn->query("SELECT PPrice FROM PRODUCT WHERE PID = $pid");
            $price = $price_result->fetch_assoc()['PPrice'];
        }
                
        $conn->query("INSERT INTO APPEARS_IN (BID, PID, Quantity, PriceSold) VALUES ($bid, $pid, $qty, $price)");
    }

    header("Location: view_cart.php");
    exit;
}

// 🛒 Remove product from basket
if (isset($_GET['remove']) && isset($_GET['bid'])) {
    $remove_pid = (int)$_GET['remove'];
    $bid = (int)$_GET['bid'];
    $conn->query("DELETE FROM APPEARS_IN WHERE BID = $bid AND PID = $remove_pid");
    header("Location: view_cart.php");
    exit;
}
?>
