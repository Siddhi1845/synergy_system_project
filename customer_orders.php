<?php
session_start();
include 'db.php';

// ✅ Only logged-in customers
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? "Customer";
$message = "";

// ✅ Handle new order (only when form is submitted)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity   = intval($_POST['quantity']);

    // Fetch product (price + stock)
    $stmt = $conn->prepare("SELECT product_id, name, remaining_qty, price FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($product && $product['remaining_qty'] >= $quantity) {
        // Service period (example: 1 year)
        $service_start  = date('Y-m-d');
        $service_expiry = date('Y-m-d', strtotime("+1 year"));

        // Lock price at order time
        $price_per_unit = (float)($product['price'] ?? 0);

        $insert_sql = "INSERT INTO orders 
                       (customer_id, product_id, quantity, price, order_date, service_start, service_expiry, status) 
                       VALUES (?, ?, ?, ?, NOW(), ?, ?, 'pending')";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iiidss", $customer_id, $product_id, $quantity, $price_per_unit, $service_start, $service_expiry);

        if ($stmt->execute()) {
            // ✅ Redirect prevents duplicate insert on refresh
            header("Location: customer_orders.php?msg=success");
            exit();
        } else {
            header("Location: customer_orders.php?msg=error");
            exit();
        }
    } else {
        header("Location: customer_orders.php?msg=nostock");
        exit();
    }
}

// ✅ Show message if redirected
if (isset($_GET['msg'])) {
    if ($_GET['msg'] === 'success') $message = "✅ Order placed successfully!";
    elseif ($_GET['msg'] === 'error') $message = "❌ Error placing order.";
    elseif ($_GET['msg'] === 'nostock') $message = "⚠️ Not enough stock available!";
}

// ✅ Fetch all products for dropdown
$products = $conn->query("SELECT * FROM products WHERE remaining_qty > 0");

// ✅ Fetch customer's orders
$order_stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.status, o.quantity, o.price, 
                                     o.service_start, o.service_expiry,
                                     p.name AS product_name
                              FROM orders o
                              JOIN products p ON o.product_id = p.product_id
                              WHERE o.customer_id = ?
                              ORDER BY o.order_date DESC");
$order_stmt->bind_param("i", $customer_id);
$order_stmt->execute();
$orders = $order_stmt->get_result();
$order_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Orders</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .badge { font-size: 0.85rem; }
  </style>
</head>
<body>
<div class="container mt-5">

  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🛒 Welcome <?= htmlspecialchars($customer_name) ?> - Place & View Orders</h2>
    <a href="customer_dashboard.php" class="btn btn-secondary">⬅ Dashboard</a>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <!-- Place New Order -->
  <div class="card p-4 shadow-sm mb-5">
    <h4>➕ Place New Order</h4>
    <form method="POST">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Choose Product</label>
          <select name="product_id" class="form-control" required>
            <option value="">-- Select Product --</option>
            <?php while ($p = $products->fetch_assoc()): ?>
              <option value="<?= $p['product_id'] ?>">
                <?= htmlspecialchars($p['name']) ?>
                (Available: <?= $p['remaining_qty'] ?> | Price: ₹<?= number_format($p['price'], 2) ?>)
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label">Quantity</label>
          <input type="number" name="quantity" class="form-control" min="1" required>
        </div>
        <div class="col-md-3 mb-3 d-flex align-items-end">
          <button type="submit" class="btn btn-success w-100">Place Order</button>
        </div>
      </div>
    </form>
  </div>

  <!-- Orders Table -->
  <div class="card p-4 shadow-sm">
    <h4>📋 Your Orders</h4>
    <div class="table-responsive mt-3">
      <table class="table table-bordered table-striped bg-white">
        <thead class="table-dark">
          <tr>
            <th>Order ID</th>
            <th>Product</th>
            <th>Quantity</th>
            <th>Unit Price</th>
            <th>Total</th>
            <th>Service Start</th>
            <th>Service Expiry</th>
            <th>Status</th>
            <th>Order Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($orders->num_rows > 0): ?>
            <?php while ($row = $orders->fetch_assoc()): ?>
              <?php
                $status = strtolower($row['status']);
                switch ($status) {
                  case 'pending':   $status_class = 'bg-warning text-dark'; break;
                  case 'approved':  $status_class = 'bg-info text-dark'; break;
                  case 'delivered': $status_class = 'bg-success'; break;
                  case 'cancelled': $status_class = 'bg-danger'; break;
                  default:          $status_class = 'bg-secondary'; break;
                }
                $unit_price = (float)$row['price'];
                $total = $row['quantity'] * $unit_price;
              ?>
              <tr>
                <td><?= $row['order_id'] ?></td>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td><?= $row['quantity'] ?></td>
                <td>₹<?= number_format($unit_price, 2) ?></td>
                <td>₹<?= number_format($total, 2) ?></td>
                <td><?= $row['service_start'] ?? '—' ?></td>
                <td><?= $row['service_expiry'] ?? '—' ?></td>
                <td><span class="badge <?= $status_class ?>"><?= ucfirst($status) ?></span></td>
                <td><?= $row['order_date'] ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="9" class="text-center">No orders found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
