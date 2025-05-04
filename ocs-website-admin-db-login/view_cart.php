
<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credit_card']) && isset($_POST['shipping_address'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once 'db.php';

    $cid = $_SESSION['cid'];
    $creditCard = $_POST['credit_card'];
    $shippingAddress = $_POST['shipping_address'];

    // 1. Get latest basket
    $result = $conn->query("SELECT BID FROM BASKET WHERE CID = $cid ORDER BY BID DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $bid = $result->fetch_assoc()['BID'];

        // 2. Deduct stock from PRODUCT table
        $items = $conn->query("SELECT PID, Quantity FROM APPEARS_IN WHERE BID = $bid");
        while ($item = $items->fetch_assoc()) {
            $pid = $item['PID'];
            $qty = $item['Quantity'];
            $conn->query("UPDATE PRODUCT SET PQuantity = PQuantity - $qty WHERE PID = $pid");
        }

        // 3. Insert transaction
        $conn->query("INSERT INTO TRANSACTION (BID, CID, SAName, CCNumber, TDate, TTag)
                      VALUES ('$bid', '$cid', '$shippingAddress', '$creditCard', NOW(), 'In-Progress')");

        // 4. Redirect to success page
        header("Location: checkout.php");
        exit;

    } else {
        echo "<div class='alert alert-warning'>No active basket found.</div>";
        exit;
    }
}
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['credit_card']) && isset($_POST['shipping_address'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    require_once 'db.php';

    $cid = $_SESSION['cid'];
    $creditCard = $_POST['credit_card'];
    $shippingAddress = $_POST['shipping_address'];

    // 1. Get latest basket
    $result = $conn->query("SELECT BID FROM BASKET WHERE CID = $cid ORDER BY BID DESC LIMIT 1");
    if ($result && $result->num_rows > 0) {
        $bid = $result->fetch_assoc()['BID'];

        // 2. Deduct stock from PRODUCT table
        $items = $conn->query("SELECT PID, Quantity FROM APPEARS_IN WHERE BID = $bid");
        while ($item = $items->fetch_assoc()) {
            $pid = $item['PID'];
            $qty = $item['Quantity'];
            $conn->query("UPDATE PRODUCT SET PQuantity = PQuantity - $qty WHERE PID = $pid");
        }

        // 3. Insert transaction
        $conn->query("INSERT INTO TRANSACTION (BID, CID, SAName, CCNumber, TDate, TTag)
                      VALUES ('$bid', '$cid', '$shippingAddress', '$creditCard', NOW(), 'completed')");

        // 4. Redirect to success page
        header("Location: checkout.php");
        exit;

    } else {
        echo "<div class='alert alert-warning'>No active basket found.</div>";
        exit;
    }
}
?>

<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

if (!isset($_SESSION['cid'])) {
  echo "<div class='alert alert-warning'>You must be logged in to view your cart.</div>";
  echo "<a href='login.php' class='btn btn-primary'>Login</a>";
  exit;
}

$cid = $_SESSION['cid'];
$result = $conn->query("SELECT BID FROM BASKET WHERE CID = $cid ORDER BY BID DESC LIMIT 1");
if ($result->num_rows === 0) {
  echo "<div class='alert alert-info'>Your cart is currently empty.</div>";
  echo "<a href='catalog.php' class='btn btn-secondary'>Browse Products</a>";
  exit;
}
$bid = $result->fetch_assoc()['BID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pid']) && isset($_POST['PQuantity'])) {
  $pid = intval($_POST['pid']);
  $quantity = intval($_POST['PQuantity']);

  $price_result = $conn->query("SELECT PPrice FROM PRODUCT WHERE PID = $pid");
  $price_row = $price_result->fetch_assoc();

  if ($price_row) {
    $price = floatval($price_row['PPrice']);
    $priceSold = $price * $quantity;

    $conn->query("UPDATE APPEARS_IN 
                  SET Quantity = $quantity, PriceSold = $priceSold 
                  WHERE BID = $bid AND PID = $pid");
  }

  echo json_encode(['success' => true]);
  exit;
}

if (isset($_GET['clear']) && $_GET['clear'] == "yes") {
  $conn->query("DELETE FROM APPEARS_IN WHERE BID = $bid");
  header("Location: view_cart.php");
  exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>My Cart</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-5">
  <h3>🛒 My Cart (Basket ID: <?php echo $bid; ?>)</h3>
  <table class="table table-bordered">
    <thead>
      <tr><th>Product Name</th><th>Price</th><th>Quantity</th><th>Total</th><th>Actions</th></tr>
    </thead>
    <tbody>
<?php
$items = $conn->query("SELECT P.PID, P.PName, A.PriceSold, A.Quantity FROM APPEARS_IN A JOIN PRODUCT P ON A.PID = P.PID WHERE A.BID = $bid");
// Stock validation
$errors = [];
$items->data_seek(0); // rewind pointer
while ($row = $items->fetch_assoc()) {
    $pid = $row['PID'];
    $cart_qty = $row['Quantity'];

    // Live check
    $stockResult = $conn->query("SELECT PQuantity FROM PRODUCT WHERE PID = $pid");
    if ($stockRow = $stockResult->fetch_assoc()) {
        $available = $stockRow['PQuantity'];

        if ($cart_qty > $available) {
            $errors[] = "⚠️ Product '{$row['PName']}' has only $available left in stock!";
        }
    }
}
$items->data_seek(0); // rewind again before displaying cart table

$total = 0;
while ($row = $items->fetch_assoc()) {
  $line_total = $row['PriceSold'] * $row['Quantity'];
  $total += $line_total;
  echo "<tr>
          <td>{$row['PName']}</td>
          <td>\${$row['PriceSold']}</td>
          <td>
            <input type='number' class='form-control quantity-input' data-pid='{$row['PID']}' data-price='{$row['PriceSold']}' value='{$row['Quantity']}' min='1' style='width:100px;'>
          </td>
          <td class='line-total' id='total_{$row['PID']}'>\$" . number_format($line_total, 2) . "</td>
          <td><a href='add_to_cart.php?remove={$row['PID']}&bid=$bid' class='btn btn-sm btn-danger'>Remove</a></td>
        </tr>";
}
?>
    </tbody>
    <tfoot>
      <tr><th colspan="3">Total</th><th id="grand-total" colspan="2">$<?php echo number_format($total, 2); ?></th></tr>
    </tfoot>
  </table>

  <div class="d-flex justify-content-between">
    <div>
      <a href="catalog.php" class="btn btn-primary">Continue Shopping</a>
      
    </div>
    <div>
      <a href="view_cart.php?clear=yes" class="btn btn-outline-danger">🗑 Clear Cart</a>
    </div>
  </div>
  <div class="mt-4">
    <a href="index.php" class="btn btn-secondary">⬅️ Back to Home</a>
  </div>

  <script>
    function updateLineTotal(pid, price, quantity) {
      const lineTotal = price * quantity;
      document.getElementById("total_" + pid).innerText = "$" + lineTotal.toFixed(2);
      updateGrandTotal();
    }

    function updateGrandTotal() {
  let sum = 0;
  document.querySelectorAll('.line-total').forEach(el => {
    const clean = el.innerText.replace(/[^0-9\.\-]+/g, '');
    sum += parseFloat(clean) || 0;
  });
  document.getElementById('grand-total').innerText = '$' + sum.toFixed(2);
}

document.querySelectorAll(".quantity-input").forEach(input => {
  input.addEventListener("change", () => {
    const pid = input.getAttribute("data-pid");
    const price = parseFloat(input.getAttribute("data-price"));
    const quantity = parseInt(input.value);
    updateLineTotal(pid, price, quantity);

    const formData = new FormData();
    formData.append("pid", pid);
    formData.append("quantity", quantity);

    fetch("view_cart.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.text()) // <-- this line parses the response
    .then(() => {
      setTimeout(() => {
        window.location.reload();
      }, 500); // reload after 0.5 seconds
    });
  });
});

    updateGrandTotal();
  </script>

<script>
document.querySelectorAll(".quantity-input").forEach(input => {
  input.addEventListener("change", () => {
    const pid = input.getAttribute("data-pid");
    const quantity = input.value;

    fetch("update_quantity.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: `pid=${pid}&quantity=${quantity}`
    }).then(res => res.text()).then(console.log);
  });
});
document.addEventListener('DOMContentLoaded', function() {
    const checkoutButton = document.querySelector('form[action="checkout.php"] button[type="submit"]');
    const creditCardSelect = document.getElementById('credit_card');
    const shippingAddressSelect = document.getElementById('shipping_address');

    function validateCheckout() {
        const creditCardValid = creditCardSelect.options.length > 0 && !creditCardSelect.options[0].disabled;
        const shippingAddressValid = shippingAddressSelect.options.length > 0 && !shippingAddressSelect.options[0].disabled;

        if (creditCardValid && shippingAddressValid &&
            creditCardSelect.value && shippingAddressSelect.value) {
            checkoutButton.disabled = false;
            checkoutButton.classList.remove('btn-secondary');
            checkoutButton.classList.add('btn-success');
            checkoutButton.textContent = '✅ Checkout';
        } else {
            checkoutButton.disabled = true;
            checkoutButton.classList.remove('btn-success');
            checkoutButton.classList.add('btn-secondary');
            checkoutButton.textContent = '❌ Cannot Checkout';
        }
    }

    creditCardSelect.addEventListener('change', validateCheckout);
    shippingAddressSelect.addEventListener('change', validateCheckout);

    // Initial check when page loads
    validateCheckout();
});
</script>

</body>
</html>



<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <?php foreach ($errors as $error): ?>
            <div><?= $error ?></div>
        <?php endforeach; ?>
    </div>

    <!-- Show disabled checkout form (greyed out) -->
    <form action="#" method="POST" class="mt-5 w-50">
        <div class="mb-3">
            <label for="credit_card" class="form-label">💳 Select Credit Card:</label>
            <select class="form-select" disabled>
                <option disabled selected>Unavailable due to stock issue</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="shipping_address" class="form-label">📦 Select Shipping Address:</label>
            <select class="form-select" disabled>
                <option disabled selected>Unavailable due to stock issue</option>
            </select>
        </div>

        <button type="button" class="btn btn-secondary" disabled>❌ Cannot Checkout</button>
    </form>

<?php else: ?>
    <form action="checkout.php" method="POST" class="mt-5 w-50">
        <div class="mb-3">
            <label for="credit_card" class="form-label">💳 Select Credit Card:</label>
            <select name="credit_card" id="credit_card" class="form-select" required>
                <?php
                $result = $conn->query("SELECT CCNumber FROM CREDIT_CARD WHERE StoredCardCID = '$cid'");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $masked = str_repeat("*", 12) . substr($row['CCNumber'], -4);
                        echo "<option value='{$row['CCNumber']}'>{$masked}</option>";
                    }
                } else {
                    echo "<option disabled selected>No cards found</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="shipping_address" class="form-label">📦 Select Shipping Address:</label>
            <select name="shipping_address" id="shipping_address" class="form-select" required>
                <?php
                $result = $conn->query("SELECT SAName FROM SHIPPING_ADDRESS WHERE CID = '$cid'");
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['SAName']}'>{$row['SAName']}</option>";
                    }
                } else {
                    echo "<option disabled selected>No addresses found</option>";
                }
                ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">✅ Checkout</button>
    </form>
<?php endif; ?>
