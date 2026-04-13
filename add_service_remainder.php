<?php
session_start();
include 'db.php';

// Allow only employees
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$message = "";

// ✅ Fetch customers (use customer_id + full name)
$customers = $conn->query("SELECT customer_id, CONCAT(first_name, ' ', last_name) AS full_name 
                           FROM customers ORDER BY first_name, last_name");

// ✅ Fetch products (use product_id + name)
$products = $conn->query("SELECT product_id, name FROM products ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customer_id   = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
    $product_id    = !empty($_POST['product_id']) ? intval($_POST['product_id']) : null;
    $product_name  = trim($_POST['product_name']) ?: null;
    $next_date     = $_POST['next_service_date'];
    $recurrence    = intval($_POST['recurrence_months']) ?: 3;
    $notes         = trim($_POST['notes']);

    $stmt = $conn->prepare("INSERT INTO service_reminders
        (customer_id, product_id, product_name, next_service_date, recurrence_months, notes)
        VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissis", $customer_id, $product_id, $product_name, $next_date, $recurrence, $notes);

    if ($stmt->execute()) {
        $message = "✅ Service reminder added successfully.";
    } else {
        $message = "❌ Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Add Service Reminder</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3>➕ Add Service Reminder</h3>
  <?php if ($message): ?><div class="alert alert-info"><?= htmlspecialchars($message) ?></div><?php endif; ?>
  
  <form method="post" class="card p-4 shadow-sm bg-white">
    <div class="row">
      <!-- Customer -->
      <div class="col-md-6 mb-3">
        <label>Customer</label>
        <select name="customer_id" class="form-select" required>
          <option value="">Select customer</option>
          <?php while ($c = $customers->fetch_assoc()): ?>
            <option value="<?= $c['customer_id'] ?>"><?= htmlspecialchars($c['full_name']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <!-- Product -->
      <div class="col-md-6 mb-3">
        <label>Product (select existing or enter new)</label>
        <div class="d-flex">
          <select name="product_id" class="form-select me-2">
            <option value="">-- Select product (optional) --</option>
            <?php while ($p = $products->fetch_assoc()): ?>
              <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
            <?php endwhile; ?>
          </select>
          <input name="product_name" class="form-control" placeholder="Or type product name">
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-4 mb-3">
        <label>Next Service Date</label>
        <input type="date" name="next_service_date" class="form-control" required>
      </div>
      <div class="col-md-4 mb-3">
        <label>Recurrence (months)</label>
        <input type="number" name="recurrence_months" class="form-control" value="3" min="0">
      </div>
      <div class="col-md-4 mb-3">
        <label>Notes</label>
        <input type="text" name="notes" class="form-control" placeholder="Optional notes">
      </div>
    </div>

    <div class="text-end">
      <a href="service_remainders.php" class="btn btn-secondary">Back</a>
      <button class="btn btn-primary">Add Reminder</button>
    </div>
  </form>
</div>
</body>
</html>
