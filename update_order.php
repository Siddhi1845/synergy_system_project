<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// ✅ Only employees allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

// ✅ Validate order ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: view_orders.php");
    exit();
}
$order_id = intval($_GET['id']);

// ✅ Helper: check if column exists
function col_exists($conn, $table, $col) {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '".$conn->real_escape_string($col)."'");
    return ($res && $res->num_rows > 0);
}

$has_service_duration = col_exists($conn, 'orders', 'service_duration');
$has_service_end_date = col_exists($conn, 'orders', 'service_end_date');

// ✅ Handle update form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_status = strtolower(trim($_POST['status']));
    $service_duration = $has_service_duration ? intval($_POST['service_duration']) : null;
    $service_end_date = ($has_service_end_date && !empty($_POST['service_end_date'])) ? $_POST['service_end_date'] : null;

    // Begin transaction
    $conn->begin_transaction();

    try {
        // 🔹 Fetch old order info first
        $fetch = $conn->prepare("SELECT product_id, quantity, status, customer_id FROM orders WHERE order_id = ?");
        $fetch->bind_param("i", $order_id);
        $fetch->execute();
        $old_order = $fetch->get_result()->fetch_assoc();
        $fetch->close();

        if (!$old_order) {
            throw new Exception("Order not found.");
        }

        $product_id = (int)$old_order['product_id'];
        $quantity   = (int)$old_order['quantity'];
        $old_status = strtolower(trim($old_order['status']));
        $customer_id = (int)$old_order['customer_id'];

        // 🔹 Undo old stock effect if not cancelled
        if ($old_status !== 'cancelled') {
            dec_product_stock_on_order($conn, $product_id, $quantity);
        }

        // 🔹 Build dynamic SQL for order update
        $sql = "UPDATE orders SET status = ?";
        $types = "s";
        $params = [$new_status];

        if ($has_service_duration) {
            $sql .= ", service_duration = ?";
            $types .= "i";
            $params[] = $service_duration;
        }
        if ($has_service_end_date) {
            $sql .= ", service_end_date = ?";
            $types .= "s";
            $params[] = $service_end_date;
        }

        $sql .= " WHERE order_id = ?";
        $types .= "i";
        $params[] = $order_id;

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Update prepare failed: " . $conn->error);
        }
        $stmt->bind_param($types, ...$params);
        if (!$stmt->execute()) {
            throw new Exception("Update failed: " . $stmt->error);
        }
        $stmt->close();

        // 🔹 Apply new stock effect if not cancelled
        if ($new_status !== 'cancelled') {
            inc_product_stock_on_order($conn, $product_id, $quantity);
        }

        // 🔹 Add in-app notification for the customer
        $notif_msg = "Your order #{$order_id} status has been updated to " . ucfirst($new_status) . ".";
        $notif_stmt = $conn->prepare("INSERT INTO notifications (user_type, user_id, message) VALUES ('customer', ?, ?)");
        if ($notif_stmt) {
            $notif_stmt->bind_param("is", $customer_id, $notif_msg);
            $notif_stmt->execute();
            $notif_stmt->close();
        }

        // 🔹 Commit transaction
        $conn->commit();

        header("Location: view_orders.php?msg=✅+Order+updated+successfully");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        $err = urlencode("❌ Update failed: " . $e->getMessage());
        header("Location: view_orders.php?msg=$err");
        exit();
    }
}

// ✅ Fetch order details for form
$select = [
    "o.order_id", "o.status", "o.quantity", "o.price",
    "c.first_name", "c.last_name", "p.name AS product_name"
];
if ($has_service_duration) $select[] = "o.service_duration";
if ($has_service_end_date) $select[] = "o.service_end_date";

$select_sql = implode(", ", $select);

$sql = "SELECT $select_sql
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        JOIN products p ON o.product_id = p.product_id
        WHERE o.order_id = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();
} else {
    die("Error preparing order fetch: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Order</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2>✏️ Update Order #<?= htmlspecialchars($order['order_id']) ?></h2>

  <div class="card p-4 shadow-sm mt-3">
    <p><strong>Customer:</strong> <?= htmlspecialchars($order['first_name']." ".$order['last_name']) ?></p>
    <p><strong>Product:</strong> <?= htmlspecialchars($order['product_name']) ?></p>
    <p><strong>Quantity:</strong> <?= (int)$order['quantity'] ?></p>
    <p><strong>Unit Price:</strong> ₹<?= number_format($order['price'], 2) ?></p>
    <p><strong>Total:</strong> ₹<?= number_format($order['quantity'] * $order['price'], 2) ?></p>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-control" required>
          <option value="pending"   <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
          <option value="approved"  <?= $order['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
          <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
          <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
        </select>
      </div>

      <?php if ($has_service_duration): ?>
      <div class="mb-3">
        <label class="form-label">Service Duration (months)</label>
        <input type="number" name="service_duration" class="form-control" min="0" 
               value="<?= htmlspecialchars($order['service_duration'] ?? 0) ?>">
      </div>
      <?php endif; ?>

      <?php if ($has_service_end_date): ?>
      <div class="mb-3">
        <label class="form-label">Service Expiry Date</label>
        <input type="date" name="service_end_date" class="form-control" 
               value="<?= htmlspecialchars($order['service_end_date'] ?? '') ?>">
      </div>
      <?php endif; ?>

      <button type="submit" class="btn btn-primary">Update Order</button>
      <a href="view_orders.php" class="btn btn-secondary">⬅ Back</a>
    </form>
  </div>
</div>
</body>
</html>
