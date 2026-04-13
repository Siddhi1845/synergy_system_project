<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Products List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <?php if (isset($_GET['msg'])): ?>
    <?php if ($_GET['msg'] == 'deleted'): ?>
      <div class="alert alert-success">✅ Product deleted successfully!</div>
    <?php elseif ($_GET['msg'] == 'updated'): ?>
      <div class="alert alert-success">✅ Product updated successfully!</div>
    <?php elseif ($_GET['msg'] == 'notfound'): ?>
      <div class="alert alert-warning">⚠️ Product not found.</div>
    <?php elseif ($_GET['msg'] == 'error'): ?>
      <div class="alert alert-danger">❌ Error performing action.</div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="card shadow">
    <div class="card-body">
      <h4 class="mb-4">📦 Products List</h4>
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Total Qty</th>
            <th>Sold Qty</th>
            <th>Remaining</th>
            <th>Type</th>
            <th>Company</th>
            <th>Model</th>
            <th>Warranty</th>
            <th>Servicing Warranty</th>
            <th>Service Frequency</th>
            <th>Created At</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['product_id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['category']) ?></td>
            <td><?= $row['total_qty'] ?></td>
            <td><?= $row['sold_qty'] ?></td>
            <td><?= $row['remaining_qty'] ?></td>
            <td><?= htmlspecialchars($row['type']) ?></td>
            <td><?= htmlspecialchars($row['company']) ?></td>
            <td><?= htmlspecialchars($row['model_no']) ?></td>
            <td><?= $row['warranty'] ?> months</td>
            <td><?= $row['servicing_warranty'] ?> months</td>
            <td><?= $row['frequency_service'] ?> months</td>
            <td><?= $row['created_at'] ?></td>
            <td>
              <a href="edit_product.php?id=<?= $row['product_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
              <a href="delete_product.php?id=<?= $row['product_id'] ?>" 
                 onclick="return confirm('Are you sure you want to delete this product?');" 
                 class="btn btn-danger btn-sm">Delete</a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
      <a href="add_product.php" class="btn btn-success">➕ Add Product</a>
    </div>
  </div>
</div>
</body>
</html>
