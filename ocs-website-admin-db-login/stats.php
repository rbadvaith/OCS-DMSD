<?php
require_once 'db.php';

// Set default date range (can be replaced by form inputs)
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '2000-01-01';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Statistics</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
    <h2 class="mb-4">📊 Sales Statistics</h2>

    <!-- Date Range Filter -->
    <form method="GET" class="row g-3 mb-5">
        <div class="col-auto">
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
        </div>
        <div class="col-auto">
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary">Filter</button>
        </div>
    </form>

    <?php
    // 1. Total amount charged per credit card (Delivered only)
    $sql1 = "SELECT CCNumber, SUM(A.PriceSold) AS TotalAmount
             FROM TRANSACTION T
             JOIN APPEARS_IN A ON T.BID = A.BID
             WHERE T.TTag = 'Delivered'
             GROUP BY T.CCNumber";
    $result1 = $conn->query($sql1);
    ?>
    <h4>Total Amount Charged per Credit Card</h4>
    <table class="table table-bordered">
        <thead><tr><th>Credit Card</th><th>Total Amount</th></tr></thead>
        <tbody>
        <?php while ($row = $result1->fetch_assoc()): ?>
            <tr><td>**** **** **** <?= htmlspecialchars(substr($row['CCNumber'], -4)) ?></td><td>$<?= number_format($row['TotalAmount'], 2) ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    // 2. 10 best customers (money spent - Delivered only)
    $sql2 = "SELECT C.CID, SUM(A.PriceSold) AS TotalSpent
             FROM CUSTOMER C
             JOIN TRANSACTION T ON C.CID = T.CID
             JOIN APPEARS_IN A ON T.BID = A.BID
             WHERE T.TTag = 'Delivered'
             GROUP BY C.CID
             ORDER BY TotalSpent DESC
             LIMIT 10";
    $result2 = $conn->query($sql2);
    ?>
    <h4>Top 10 Customers by Money Spent</h4>
    <table class="table table-bordered">
        <thead><tr><th>Customer ID</th><th>Total Spent</th></tr></thead>
        <tbody>
        <?php while ($row = $result2->fetch_assoc()): ?>
            <tr><td><?= htmlspecialchars($row['CID']) ?></td><td>$<?= number_format($row['TotalSpent'], 2) ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    // 3. Most frequently sold products in date range (Delivered only)
    $sql3 = "SELECT P.PName, SUM(A.Quantity) AS TotalSold
             FROM PRODUCT P
             JOIN APPEARS_IN A ON P.PID = A.PID
             JOIN TRANSACTION T ON A.BID = T.BID
             WHERE T.TTag = 'Delivered' AND T.TDate BETWEEN '$start_date' AND '$end_date'
             GROUP BY P.PID
             ORDER BY TotalSold DESC";
    $result3 = $conn->query($sql3);
    ?>
    <h4>Most Frequently Sold Products (<?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?>)</h4>
    <table class="table table-bordered">
        <thead><tr><th>Product</th><th>Units Sold</th></tr></thead>
        <tbody>
        <?php while ($row = $result3->fetch_assoc()): ?>
            <tr><td><?= htmlspecialchars($row['PName']) ?></td><td><?= htmlspecialchars($row['TotalSold']) ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    // 4. Products sold to highest number of distinct customers (Delivered only)
    $sql4 = "SELECT P.PName, COUNT(DISTINCT T.CID) AS CustomerCount
             FROM PRODUCT P
             JOIN APPEARS_IN A ON P.PID = A.PID
             JOIN TRANSACTION T ON A.BID = T.BID
             WHERE T.TTag = 'Delivered' AND T.TDate BETWEEN '$start_date' AND '$end_date'
             GROUP BY P.PID
             ORDER BY CustomerCount DESC";
    $result4 = $conn->query($sql4);
    ?>
    <h4>Products Sold to Highest Number of Distinct Customers (<?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?>)</h4>
    <table class="table table-bordered">
        <thead><tr><th>Product</th><th>Number of Customers</th></tr></thead>
        <tbody>
        <?php while ($row = $result4->fetch_assoc()): ?>
            <tr><td><?= htmlspecialchars($row['PName']) ?></td><td><?= htmlspecialchars($row['CustomerCount']) ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    // 5. Maximum basket total amount per credit card (Delivered only)
    $sql5 = "SELECT T.CCNumber, MAX(BasketTotal) AS MaxBasketTotal
             FROM (
                 SELECT T.BID, T.CCNumber, SUM(A.PriceSold) AS BasketTotal
                 FROM TRANSACTION T
                 JOIN APPEARS_IN A ON T.BID = A.BID
                 WHERE T.TTag = 'Delivered' AND T.TDate BETWEEN '$start_date' AND '$end_date'
                 GROUP BY T.BID, T.CCNumber
             ) AS BasketSums
             JOIN TRANSACTION T ON BasketSums.BID = T.BID
             GROUP BY T.CCNumber";
    $result5 = $conn->query($sql5);
    ?>
    <h4>Maximum Basket Total Amount per Credit Card (<?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?>)</h4>
    <table class="table table-bordered">
        <thead><tr><th>Credit Card</th><th>Max Basket Total</th></tr></thead>
        <tbody>
        <?php while ($row = $result5->fetch_assoc()): ?>
            <tr><td>**** **** **** <?= htmlspecialchars(substr($row['CCNumber'], -4)) ?></td><td>$<?= number_format($row['MaxBasketTotal'], 2) ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <?php
    // 6. Average selling product price per product type (Delivered only)
    $sql6 = "SELECT P.PType, AVG(A.PriceSold) AS AvgPrice
             FROM PRODUCT P
             JOIN APPEARS_IN A ON P.PID = A.PID
             JOIN TRANSACTION T ON A.BID = T.BID
             WHERE T.TTag = 'Delivered' AND T.TDate BETWEEN '$start_date' AND '$end_date'
             GROUP BY P.PType";
    $result6 = $conn->query($sql6);
    ?>
    <h4>Average Selling Product Price per Type (<?= htmlspecialchars($start_date) ?> to <?= htmlspecialchars($end_date) ?>)</h4>
    <table class="table table-bordered">
        <thead><tr><th>Product Type</th><th>Average Price</th></tr></thead>
        <tbody>
        <?php while ($row = $result6->fetch_assoc()): ?>
            <tr><td><?= htmlspecialchars($row['PType']) ?></td><td>$<?= number_format($row['AvgPrice'], 2) ?></td></tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <div class="mt-4">
        <a href="admin_dashboard.php" class="btn btn-secondary">⬅️ Back to Dashboard</a>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
