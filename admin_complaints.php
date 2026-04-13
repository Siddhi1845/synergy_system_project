<?php
session_start();
include 'db.php';

// Ensure only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all complaints
$sql = "SELECT c.complaint_id, c.complaint_text, c.status, c.remarks, c.created_at,
               cu.first_name AS customer_name, e.first_name AS employee_name
        FROM complaints c
        LEFT JOIN customers cu ON c.customer_id = cu.customer_id
        LEFT JOIN employee_details e ON c.assigned_employee = e.employee_id
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin - Complaints</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2 class="mb-4">📋 All Complaints (Admin View)</h2>

  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-primary">
        <tr>
          <th>ID</th>
          <th>Customer</th>
          <th>Complaint</th>
          <th>Status</th>
          <th>Assigned Employee</th>
          <th>Remarks</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['complaint_id']; ?></td>
            <td><?= htmlspecialchars($row['customer_name']); ?></td>
            <td><?= htmlspecialchars($row['complaint_text']); ?></td>
            <td>
              <?php if ($row['status'] == 'Pending'): ?>
                <span class="badge bg-warning">Pending</span>
              <?php elseif ($row['status'] == 'In Progress'): ?>
                <span class="badge bg-info text-dark">In Progress</span>
              <?php else: ?>
                <span class="badge bg-success">Resolved</span>
              <?php endif; ?>
            </td>
            <td><?= $row['employee_name'] ?: 'Not Assigned'; ?></td>
            <td><?= htmlspecialchars($row['remarks']); ?></td>
            <td><?= $row['created_at']; ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="7" class="text-center">No complaints found</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
</div>
</body>
</html>
