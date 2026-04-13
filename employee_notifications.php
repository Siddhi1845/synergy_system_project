<?php
session_start();
include 'db.php';

// Allow only employees
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

// Mark as read if requested
if (isset($_GET['mark_read'])) {
    $id = intval($_GET['mark_read']);
    $stmt = $conn->prepare("UPDATE notifications SET is_read=1 WHERE id=? AND user_type='employee' AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $stmt->close();
    header("Location: employee_notifications.php");
    exit();
}

// Fetch notifications for this employee
$sql = "SELECT * FROM notifications WHERE user_type='employee' AND user_id=? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Notifications</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h3>🔔 My Notifications</h3>
  <a href="employee_dashboard.php" class="btn btn-secondary mb-3">⬅ Back to Dashboard</a>
  
  <div class="card shadow-sm">
    <div class="card-body">
      <?php if ($result->num_rows > 0): ?>
        <ul class="list-group">
          <?php while ($row = $result->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <?php if ($row['is_read'] == 0): ?>
                  <strong><?php echo htmlspecialchars($row['message']); ?></strong>
                <?php else: ?>
                  <?php echo htmlspecialchars($row['message']); ?>
                <?php endif; ?>
                <br>
                <small class="text-muted"><?php echo $row['created_at']; ?></small>
              </div>
              <?php if ($row['is_read'] == 0): ?>
                <a href="?mark_read=<?php echo $row['id']; ?>" class="btn btn-sm btn-success">Mark Read</a>
              <?php else: ?>
                <span class="badge bg-secondary">Read</span>
              <?php endif; ?>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p class="text-center">No notifications found.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
