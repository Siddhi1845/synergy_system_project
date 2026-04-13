<?php
session_start();
include 'db.php';

// Allow only employees
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

// Validate complaint id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid complaint ID");
}
$complaint_id = intval($_GET['id']);

// Fetch complaint details
$sql = "SELECT c.*, cu.name AS customer_name, cu.email AS customer_email, cu.mobile AS customer_mobile
        FROM complaints c
        LEFT JOIN customers cu ON c.customer_id = cu.id
        WHERE c.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $complaint_id);
$stmt->execute();
$result = $stmt->get_result();
$complaint = $result->fetch_assoc();

if (!$complaint) {
    die("Complaint not found");
}

$message = "";

// Handle update form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $status = $_POST['status'];
    $remarks = $_POST['remarks'];

    $update_sql = "UPDATE complaints 
                   SET status = ?, remarks = ?, updated_at = NOW() 
                   WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $status, $remarks, $complaint_id);

    if ($stmt->execute()) {
        $message = "Complaint updated successfully!";
        // Refresh complaint details
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $complaint_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $complaint = $result->fetch_assoc();
    } else {
        $message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Update Complaint</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card shadow">
        <div class="card-body">
          <h4 class="mb-4">Update Complaint</h4>

          <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
          <?php endif; ?>

          <p><strong>Customer:</strong> <?php echo htmlspecialchars($complaint['customer_name']); ?> (<?php echo htmlspecialchars($complaint['customer_email']); ?>)</p>
          <p><strong>Mobile:</strong> <?php echo htmlspecialchars($complaint['customer_mobile']); ?></p>
          <p><strong>Description:</strong> <?php echo htmlspecialchars($complaint['description']); ?></p>
          <p><strong>Created At:</strong> <?php echo $complaint['created_at']; ?></p>

          <form method="POST">
            <div class="mb-3">
              <label>Status</label>
              <select name="status" class="form-select" required>
                <option value="Pending" <?php if ($complaint['status']=="Pending") echo "selected"; ?>>Pending</option>
                <option value="In Progress" <?php if ($complaint['status']=="In Progress") echo "selected"; ?>>In Progress</option>
                <option value="Resolved" <?php if ($complaint['status']=="Resolved") echo "selected"; ?>>Resolved</option>
                <option value="Closed" <?php if ($complaint['status']=="Closed") echo "selected"; ?>>Closed</option>
              </select>
            </div>

            <div class="mb-3">
              <label>Remarks</label>
              <textarea name="remarks" class="form-control" rows="3"><?php echo htmlspecialchars($complaint['remarks']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-success">Update</button>
            <a href="employee_complaints.php" class="btn btn-secondary">Back</a>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
