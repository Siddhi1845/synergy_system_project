<?php
session_start();
include 'db.php';

// check customer login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// fetch all products
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Available Products</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .container { margin-top: 40px; }
    .table-hover tbody tr:hover { background: #eef4ff; }
    .btn-sm { margin-right: 5px; }
  </style>
</head>
<body>
<div class="container">
  <h2 class="mb-4">Available Products</h2>

  <div class="table-responsive">
    <table class="table table-bordered table-hover bg-white">
      <thead class="table-success">
        <tr>
          <th>ID</th>
          <th>Type</th>
          <th>Company</th>
          <th>Name</th>
          <th>Model No</th>
          <th>Warranty (Months)</th>
          <th>Servicing Warranty (Months)</th>
          <th>Frequency of Service (Months)</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id'] ?></td>
              <td><?= htmlspecialchars($row['type']) ?></td>
              <td><?= htmlspecialchars($row['company']) ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['model_no']) ?></td>
              <td><?= htmlspecialchars($row['warranty_months']) ?></td>
              <td><?= htmlspecialchars($row['servicing_warranty_months']) ?></td>
              <td><?= htmlspecialchars($row['frequency_service_months']) ?></td>
              <td>
                <a href="request_service.php?product_id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Request Service</a>
                <a href="order_product.php?product_id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">Order Now</a>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="9" class="text-center">No products available</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
