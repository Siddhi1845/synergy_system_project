<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view_products.php?msg=notfound");
    exit();
}

$id = intval($_GET['id']);
$message = "";

// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: view_products.php?msg=notfound");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $total_qty = (int)$_POST['total_qty'];
    $sold_qty = (int)$_POST['sold_qty'];
    $remaining_qty = $total_qty - $sold_qty;

    $type = $_POST['type'];
    $company = $_POST['company'];
    $model_no = $_POST['model_no'];
    $warranty = (int)$_POST['warranty'];
    $servicing_warranty = (int)$_POST['servicing_warranty'];
    $frequency_service = (int)$_POST['frequency_service'];
    $price = (float)$_POST['price'];

    $sql = "UPDATE products 
            SET name=?, category=?, total_qty=?, sold_qty=?, remaining_qty=?, 
                type=?, company=?, model_no=?, warranty=?, servicing_warranty=?, frequency_service=?, price=? 
            WHERE product_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "ssiiisssiiidi",
        $name, $category, $total_qty, $sold_qty, $remaining_qty,
        $type, $company, $model_no, $warranty, $servicing_warranty,
        $frequency_service, $price, $id
    );

    if ($stmt->execute()) {
        header("Location: view_products.php?msg=updated");
        exit();
    } else {
        $message = "❌ Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Product</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow">
        <div class="card-body">
          <h4 class="mb-4">✏️ Edit Product</h4>
          <?php if ($message): ?>
            <div class="alert alert-danger"><?= $message ?></div>
          <?php endif; ?>
          <form method="POST">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label>Product Name</label>
                <input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" class="form-control" required>
              </div>
              <div class="col-md-6 mb-3">
                <label>Category</label>
                <input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" class="form-control" required>
              </div>
              <div class="col-md-4 mb-3">
                <label>Total Qty</label>
                <input type="number" name="total_qty" value="<?= $product['total_qty'] ?>" class="form-control" required>
              </div>
              <div class="col-md-4 mb-3">
                <label>Sold Qty</label>
                <input type="number" name="sold_qty" value="<?= $product['sold_qty'] ?>" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>Type</label>
                <input type="text" name="type" value="<?= htmlspecialchars($product['type']) ?>" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>Company</label>
                <input type="text" name="company" value="<?= htmlspecialchars($product['company']) ?>" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>Model No</label>
                <input type="text" name="model_no" value="<?= htmlspecialchars($product['model_no']) ?>" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>Warranty (months)</label>
                <input type="number" name="warranty" value="<?= $product['warranty'] ?>" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>Servicing Warranty (months)</label>
                <input type="number" name="servicing_warranty" value="<?= $product['servicing_warranty'] ?>" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>Service Frequency (months)</label>
                <input type="number" name="frequency_service" value="<?= $product['frequency_service'] ?>" class="form-control">
              </div>
              <div class="col-md-4 mb-3">
                <label>Price (₹)</label>
                <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" class="form-control" required>
              </div>
            </div>
            <button type="submit" class="btn btn-warning">Update</button>
            <a href="view_products.php" class="btn btn-secondary">Back</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
