<?php
session_start();
require_once 'db.php';

// ✅ Cancel Order if user clicked "Cancel" and confirmed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_bid'])) {
    $bidToCancel = intval($_POST['cancel_order_bid']);
    $conn->query("UPDATE TRANSACTION SET TTag = 'Cancelled' WHERE BID = $bidToCancel AND (TTag = 'In-Progress' OR TTag = 'Not-Delivered')");
    header("Location: view_user_orders.php");
    exit;
}

// ✅ Auto-update order status based on overdue logic
$overdueOrders = $conn->query("SELECT T.BID, T.CID, T.TDate, T.TTag, C.Status AS CustomerStatus 
                                FROM TRANSACTION T 
                                JOIN CUSTOMER C ON T.CID = C.CID");
$currentDate = new DateTime();

while ($order = $overdueOrders->fetch_assoc()) {
    $tDate = new DateTime($order['TDate']);
    $daysPassed = $currentDate->diff($tDate)->days;
    $status = strtolower($order['CustomerStatus']);
    $bid = $order['BID'];

    $allowedDays = 5;
    if ($status == 'silver') $allowedDays = 4;
    if ($status == 'gold') $allowedDays = 3;
    if ($status == 'platinum') $allowedDays = 2;

    if ($order['TTag'] == 'In-Progress' && $daysPassed > $allowedDays) {
        $conn->query("UPDATE TRANSACTION SET TTag = 'Not-Delivered' WHERE BID = $bid");
    }
    elseif ($order['TTag'] == 'Not-Delivered' && $daysPassed <= $allowedDays) {
        $conn->query("UPDATE TRANSACTION SET TTag = 'In-Progress' WHERE BID = $bid");
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>My Orders</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script>
        function confirmCancel(bid) {
            if (confirm("Are you sure you want to cancel this order?")) {
                const form = document.createElement("form");
                form.method = "POST";
                form.style.display = "none";
                const input = document.createElement("input");
                input.type = "hidden";
                input.name = "cancel_order_bid";
                input.value = bid;
                form.appendChild(input);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</head>
<body class="container mt-5">
    <h2 class="mb-4">📜 My Orders</h2>

    <div class="mb-4">
        <input type="text" id="orderSearch" class="form-control" placeholder="Search by Basket ID or Product Name...">
    </div>

    <?php
    if (!isset($_SESSION['cid'])) {
        echo "<div class='alert alert-warning'>Please log in to view your orders.</div>";
        exit;
    }
    $cid = $_SESSION['cid'];

    $sql = "SELECT T.BID, T.TDate, T.TTag, S.Street, S.SNumber, S.City, S.Zip, S.State, S.Country, S.RecepientName,
                   P.PName, A.Quantity, A.PriceSold
            FROM TRANSACTION T
            JOIN SHIPPING_ADDRESS S ON T.SAName = S.SAName AND T.CID = S.CID
            JOIN APPEARS_IN A ON T.BID = A.BID
            JOIN PRODUCT P ON A.PID = P.PID
            WHERE T.CID = $cid
            ORDER BY T.BID DESC, T.TDate DESC";

    $result = $conn->query($sql);
    $currentBID = null;

    if ($result->num_rows > 0) {
        echo "<div class='accordion' id='ordersAccordion'>";
        while ($row = $result->fetch_assoc()) {
            if ($currentBID !== $row['BID']) {
                if ($currentBID !== null) echo "</tbody></table>" .
                                               (in_array($lastTag, ['In-Progress', 'Not-Delivered']) ? "<button class='btn btn-danger mt-2' onclick='confirmCancel($currentBID)'>Cancel Order</button>" : "") .
                                               "</div></div></div>";

                $currentBID = $row['BID'];
                $lastTag = $row['TTag'];
                echo "<div class='accordion-item mb-3 order-card' data-bid='{$currentBID}'>
                        <h2 class='accordion-header' id='heading$currentBID'>
                          <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapse$currentBID' aria-expanded='false' aria-controls='collapse$currentBID'>
                            Order #$currentBID - {$row['TDate']} - Status: {$row['TTag']}
                          </button>
                        </h2>
                        <div id='collapse$currentBID' class='accordion-collapse collapse' aria-labelledby='heading$currentBID' data-bs-parent='#ordersAccordion'>
                          <div class='accordion-body'>
                            <p><strong>Shipping To:</strong><br>
                               {$row['RecepientName']}<br>
                               {$row['Street']} {$row['SNumber']}, {$row['City']} - {$row['Zip']}<br>
                               {$row['State']}, {$row['Country']}</p>
                            <table class='table table-bordered mt-3'>
                              <thead><tr><th>Product</th><th>Quantity</th><th>Subtotal</th></tr></thead>
                              <tbody>";
            }
            echo "<tr>
                    <td class='product-name'>{$row['PName']}</td>
                    <td>{$row['Quantity']}</td>
                    <td>\${$row['PriceSold']}</td>
                  </tr>";
        }
        echo "</tbody></table>" .
             (in_array($lastTag, ['In-Progress', 'Not-Delivered']) ? "<button class='btn btn-danger mt-2' onclick='confirmCancel($currentBID)'>Cancel Order</button>" : "") .
             "</div></div></div></div>";
    } else {
        echo "<div class='alert alert-info'>No orders found.</div>";
    }
    ?>

    <a href="index.php" class="btn btn-secondary mt-4">⬅ Back to Home</a>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('orderSearch').addEventListener('keyup', function() {
            let filter = this.value.toUpperCase();
            let cards = document.querySelectorAll('.order-card');
            cards.forEach(card => {
                let basketId = card.getAttribute('data-bid').toUpperCase();
                let productNames = card.querySelectorAll('.product-name');
                let match = basketId.includes(filter);

                productNames.forEach(nameCell => {
                    if (nameCell.textContent.toUpperCase().includes(filter)) {
                        match = true;
                    }
                });

                card.style.display = match ? '' : 'none';
            });
        });
    </script>
</body>
</html>
