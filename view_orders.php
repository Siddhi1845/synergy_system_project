<?php
session_start();
include 'db.php';

// ✅ Only employees allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

// helper: does column exist?
function col_exists($conn, $table, $col) {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '".$conn->real_escape_string($col)."'");
    return ($res && $res->num_rows > 0);
}

// Build SELECT list dynamically
$select = [
    "o.order_id", "o.order_date", "o.status", "o.quantity", "o.price",
    "c.first_name", "c.last_name", "p.name AS product_name"
];

if (col_exists($conn, 'orders', 'service_duration')) {
    $select[] = "o.service_duration";
}
if (col_exists($conn, 'orders', 'service_end_date')) {
    $select[] = "o.service_end_date";
}
if (col_exists($conn, 'orders', 'service_start')) {
    $select[] = "o.service_start";
}
if (col_exists($conn, 'orders', 'service_expiry')) {
    $select[] = "o.service_expiry";
}

$select_sql = implode(", ", $select);

$sql = "SELECT $select_sql
        FROM orders o
        JOIN customers c ON o.customer_id = c.customer_id
        JOIN products p ON o.product_id = p.product_id
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
if (!$result) {
    die("SQL Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Orders - Employee</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style> 
    body { background:#f8f9fa; } 
    .badge { font-size:0.85rem; } 
  </style>
</head>
<body>
<div class="container mt-5">
  <h2>📋 Manage Customer Orders</h2>

  <?php if (isset($_GET['msg']) && $_GET['msg'] === 'updated'): ?>
    <div class="alert alert-success mt-3">✅ Order updated successfully!</div>
  <?php endif; ?>

  <div class="table-responsive mt-4">
    <table class="table table-bordered table-striped bg-white">
      <thead class="table-dark">
        <tr>
          <th>Order ID</th>
          <th>Customer</th>
          <th>Product</th>
          <th>Quantity</th>
          <th>Unit Price</th>
          <th>Total</th>
          <th>Date</th>
          <th>Service</th>
          <th>Expiry Date</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
<?php if ($result->num_rows > 0): ?>
  <?php while ($row = $result->fetch_assoc()): ?>
    <?php
      // ✅ Status badge
      $status = strtolower($row['status'] ?? '');
      switch ($status) {
        case 'pending':   $status_class = 'bg-warning text-dark'; break;
        case 'approved':  $status_class = 'bg-info text-dark'; break;
        case 'delivered': $status_class = 'bg-success'; break;
        case 'cancelled': $status_class = 'bg-danger'; break;
        default:          $status_class = 'bg-secondary'; break;
      }

      // ✅ Pricing
      $price_per_unit = (float)($row['price'] ?? 0);
      $total = ($row['quantity'] ?? 0) * $price_per_unit;

      // ✅ Service duration & expiry
      $display_duration = '—';
      $display_expiry   = '—';

      if (array_key_exists('service_duration', $row) && !is_null($row['service_duration']) && $row['service_duration'] !== '') {
          $display_duration = (int)$row['service_duration'] . ' months';
      } elseif (array_key_exists('service_start', $row) && array_key_exists('service_expiry', $row) 
                && !empty($row['service_start']) && !empty($row['service_expiry'])) {
          try {
              $d1 = new DateTime($row['service_start']);
              $d2 = new DateTime($row['service_expiry']);
              $interval = $d1->diff($d2);
              $months = ($interval->y * 12) + $interval->m;
              $display_duration = ($months > 0 ? $months . ' months' : '—');
          } catch (Exception $e) {
              $display_duration = '—';
          }
      }

      if (array_key_exists('service_end_date', $row) && !empty($row['service_end_date'])) {
          $display_expiry = $row['service_end_date'];
      } elseif (array_key_exists('service_expiry', $row) && !empty($row['service_expiry'])) {
          $display_expiry = $row['service_expiry'];
      }
    ?>
    <tr>
      <td><?= htmlspecialchars($row['order_id']) ?></td>
      <td><?= htmlspecialchars(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? '')) ?></td>
      <td><?= htmlspecialchars($row['product_name'] ?? '—') ?></td>
      <td><?= htmlspecialchars($row['quantity'] ?? 0) ?></td>
      <td>₹<?= number_format($price_per_unit, 2) ?></td>
      <td>₹<?= number_format($total, 2) ?></td>
      <td><?= htmlspecialchars($row['order_date'] ?? '—') ?></td>
      <td><?= $display_duration ?></td>
      <td><?= $display_expiry ?></td>
      <td><span class="badge <?= $status_class ?>"><?= ucfirst($status) ?></span></td>
      <td>
        <a href="update_order.php?id=<?= urlencode($row['order_id']) ?>" class="btn btn-warning btn-sm">Update</a>
        <a href="delete_order.php?id=<?= urlencode($row['order_id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this order?')">Delete</a>
      </td>
    </tr>
  <?php endwhile; ?>
<?php else: ?>
  <tr><td colspan="11" class="text-center">No orders found.</td></tr>
<?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
