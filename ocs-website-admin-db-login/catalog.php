<?php
session_start();
require_once 'db.php';

// Handle Search
$search = '';
if (isset($_GET['search'])) {
    $search = trim($_GET['search']);
    $products = $conn->query("SELECT * FROM PRODUCT WHERE PName LIKE '%$search%' OR PType LIKE '%$search%' ORDER BY PID");
} else {
    $products = $conn->query("SELECT * FROM PRODUCT ORDER BY PID");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Catalog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="mb-4">📦 Product Catalog</h2>

    <!-- Search Form -->
    <form method="GET" class="mb-4 d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Search by Product Name or Type" value="<?= htmlspecialchars($search) ?>">
        <button type="submit" class="btn btn-primary">Search</button>
        <a href="catalog.php" class="btn btn-secondary ms-2">Reset</a>
    </form>

    <?php if ($products && $products->num_rows > 0): ?>
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th>PID</th>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Available</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($product = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($product['PID']) ?></td>
                    <td><?= htmlspecialchars($product['PType']) ?></td>
                    <td><?= htmlspecialchars($product['PName']) ?></td>
                    <td>$<?= htmlspecialchars($product['PPrice']) ?></td>
                    <td><?= htmlspecialchars($product['Description']) ?></td>
                    <td><?= htmlspecialchars($product['PQuantity']) ?></td>
                    <td>
                        <?php if (isset($_SESSION['cid'])): ?>
                            <?php if ($product['PQuantity'] > 0): ?>
                                <form method="POST" action="add_to_cart.php" class="d-flex">
                                    <input type="hidden" name="pid" value="<?= $product['PID'] ?>">
                                    <input type="number" name="qty" value="1" min="1" max="<?= $product['PQuantity'] ?>" class="form-control me-2" style="width: 80px;">
                                    <button type="submit" name="submit" class="btn btn-success">Add to Cart</button>
                                </form>
                            <?php else: ?>
                                <div class="badge bg-danger d-block text-center">Out of Stock</div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="text-muted text-center">Login to add to cart</div>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="7">
                        <div class="p-2 bg-light rounded">
                            <?php
                            $pid = $product['PID'];
                            $type = strtolower($product['PType']);

                            if ($type === 'printer') {
                                $details = $conn->query("SELECT * FROM PRINTER WHERE PID = $pid")->fetch_assoc();
                                if ($details) {
                                    echo "<strong>Printer Type:</strong> " . htmlspecialchars($details['PrinterType']) . "<br>";
                                    echo "<strong>Resolution:</strong> " . htmlspecialchars($details['Resolution']);
                                }
                            } elseif ($type === 'computer') {
                                $details = $conn->query("SELECT * FROM COMPUTER WHERE PID = $pid")->fetch_assoc();
                                if ($details) {
                                    echo "<strong>CPU Type:</strong> " . htmlspecialchars($details['CPUType']);
                                }
                            } elseif ($type === 'laptop') {
                                $details_laptop = $conn->query("SELECT * FROM LAPTOP WHERE PID = $pid")->fetch_assoc();
                                $details_computer = $conn->query("SELECT * FROM COMPUTER WHERE PID = $pid")->fetch_assoc();
                                if ($details_laptop && $details_computer) {
                                    echo "<strong>Battery Type:</strong> " . htmlspecialchars($details_laptop['BType']) . "<br>";
                                    echo "<strong>Weight:</strong> " . htmlspecialchars($details_laptop['Weight']) . "<br>";
                                    echo "<strong>CPU Type:</strong> " . htmlspecialchars($details_computer['CPUType']);
                                }
                            }
                            ?>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No products found.</div>
    <?php endif; ?>

    <a href="index.php" class="btn btn-primary mt-4">⬅️ Back to Home</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
