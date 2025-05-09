<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin'])) {
    echo "<div class='alert alert-warning text-center mt-5'>Access denied. Please login as Admin.</div>";
    exit;
}

// Handle fetching attributes for Modal (for More Option)
if (isset($_POST['fetch_attributes']) && isset($_POST['pid']) && isset($_POST['ptype'])) {
    $pid = intval($_POST['pid']);
    $ptype = $_POST['ptype'];

    if ($ptype == 'Computer') {
        $query = "SELECT * FROM COMPUTER WHERE PID = $pid";
    } elseif ($ptype == 'Laptop') {
        $query = "SELECT C.CPUType, L.Weight, L.Btype FROM COMPUTER C JOIN LAPTOP L ON C.PID = L.PID WHERE C.PID = $pid";
    } elseif ($ptype == 'Printer') {
        $query = "SELECT * FROM PRINTER WHERE PID = $pid";
    } else {
        echo "<div class='alert alert-danger'>No Additional Details</div>";
        exit;
    }

    $result = $conn->query($query);

    if ($result && $row = $result->fetch_assoc()) {
        echo "<table class='table table-bordered'>";
        foreach ($row as $key => $value) {
            echo "<tr><th>" . htmlspecialchars($key) . "</th><td>" . htmlspecialchars($value) . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<div class='alert alert-warning'>No additional details found.</div>";
    }
    exit;
}

// Product Add/Edit/Delete Handling
$edit_mode = false;
$edit_product = null;
$edit_extra = null;


if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['fetch_attributes'])) {
    $pid = (int)$_POST['pid'];
    $ptype = $_POST['ptype'];
    $pname = $_POST['pname'];
    $pprice = (float)$_POST['pprice'];
    if (isset($_POST['OfferPrice']) && $_POST['OfferPrice'] !== '') {
        $OfferPrice = (float)$_POST['OfferPrice'];
    } else {
        $OfferPrice = "NULL";
    }
    $description = $_POST['description'];
    $pquantity = (int)$_POST['pquantity'];

    if (isset($_POST['add_product'])) {

        $conn->query("INSERT INTO PRODUCT (PID, PType, PName, PPrice, Description, PQuantity)
                      VALUES ($pid, '$ptype', '$pname', $pprice, '$description', $pquantity)");
        if (isset($_POST['OfferPrice']) && $_POST['OfferPrice'] !== '') {
            $OfferPrice = (float)$_POST['OfferPrice'];
            $conn->query("INSERT INTO OFFER_PRODUCT (PID, OfferPrice) VALUES ($pid, $OfferPrice)");
        }
        

        // Insert into respective table
        if ($ptype == 'Computer') {
            $CPUType = $_POST['CPUType_Computer'];
            $conn->query("INSERT INTO COMPUTER (PID, CPUType) VALUES ($pid, '$CPUType')");
        } elseif ($ptype == 'Laptop') {
            $CPUType = $_POST['CPUType_Laptop'];
            $Weight = $_POST['Weight'];
            $Btype = $_POST['Btype'];
            $conn->query("INSERT INTO COMPUTER (PID, CPUType) VALUES ($pid, '$CPUType')");
            $conn->query("INSERT INTO LAPTOP (PID, Weight, Btype) VALUES ($pid, '$Weight', '$Btype')");
        } elseif ($ptype == 'Printer') {
            $printer_type = $_POST['printer_type'];
            $resolution = $_POST['resolution'];
            $conn->query("INSERT INTO PRINTER (PID, PrinterType, resolution) VALUES ($pid, '$printer_type', '$resolution')");
        }


    } elseif (isset($_POST['update_product'])) {

    $conn->query("UPDATE PRODUCT SET PType='$ptype', PName='$pname', PPrice=$pprice, Description='$description', PQuantity=$pquantity WHERE PID=$pid");
    if (isset($_POST['OfferPrice']) && $_POST['OfferPrice'] !== '') {
    $OfferPrice = (float)$_POST['OfferPrice'];

    $offer_check = $conn->query("SELECT * FROM OFFER_PRODUCT WHERE PID = $pid");
    if ($offer_check && $offer_check->num_rows > 0) {
        $conn->query("UPDATE OFFER_PRODUCT SET OfferPrice = $OfferPrice WHERE PID = $pid");
    } else {
        $conn->query("INSERT INTO OFFER_PRODUCT (PID, OfferPrice) VALUES ($pid, $OfferPrice)");
    }
} else {
    $conn->query("DELETE FROM OFFER_PRODUCT WHERE PID = $pid");
}


    // 🛠 First remove from all extra tables
    $conn->query("DELETE FROM LAPTOP WHERE PID = $pid");
    $conn->query("DELETE FROM COMPUTER WHERE PID = $pid");
    $conn->query("DELETE FROM PRINTER WHERE PID = $pid");

        if ($ptype == 'Computer') {
            $CPUType = $_POST['CPUType_Computer'];
            $conn->query("INSERT INTO COMPUTER (PID, CPUType) VALUES ($pid, '$CPUType')");
        } elseif ($ptype == 'Laptop') {
            $CPUType = $_POST['CPUType_Laptop'];
            $Weight = $_POST['Weight'];
            $Btype = $_POST['Btype'];
            $conn->query("INSERT INTO COMPUTER (PID, CPUType) VALUES ($pid, '$CPUType')");
            $conn->query("INSERT INTO LAPTOP (PID, Weight, Btype) VALUES ($pid, '$Weight', '$Btype')");
        } elseif ($ptype == 'Printer') {
            $printer_type = $_POST['printer_type'];
            $resolution = $_POST['resolution'];
            $conn->query("INSERT INTO PRINTER (PID, PrinterType, resolution) VALUES ($pid, '$printer_type', '$resolution')");
        }
    }

    header("Location: admin_product.php");
    exit;
}

if (isset($_GET['edit'])) {
    $edit_pid = (int)$_GET['edit'];

    // First fetch main product info
    $edit_product = $conn->query("SELECT P.*, O.OfferPrice FROM PRODUCT P LEFT JOIN OFFER_PRODUCT O ON P.PID = O.PID WHERE P.PID = $edit_pid")->fetch_assoc();

    if ($edit_product) {
        $edit_mode = true;

        // Now depending on Product Type (PType), fetch extra info
        if ($edit_product['PType'] == 'Computer') {
            $edit_extra = $conn->query("SELECT * FROM COMPUTER WHERE PID = $edit_pid")->fetch_assoc();
        } elseif ($edit_product['PType'] == 'Laptop') {
            $edit_extra = $conn->query("SELECT L.Weight, L.Btype, C.CPUType 
                                        FROM LAPTOP L 
                                        JOIN COMPUTER C ON L.PID = C.PID 
                                        WHERE L.PID = $edit_pid")->fetch_assoc();
        } elseif ($edit_product['PType'] == 'Printer') {
            $edit_extra = $conn->query("SELECT * FROM PRINTER WHERE PID = $edit_pid")->fetch_assoc();
        } else {
            $edit_extra = null; // No extra fields for unknown types
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_pid = (int)$_GET['delete'];

    // Delete dependent product type details
    $conn->query("DELETE FROM LAPTOP WHERE PID = $delete_pid");
    $conn->query("DELETE FROM COMPUTER WHERE PID = $delete_pid");
    $conn->query("DELETE FROM PRINTER WHERE PID = $delete_pid");

    // Delete offer entry
    $conn->query("DELETE FROM OFFER_PRODUCT WHERE PID = $delete_pid");

    // Delete from APPEARS_IN (linked to basket)
    $conn->query("DELETE FROM APPEARS_IN WHERE PID = $delete_pid");

    // Finally, delete from PRODUCT
    $conn->query("DELETE FROM PRODUCT WHERE PID = $delete_pid");

    header("Location: admin_product.php");
    exit;
}


// Fetch Products
// Fetch Products - Dynamic SQL
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? trim($_GET['filter']) : '';
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : '';

$sql = "SELECT P.*, O.OfferPrice FROM PRODUCT P LEFT JOIN OFFER_PRODUCT O ON P.PID = O.PID WHERE 1 ";

if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $sql .= "AND (P.PID LIKE '%$search_safe%' OR P.PType LIKE '%$search_safe%' OR P.PName LIKE '%$search_safe%' OR P.Description LIKE '%$search_safe%') ";
}

if (!empty($filter)) {
    $filter_safe = $conn->real_escape_string($filter);
    $sql .= "AND P.PType = '$filter_safe' ";
}

if ($sort === 'asc') {
    $sql .= "ORDER BY P.PPrice ASC";
} elseif ($sort === 'desc') {
    $sql .= "ORDER BY P.PPrice DESC";
} else {
    $sql .= "ORDER BY P.PID ASC";
}

$products = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Products</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2><?= $edit_mode ? 'Edit' : 'Add' ?> Product</h2>
    <form method="POST" class="bg-light p-4 rounded mb-5">
        <div class="row g-3">
            
            <div class="col-md-4">
                <input type="number" name="pid" class="form-control" placeholder="Product ID (PID)"
                    value="<?= $edit_mode ? htmlspecialchars($edit_product['PID']) : '' ?>"
                    <?= $edit_mode ? 'readonly' : '' ?> required>
            </div>

            <div class="col-md-4">
            <select name="ptype" id="ptype" class="form-select" onchange="showExtraFields()" required>
                <option value="">Select Product Type</option>
                <option value="Laptop" <?= ($edit_mode && $edit_product['PType'] === 'Laptop') ? 'selected' : '' ?>>Laptop</option>
                <option value="Computer" <?= ($edit_mode && $edit_product['PType'] === 'Computer') ? 'selected' : '' ?>>Computer</option>
                <option value="Printer" <?= ($edit_mode && $edit_product['PType'] === 'Printer') ? 'selected' : '' ?>>Printer</option>
                <option value="Other" <?= ($edit_mode && $edit_product['PType'] === 'Other') ? 'selected' : '' ?>>Other</option> <!-- ✅ New added -->
            </select>

            </div>

            <div class="col-md-4">
                <input type="text" name="pname" class="form-control" placeholder="Product Name (PName)"
                    value="<?= $edit_mode ? htmlspecialchars($edit_product['PName']) : '' ?>" required>
            </div>

            <div class="col-md-4">
                <input type="number" step="0.01" name="pprice" class="form-control" placeholder="Price (PPrice)"
                    value="<?= $edit_mode ? htmlspecialchars($edit_product['PPrice']) : '' ?>" required>
            </div>

            <div class="col-md-4">
                <input type="number" step="0.01" name="OfferPrice" class="form-control" placeholder="Offer Price"
                    value="<?= $edit_mode ? htmlspecialchars($edit_product['OfferPrice']) : '' ?>">
            </div>

            <div class="col-md-4">
                <input type="number" name="pquantity" class="form-control" placeholder="Quantity (PQuantity)"
                    value="<?= $edit_mode ? htmlspecialchars($edit_product['PQuantity']) : '' ?>" required>
            </div>

            <div class="col-md-12">
                <input type="text" name="description" class="form-control" placeholder="Description"
                    value="<?= $edit_mode ? htmlspecialchars($edit_product['Description']) : '' ?>" required>
            </div>

            <!-- Dynamic Fields for Computer -->
            <div id="computerFields" style="display:none;">
                <div class="col-md-4">
                    <input type="text" name="CPUType_Computer" class="form-control" placeholder="CPU-Type-Computer"
                        value="<?= ($edit_mode && $edit_extra && isset($edit_extra['CPUType'])) ? htmlspecialchars($edit_extra['CPUType']) : ''?>">
                </div>
            </div>

            <!-- Dynamic Fields for Laptop -->
            <div id="laptopFields" style="display:none;">
                <div class="col-md-4">
                    <input type="text" name="CPUType_Laptop" class="form-control" placeholder="CPU-Type-Laptop"
                        value="<?= ($edit_mode && $edit_extra && isset($edit_extra['CPUType'])) ? htmlspecialchars($edit_extra['CPUType']) : '' ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" name="Weight" class="form-control" placeholder="Weight"
                        value="<?= ($edit_mode && $edit_extra && isset($edit_extra['Weight'])) ? htmlspecialchars($edit_extra['Weight']) : '' ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" name="Btype" class="form-control" placeholder="Battery Type"
                        value="<?= ($edit_mode && $edit_extra && isset($edit_extra['Btype'])) ? htmlspecialchars($edit_extra['Btype']) : '' ?>">
                </div>
            </div>

            <!-- Dynamic Fields for Printer -->
            <div id="printerFields" style="display:none;">
                <div class="col-md-4">
                    <input type="text" name="printer_type" class="form-control" placeholder="Printer Type"
                        value="<?= ($edit_mode && $edit_extra && isset($edit_extra['PrinterType'])) ? htmlspecialchars($edit_extra['PrinterType']) : '' ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" name="resolution" class="form-control" placeholder="Resolution"
                        value="<?= ($edit_mode && $edit_extra && isset($edit_extra['resolution'])) ? htmlspecialchars($edit_extra['resolution']) : '' ?>">
                </div>
            </div>

            <div class="col-12">
                <button type="submit" name="<?= $edit_mode ? 'update_product' : 'add_product' ?>" class="btn btn-success">
                    <?= $edit_mode ? 'Update' : 'Add' ?> Product
                </button>
                <a href="admin_product.php" class="btn btn-secondary ms-2">Cancel</a>
                <a href="admin_dashboard.php" class="btn btn-secondary ms-2">Back To Dashboard</a>
            </div>

        </div>
    </form>
</div>


    <h3>📦 All Products</h3>
    <!-- 🔍 Search + 🎯 Filter + 🔃 Sort Form -->
<form method="GET" class="row g-3 mb-4">

<div class="col-md-4">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" class="form-control" placeholder="Search by PID, PName, PType, Description">
</div>

<div class="col-md-3">
    <select name="filter" class="form-select">
        <option value="">Filter by Type</option>
        <option value="Laptop" <?= $filter == 'Laptop' ? 'selected' : '' ?>>Laptop</option>
        <option value="Computer" <?= $filter == 'Computer' ? 'selected' : '' ?>>Computer</option>
        <option value="Printer" <?= $filter == 'Printer' ? 'selected' : '' ?>>Printer</option>
        <option value="Other" <?= $filter == 'Other' ? 'selected' : '' ?>>Other</option>
    </select>
</div>

<div class="col-md-3">
    <select name="sort" class="form-select">
        <option value="">Sort by Price</option>
        <option value="asc" <?= $sort == 'asc' ? 'selected' : '' ?>>Price Low to High</option>
        <option value="desc" <?= $sort == 'desc' ? 'selected' : '' ?>>Price High to Low</option>
    </select>
</div>

<div class="col-md-2">
    <button type="submit" class="btn btn-primary w-100">Apply</button>
</div>

</form>

    <?php if ($products && $products->num_rows > 0): ?>
    <table class="table table-striped table-bordered align-middle">
        <thead class="table-dark">
            <tr>
                <th>PID</th>
                <th>PType</th>
                <th>PName</th>
                <th>PPrice</th>
                <th>Description</th>
                <th>PQuantity</th>
                <th>OfferPrice</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($product['PID']) ?></td>
                    <td><?= htmlspecialchars($product['PType']) ?></td>
                    <td><?= htmlspecialchars($product['PName']) ?></td>
                    <td><?= htmlspecialchars($product['PPrice']) ?></td>
                    <td><?= htmlspecialchars($product['Description']) ?></td>
                    <td><?= htmlspecialchars($product['PQuantity']) ?></td>
                    <td><?= htmlspecialchars($product['OfferPrice']) ?></td>
                    <td>
                        <a href="admin_product.php?edit=<?= $product['PID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="admin_product.php?delete=<?= $product['PID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        <button class="btn btn-info btn-sm" onclick="loadMoreDetails(<?= $product['PID'] ?>, '<?= $product['PType'] ?>')">More</button>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-warning">No products found.</div>
    <?php endif; ?>
</div>

<!-- Modal for More Option -->
<div class="modal fade" id="moreOptionModal" tabindex="-1" aria-labelledby="moreOptionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="moreOptionModalLabel">More Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="moreOptionContent"></div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showExtraFields() {
    var ptype = document.getElementById('ptype').value;

    document.getElementById('computerFields').style.display = (ptype === 'Computer') ? 'block' : 'none';
    document.getElementById('laptopFields').style.display = (ptype === 'Laptop') ? 'block' : 'none';
    document.getElementById('printerFields').style.display = (ptype === 'Printer') ? 'block' : 'none';
}

// ✅ Auto trigger when page loads
window.onload = function() {
    showExtraFields();
};

function loadMoreDetails(pid, ptype) {
    document.getElementById('moreOptionContent').innerHTML = '<div class="text-center">Loading...</div>';
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "", true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function() {
        if (xhr.status === 200) {
            document.getElementById('moreOptionContent').innerHTML = xhr.responseText;
            var myModal = new bootstrap.Modal(document.getElementById('moreOptionModal'));
            myModal.show();
        } else {
            document.getElementById('moreOptionContent').innerHTML = '<div class="text-danger">Error loading details.</div>';
        }
    };
    xhr.send("fetch_attributes=1&pid=" + pid + "&ptype=" + ptype);
}
</script>
</body>
</html>