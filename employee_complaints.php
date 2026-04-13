<?php
session_start();
include 'db.php';

// ✅ Only employees allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$message = "";

// Handle update (status/remarks)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['complaint_id'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $status = $_POST['status'];
    $remarks = trim($_POST['remarks']);

    $sql = "UPDATE complaints SET status = ?, remarks = ?, updated_at = NOW() WHERE complaint_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) die("SQL error: ".$conn->error);

    $stmt->bind_param("ssi", $status, $remarks, $complaint_id);
    if ($stmt->execute()) {
        $message = "✅ Complaint updated successfully!";
    } else {
        $message = "❌ Error: ".$stmt->error;
    }
    $stmt->close();
}

// Fetch all complaints with order & customer info
$sql = "SELECT c.complaint_id, c.complaint_text, c.status, c.remarks, c.created_at, c.updated_at,
               o.order_id, o.order_date,
               cu.first_name, cu.last_name, cu.email
        FROM complaints c
        LEFT JOIN orders o ON c.order_id = o.order_id
        LEFT JOIN customers cu ON c.customer_id = cu.customer_id
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Complaints</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2>📢 Manage Customer Complaints</h2>
  <a href="employee_dashboard.php" class="btn btn-secondary mb-3">⬅ Dashboard</a>

  <?php if ($message): ?>
    <div class="alert alert-info"><?= $message ?></div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-bordered table-striped bg-white">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Order</th>
          <th>Complaint</th>
          <th>Status</th>
          <th>Remarks</th>
          <th>Created</th>
          <th>Updated</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): ?>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['complaint_id'] ?></td>
              <td><?= htmlspecialchars($row['first_name']." ".$row['last_name']) ?><br>
                  <small><?= htmlspecialchars($row['email']) ?></small>
              </td>
              <td>#<?= $row['order_id'] ?> (<?= $row['order_date'] ?>)</td>
              <td><?= htmlspecialchars($row['complaint_text']) ?></td>
              <td><?= ucfirst($row['status']) ?></td>
              <td><?= $row['remarks'] ?: '—' ?></td>
              <td><?= $row['created_at'] ?></td>
              <td><?= $row['updated_at'] ?: '—' ?></td>
              <td>
                <!-- Update Form -->
                <form method="POST" class="d-flex flex-column gap-1">
                  <input type="hidden" name="complaint_id" value="<?= $row['complaint_id'] ?>">
                  <select name="status" class="form-select form-select-sm">
                    <option value="pending"     <?= $row['status']=='pending'?'selected':'' ?>>Pending</option>
                    <option value="in-progress" <?= $row['status']=='in-progress'?'selected':'' ?>>In-Progress</option>
                    <option value="resolved"    <?= $row['status']=='resolved'?'selected':'' ?>>Resolved</option>
                    <option value="closed"      <?= $row['status']=='closed'?'selected':'' ?>>Closed</option>
                  </select>
                  <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Add remarks"><?= $row['remarks'] ?></textarea>
                  <button type="submit" class="btn btn-sm btn-primary mt-1">Update</button>
                </form>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <tr><td colspan="9" class="text-center">No complaints found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
