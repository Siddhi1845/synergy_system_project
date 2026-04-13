<?php 
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

// Fetch customers for dropdown
$customers = $conn->query("SELECT customer_id, first_name, last_name FROM customers");

// Fetch products for dropdown
$products = $conn->query("SELECT product_id, name, company, model_no, remaining_qty, price FROM products WHERE remaining_qty > 0");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $customer_id = intval($_POST['customer_id']);
    $product_id  = intval($_POST['product_id']);
    $quantity    = intval($_POST['quantity']);
    $order_date  = $_POST['order_date'] ?? date('Y-m-d');

    // Fetch product info (price + stock)
    $stmt = $conn->prepare("SELECT price, remaining_qty FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$product) {
        die("❌ Product not found!");
    }

    if ($product['remaining_qty'] < $quantity) {
        echo "<script>alert('⚠️ Not enough stock available!'); window.location='add_order.php';</script>";
        exit();
    }

    $price_per_unit = (float)$product['price'];
    $service_start  = date('Y-m-d');
    $service_expiry = date('Y-m-d', strtotime("+1 year"));

    // Default status for new orders
    $status = 'pending';

    // Begin transaction to ensure insert + stock update are atomic
    $conn->begin_transaction();
    try {
        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders 
            (customer_id, product_id, quantity, price, order_date, service_start, service_expiry, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        // bind: i i i d s s s s
        $stmt->bind_param("iiidssss", $customer_id, $product_id, $quantity, $price_per_unit, $order_date, $service_start, $service_expiry, $status);

        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        $stmt->close();

        // Update stock only if order status is not 'cancelled'
        if ($status !== 'cancelled') {
            inc_product_stock_on_order($conn, (int)$product_id, (int)$quantity);
        }

        // Commit transaction
        $conn->commit();

        echo "<script>alert('✅ Order added successfully!'); window.location='view_orders.php';</script>";
        exit();
    } catch (Exception $e) {
        // Rollback and show error
        $conn->rollback();
        // Log error in activity_logs or error_log (recommended). For now show friendly message.
        $err = htmlspecialchars($e->getMessage());
        echo "<script>alert('❌ Error adding order: {$err}'); window.location='add_order.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Order</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2>➕ Add Order (Employee)</h2>
  <form method="POST">
    <div class="mb-3">
      <label>Customer</label>
      <select name="customer_id" class="form-control" required>
        <option value="">-- Select Customer --</option>
        <?php while($row = $customers->fetch_assoc()): ?>
          <option value="<?= $row['customer_id']; ?>">
            <?= htmlspecialchars($row['first_name'] . " " . $row['last_name']); ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Product</label>
      <select name="product_id" class="form-control" required>
        <option value="">-- Select Product --</option>
        <?php while($p = $products->fetch_assoc()): ?>
          <option value="<?= $p['product_id']; ?>">
            <?= htmlspecialchars($p['name']) ?> - <?= htmlspecialchars($p['company']) ?> <?= htmlspecialchars($p['model_no']) ?>
            (Available: <?= $p['remaining_qty'] ?> | Price: ₹<?= number_format($p['price'], 2) ?>)
          </option>
        <?php endwhile; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Quantity</label>
      <input type="number" name="quantity" class="form-control" min="1" required>
    </div>
    <div class="mb-3">
      <label>Order Date</label>
      <input type="date" name="order_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
    </div>
    <button type="submit" class="btn btn-primary">Save Order</button>
    <a href="view_orders.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
