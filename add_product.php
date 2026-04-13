<?php
session_start();
include 'db.php';

// Only employees allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $category = $_POST['category'];
    $total_qty = (int)$_POST['total_qty'];
    $sold_qty = 0;
    $remaining_qty = $total_qty;

    $type = $_POST['type'];
    $company = $_POST['company'];
    $model_no = $_POST['model_no'];
    $warranty = (int)$_POST['warranty'];
    $servicing_warranty = (int)$_POST['servicing_warranty'];
    $frequency_service = (int)$_POST['frequency_service'];
    $price = (float)$_POST['price'];

    $sql = "INSERT INTO products
        (name, category, total_qty, sold_qty, remaining_qty, created_at, 
         type, company, model_no, warranty, servicing_warranty, frequency_service, price)
        VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("❌ SQL Prepare Error: " . $conn->error);
    }

    $stmt->bind_param("ssiiisssiiid", 
        $name, $category, $total_qty, $sold_qty, $remaining_qty,
        $type, $company, $model_no, $warranty, $servicing_warranty, 
        $frequency_service, $price
    );

    if ($stmt->execute()) {
        $message = "✅ Product added successfully!";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .card { border-radius: 12px; }
        .alert { border-radius: 8px; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-body">
                    <h4 class="mb-4">➕ Add New Product</h4>
                    <?php if ($message): ?>
                        <div class="alert alert-info"><?= $message ?></div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Product Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Category</label>
                                <input type="text" name="category" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Total Quantity</label>
                                <input type="number" name="total_qty" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Type</label>
                                <input type="text" name="type" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Company</label>
                                <input type="text" name="company" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Model No</label>
                                <input type="text" name="model_no" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Warranty (months)</label>
                                <input type="number" name="warranty" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Servicing Warranty (months)</label>
                                <input type="number" name="servicing_warranty" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Service Frequency (months)</label>
                                <input type="number" name="frequency_service" class="form-control">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Price (₹)</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success">Save</button>
                        <a href="view_products.php" class="btn btn-secondary">Back</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
