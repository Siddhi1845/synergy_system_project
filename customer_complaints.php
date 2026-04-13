<?php
session_start();
include 'db.php';

// ✅ Only logged-in customers
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$message = "";

// Handle complaint form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id']);
    $complaint_text = trim($_POST['complaint_text']);

    if ($order_id && $complaint_text !== "") {
        $sql = "INSERT INTO complaints (customer_id, order_id, complaint_text, status, created_at) 
                VALUES (?, ?, ?, 'pending', NOW())";
        $stmt = $conn->prepare($sql);

        if (!$stmt) {
            die("❌ SQL Error: " . $conn->error);
        }

        $stmt->bind_param("iis", $customer_id, $order_id, $complaint_text);

        if ($stmt->execute()) {
            $message = "✅ Complaint submitted successfully!";
        } else {
            $message = "❌ Failed to submit complaint: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $message = "⚠️ Please select an order and enter complaint details.";
    }
}

// Fetch orders of this customer
$order_sql = "SELECT order_id, order_date, status 
              FROM orders 
              WHERE customer_id = ?";
$order_stmt = $conn->prepare($order_sql);
$order_stmt->bind_param("i", $customer_id);
$order_stmt->execute();
$orders = $order_stmt->get_result();
$order_stmt->close();

// Fetch existing complaints
$complaint_sql = "SELECT c.complaint_id, c.complaint_text, c.status, c.remarks, c.created_at,
                         o.order_id, o.order_date
                  FROM complaints c
                  LEFT JOIN orders o ON c.order_id = o.order_id
                  WHERE c.customer_id = ?
                  ORDER BY c.created_at DESC";
$comp_stmt = $conn->prepare($complaint_sql);
$comp_stmt->bind_param("i", $customer_id);
$comp_stmt->execute();
$complaints = $comp_stmt->get_result();
$comp_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Complaints</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

  <h2>📢 My Complaints</h2>
  <a href="customer_dashboard.php" class="btn btn-secondary mb-3">⬅ Dashboard</a>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <!-- Complaint Form -->
  <div class="card p-4 shadow-sm mb-4">
    <h4>➕ Raise a Complaint</h4>
    <form method="POST">
      <div class="mb-3">
        <label for="order_id" class="form-label">Select Order</label>
        <select name="order_id" class="form-control" required>
          <option value="">-- Select an Order --</option>
          <?php while ($o = $orders->fetch_assoc()): ?>
            <option value="<?= $o['order_id'] ?>">
              Order #<?= $o['order_id'] ?> - <?= $o['order_date'] ?> (<?= ucfirst($o['status']) ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Complaint Details</label>
        <textarea name="complaint_text" class="form-control" rows="3" required></textarea>
      </div>
      <button type="submit" class="btn btn-danger">Submit Complaint</button>
    </form>
  </div>

  <!-- Complaints List -->
  <div class="card p-4 shadow-sm">
    <h4>📋 Your Complaints</h4>
    <div class="table-responsive mt-3">
      <table class="table table-bordered table-striped">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Order</th>
            <th>Complaint</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($complaints->num_rows > 0): ?>
            <?php while ($c = $complaints->fetch_assoc()): ?>
              <tr>
                <td><?= $c['complaint_id'] ?></td>
                <td>#<?= $c['order_id'] ?> (<?= $c['order_date'] ?>)</td>
                <td><?= htmlspecialchars($c['complaint_text']) ?></td>
                <td><?= ucfirst($c['status']) ?></td>
                <td><?= $c['remarks'] ?: '—' ?></td>
                <td><?= $c['created_at'] ?></td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center">No complaints found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>
</body>
</html>
