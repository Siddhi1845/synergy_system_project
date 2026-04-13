<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Activity Logs</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .container { margin-top: 40px; }
    .table-hover tbody tr:hover { background-color: #eef4ff; }
  </style>
</head>
<body>
  <div class="container">
    <h2>📝 Activity Logs</h2>
    <p class="text-muted">All admin activities are tracked here.</p>

    <div class="table-responsive">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-primary">
          <tr>
            <th>#</th>
            <th>Action</th>
            <th>Performed By</th>
            <th>Date & Time</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $sql = "SELECT * FROM activity_logs ORDER BY created_at DESC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          $i = 1;
          while ($row = $result->fetch_assoc()) {
            echo "<tr>
              <td>{$i}</td>
              <td>{$row['action']}</td>
              <td>{$row['performed_by']}</td>
              <td>{$row['created_at']}</td>
            </tr>";
            $i++;
          }
        } else {
          echo "<tr><td colspan='4' class='text-center'>No activity logs found</td></tr>";
        }
        ?>
        </tbody>
      </table>
    </div>

    <a href="admin_dashboard.php" class="btn btn-secondary mt-3">⬅ Back to Dashboard</a>
  </div>
</body>
</html>
