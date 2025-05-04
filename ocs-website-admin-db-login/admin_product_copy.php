<?php
session_start();
require_once 'db.php';

$edit_mode = false;
$edit_product = null;
$edit_extra_computer = null;
$edit_extra_laptop = null;
$edit_extra_printer = null;

// Fetch for "More" modal
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



// Form handling
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['fetch_attributes'])) {
    $pid = (int)$_POST['pid'];
    $ptype = $_POST['ptype'];
    $pname = $_POST['pname'];
    $pprice = (float)$_POST['pprice'];
    $description = $_POST['description'];
    $pquantity = (int)$_POST['pquantity'];
    $OfferPrice = isset($_POST['OfferPrice']) && $_POST['OfferPrice'] !== '' ? (float)$_POST['OfferPrice'] : null;

    if (isset($_POST['add_product'])) {
        $conn->query("INSERT INTO PRODUCT (PID, PType, PName, PPrice, Description, PQuantity) VALUES ($pid, '$ptype', '$pname', $pprice, '$description', $pquantity)");

        if (!is_null($OfferPrice)) {
            $conn->query("INSERT INTO OFFER_PRODUCT (PID, OfferPrice) VALUES ($pid, $OfferPrice)");
        }

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

        if (!is_null($OfferPrice)) {
            $offer_check = $conn->query("SELECT * FROM OFFER_PRODUCT WHERE PID = $pid");
            if ($offer_check && $offer_check->num_rows > 0) {
                $conn->query("UPDATE OFFER_PRODUCT SET OfferPrice = $OfferPrice WHERE PID = $pid");
            } else {
                $conn->query("INSERT INTO OFFER_PRODUCT (PID, OfferPrice) VALUES ($pid, $OfferPrice)");
            }
        } else {
            $conn->query("DELETE FROM OFFER_PRODUCT WHERE PID = $pid");
        }

        $conn->query("DELETE FROM COMPUTER WHERE PID = $pid");
        $conn->query("DELETE FROM LAPTOP WHERE PID = $pid");
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
    header("Location: admin_product_copy.php");
    exit;
}

if (isset($_GET['edit'])) {
    $edit_pid = (int)$_GET['edit'];
    $edit_product = $conn->query("SELECT P.*, O.OfferPrice FROM PRODUCT P LEFT JOIN OFFER_PRODUCT O ON P.PID = O.PID WHERE P.PID = $edit_pid")->fetch_assoc();

    if ($edit_product) {
        $edit_mode = true;
        if ($edit_product['PType'] == 'Computer' || $edit_product['PType'] == 'Laptop') {
            $edit_extra_computer = $conn->query("SELECT * FROM COMPUTER WHERE PID = $edit_pid")->fetch_assoc();
        }
        if ($edit_product['PType'] == 'Laptop') {
            $edit_extra_laptop = $conn->query("SELECT * FROM LAPTOP WHERE PID = $edit_pid")->fetch_assoc();
        }
        if ($edit_product['PType'] == 'Printer') {
            $edit_extra_printer = $conn->query("SELECT * FROM PRINTER WHERE PID = $edit_pid")->fetch_assoc();
        }
    }
}

if (isset($_GET['delete'])) {
    $delete_pid = (int)$_GET['delete'];
    $conn->query("DELETE FROM COMPUTER WHERE PID = $delete_pid");
    $conn->query("DELETE FROM LAPTOP WHERE PID = $delete_pid");
    $conn->query("DELETE FROM PRINTER WHERE PID = $delete_pid");
    $conn->query("DELETE FROM OFFER_PRODUCT WHERE PID = $delete_pid");
    $conn->query("DELETE FROM PRODUCT WHERE PID = $delete_pid");
    header("Location: admin_product_copy.php");
    exit;
}

$products = $conn->query("SELECT P.*, O.OfferPrice FROM PRODUCT P LEFT JOIN OFFER_PRODUCT O ON P.PID = O.PID ORDER BY P.PID");

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
                    <option value="Other" <?= ($edit_mode && $edit_product['PType'] === 'Other') ? 'selected' : '' ?>>Other</option>
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
                    value="<?= $edit_mode && isset($edit_product['OfferPrice']) ? htmlspecialchars($edit_product['OfferPrice']) : '' ?>">
            </div>

            <div class="col-md-4">
                <input type="number" name="pquantity" class="form-control" placeholder="Quantity (PQuantity)"
                    value="<?= $edit_mode ? htmlspecialchars($edit_product['PQuantity']) : '' ?>" required>
            </div>

            <div class="col-md-12">
                <input type="text" name="description" class="form-control" placeholder="Description"
                    value="<?= $edit_mode ? htmlspecialchars($edit_product['Description']) : '' ?>" required>
            </div>

            <!-- Dynamic Fields -->
            <div id="computerFields" style="display:none;">
                <div class="col-md-4">
                    <input type="text" name="CPUType_Computer" class="form-control" placeholder="CPU Type (Computer)"
                        value="<?= ($edit_mode && $edit_extra_computer && isset($edit_extra_computer['CPUType'])) ? htmlspecialchars($edit_extra_computer['CPUType']) : '' ?>">
                </div>
            </div>

            <div id="laptopFields" style="display:none;">
                <div class="col-md-4">
                    <input type="text" name="CPUType_Laptop" class="form-control" placeholder="CPU Type (Laptop)"
                        value="<?= ($edit_mode && $edit_extra_computer && isset($edit_extra_computer['CPUType'])) ? htmlspecialchars($edit_extra_computer['CPUType']) : '' ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" name="Weight" class="form-control" placeholder="Weight"
                        value="<?= ($edit_mode && $edit_extra_laptop && isset($edit_extra_laptop['Weight'])) ? htmlspecialchars($edit_extra_laptop['Weight']) : '' ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" name="Btype" class="form-control" placeholder="Battery Type"
                        value="<?= ($edit_mode && $edit_extra_laptop && isset($edit_extra_laptop['Btype'])) ? htmlspecialchars($edit_extra_laptop['Btype']) : '' ?>">
                </div>
            </div>

            <div id="printerFields" style="display:none;">
                <div class="col-md-4">
                    <input type="text" name="printer_type" class="form-control" placeholder="Printer Type"
                        value="<?= ($edit_mode && $edit_extra_printer && isset($edit_extra_printer['PrinterType'])) ? htmlspecialchars($edit_extra_printer['PrinterType']) : '' ?>">
                </div>
                <div class="col-md-4">
                    <input type="text" name="resolution" class="form-control" placeholder="Resolution"
                        value="<?= ($edit_mode && $edit_extra_printer && isset($edit_extra_printer['resolution'])) ? htmlspecialchars($edit_extra_printer['resolution']) : '' ?>">
                </div>
            </div>

            <div class="col-12">
                <button type="submit" name="<?= $edit_mode ? 'update_product' : 'add_product' ?>" class="btn btn-success">
                    <?= $edit_mode ? 'Update' : 'Add' ?> Product
                </button>
                <a href="admin_product_copy.php" class="btn btn-secondary ms-2">Cancel</a>
                <a href="admin_dashboard.php" class="btn btn-secondary ms-2">Back To Dashboard</a>
            </div>

        </div>
    </form>

    <!-- 📦 Display All Products Table -->
    <h3 class="mt-5">All Products</h3>
    <?php if ($products && $products->num_rows > 0): ?>
        <table class="table table-striped table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>PID</th>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Price</th>
                    <th>Description</th>
                    <th>Quantity</th>
                    <th>Offer Price</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $products->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['PID']) ?></td>
                    <td><?= htmlspecialchars($row['PType']) ?></td>
                    <td><?= htmlspecialchars($row['PName']) ?></td>
                    <td>$<?= htmlspecialchars($row['PPrice']) ?></td>
                    <td><?= htmlspecialchars($row['Description']) ?></td>
                    <td><?= htmlspecialchars($row['PQuantity']) ?></td>
                    <td><?= htmlspecialchars($row['OfferPrice']) ?></td>
                    <td>
                        <a href="admin_product_copy.php?edit=<?= $row['PID'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="admin_product_copy.php?delete=<?= $row['PID'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        <button class="btn btn-info btn-sm" onclick="loadMoreDetails(<?= $row['PID'] ?>, '<?= $row['PType'] ?>')">More</button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="alert alert-warning">No products found.</div>
    <?php endif; ?>
</div>

<!-- More Option Modal -->
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

<script>
function showExtraFields() {
    var ptype = document.getElementById('ptype').value;
    document.getElementById('computerFields').style.display = (ptype === 'Computer') ? 'block' : 'none';
    document.getElementById('laptopFields').style.display = (ptype === 'Laptop') ? 'block' : 'none';
    document.getElementById('printerFields').style.display = (ptype === 'Printer') ? 'block' : 'none';
}
window.onload = function() { showExtraFields(); };

function loadMoreDetails(pid, ptype) {
    document.getElementById('moreOptionContent').innerHTML = '<div class="text-center">Loading...</div>';
    const xhr = new XMLHttpRequest();
    xhr.open("POST", "admin_product_copy.php", true);  // <-- Very Important, post back to same page
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
    xhr.send("fetch_attributes=1&ajax_only=1&pid=" + pid + "&ptype=" + ptype);
}

</script>
</body>
</html>
