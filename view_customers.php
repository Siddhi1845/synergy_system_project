<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

$sql = "SELECT * FROM customers ORDER BY created_at DESC";
$result = $conn->query($sql);

// Debugging if query fails
if (!$result) {
    die("Query Failed: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customers</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
  <h2>Customer List</h2>
  
  <table class="table table-bordered table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Phone</th>
        <th>Mobile No</th>
        <th>Address</th>
        <th>Registered At</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['customer_id']; ?></td>
            <td><?= $row['first_name'] . " " . $row['last_name']; ?></td>
            <td><?= $row['email']; ?></td>
            <td><?= $row['phone']; ?></td>
            <td><?= $row['mobile_no']; ?></td>
            <td><?= $row['address']; ?></td>
            <td><?= $row['created_at']; ?></td>
            <td>
              <a href="edit_customer.php?id=<?= $row['customer_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
              <a href="delete_customer.php?id=<?= $row['customer_id']; ?>" 
                 class="btn btn-danger btn-sm" 
                 onclick="return confirm('Delete this customer?');">Delete</a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="8" class="text-center text-muted">No customers found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</body>
</html>
