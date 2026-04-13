<?php
session_start();
include 'db.php';

// Check if employee is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

// Handle complaint status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complaint_id'])) {
    $complaint_id = intval($_POST['complaint_id']);
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $stmt = $conn->prepare("UPDATE complaints SET status=?, remarks=? WHERE complaint_id=?");
    $stmt->bind_param("ssi", $status, $remarks, $complaint_id);
    if ($stmt->execute()) {
        echo "<script>alert('Complaint updated successfully!'); window.location='manage_complaints.php';</script>";
    } else {
        echo "Error updating complaint: " . $stmt->error;
    }
    $stmt->close();
}

// Fetch complaints
$sql = "SELECT c.complaint_id, c.complaint_text, c.status, c.remarks, c.created_at,
               cu.first_name, cu.last_name, cu.email, cu.mobile
        FROM complaints c
        JOIN customers cu ON c.customer_id = cu.customer_id
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Complaints</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
    .sidebar { height: 100vh; background: #198754; color: #fff; padding: 20px; position: fixed; width: 240px; }
    .sidebar h4 { color: #fff; }
    .sidebar a { color: #fff; text-decoration: none; display: block; margin: 12px 0; font-weight: 500; }
    .sidebar a:hover { text-decoration: underline; }
    .content { margin-left: 260px; padding: 30px; }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <h4>Employee Panel</h4>
    <p>Welcome, <?php echo $_SESSION['username']; ?> 👋</p>
    <a href="employee_dashboard.php">🏠 Dashboard</a>
    <a href="manage_complaints.php">⚡ Manage Complaints</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <!-- Content -->
  <div class="content">
    <h2>Manage Complaints</h2>

    <div class="table-responsive mt-4">
      <table class="table table-bordered table-striped table-hover">
        <thead class="table-success">
          <tr>
            <th>ID</th>
            <th>Customer</th>
            <th>Contact</th>
            <th>Complaint</th>
            <th>Status</th>
            <th>Remarks</th>
            <th>Created At</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                  echo "<tr>
                    <td>{$row['complaint_id']}</td>
                    <td>{$row['first_name']} {$row['last_name']}</td>
                    <td>Email: {$row['email']}<br>Mobile: {$row['mobile']}</td>
                    <td>{$row['complaint_text']}</td>
                    <td>{$row['status']}</td>
                    <td>{$row['remarks']}</td>
                    <td>{$row['created_at']}</td>
                    <td>
                      <form method='POST' class='d-flex flex-column'>
                        <input type='hidden' name='complaint_id' value='{$row['complaint_id']}'>
                        <select name='status' class='form-select mb-2' required>
                          <option value='Pending' " . ($row['status'] == 'Pending' ? 'selected' : '') . ">Pending</option>
                          <option value='In Progress' " . ($row['status'] == 'In Progress' ? 'selected' : '') . ">In Progress</option>
                          <option value='Resolved' " . ($row['status'] == 'Resolved' ? 'selected' : '') . ">Resolved</option>
                        </select>
                        <textarea name='remarks' class='form-control mb-2' placeholder='Add remarks...'>{$row['remarks']}</textarea>
                        <button type='submit' class='btn btn-sm btn-success'>Update</button>
                      </form>
                    </td>
                  </tr>";
              }
          } else {
              echo "<tr><td colspan='8' class='text-center'>No complaints found</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
