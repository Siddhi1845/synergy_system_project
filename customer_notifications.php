<?php
session_start();
include 'db.php';

// ✅ Only logged-in customers
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch all notifications
$stmt = $conn->prepare("SELECT id, message, created_at, is_read FROM notifications WHERE user_type='customer' AND user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$res = $stmt->get_result();

// Mark all as read
$conn->query("UPDATE notifications SET is_read=1 WHERE user_type='customer' AND user_id=$customer_id");
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
  <h2>🔔 My Notifications</h2>
  <a href="customer_dashboard.php" class="btn btn-secondary mb-3">⬅ Back</a>
  <div class="card shadow-sm">
    <div class="card-body">
      <?php if ($res->num_rows > 0): ?>
        <ul class="list-group">
          <?php while ($n = $res->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div>
                <?= htmlspecialchars($n['message']) ?>
                <div><small class="text-muted"><?= $n['created_at'] ?></small></div>
              </div>
              <?php if (!$n['is_read']): ?>
                <span class="badge bg-warning">New</span>
              <?php endif; ?>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p class="text-muted">No notifications yet.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
