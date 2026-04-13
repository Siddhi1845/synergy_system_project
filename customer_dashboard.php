<?php
session_start();
include 'db.php';

// ✅ Only allow logged-in customers
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$customer_name = $_SESSION['customer_name'] ?? "Customer";

// Count unread notifications for this customer
$notif_count = 0;
$stmt = $conn->prepare("SELECT COUNT(*) as c FROM notifications WHERE user_type='customer' AND user_id=? AND is_read=0");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $notif_count = $row['c'];
}
$stmt->close();

// Fetch latest notifications (optional: if you want to display somewhere)
$notif_stmt = $conn->prepare("SELECT message, created_at FROM notifications WHERE user_type='customer' AND user_id=? ORDER BY created_at DESC LIMIT 5");
$notif_stmt->bind_param("i", $customer_id);
$notif_stmt->execute();
$notif_res = $notif_stmt->get_result();
?>
<?php include 'header.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .card {
        transition: transform 0.2s ease;
    }
    .card:hover {
        transform: scale(1.05);
    }
  </style>
</head>
<body class="bg-light">

<div class="container mt-5">
  <!-- Top bar -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>👤 Welcome, <?= htmlspecialchars($customer_name) ?></h2>
    <div>
      <a href="customer_notifications.php" class="btn btn-outline-primary position-relative me-2">
        🔔 Notifications
        <?php if ($notif_count > 0): ?>
          <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
            <?= $notif_count ?>
          </span>
        <?php endif; ?>
      </a>
      <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
  </div>

  <!-- Dashboard cards -->
  <div class="row">
    <!-- Orders -->
    <div class="col-md-4 mb-4">
      <a href="customer_orders.php" class="text-decoration-none">
        <div class="card shadow-sm text-center p-4">
          <h4>🛒 My Orders</h4>
          <p>View and place new product orders.</p>
        </div>
      </a>
    </div>

    <!-- Complaints -->
    <div class="col-md-4 mb-4">
      <a href="customer_complaints.php" class="text-decoration-none">
        <div class="card shadow-sm text-center p-4">
          <h4>📢 Complaints</h4>
          <p>Raise and track complaints easily.</p>
        </div>
      </a>
    </div>

    <!-- Service Reminders -->
    <div class="col-md-4 mb-4">
      <a href="customer_reminders.php" class="text-decoration-none">
        <div class="card shadow-sm text-center p-4">
          <h4>⏰ Service Reminders</h4>
          <p>Check your upcoming service deadlines.</p>
        </div>
      </a>
    </div>

    <!-- Notifications -->
    <div class="col-md-4 mb-4">
      <a href="customer_notifications.php" class="text-decoration-none">
        <div class="card shadow-sm text-center p-4">
          <h4>🔔 Notifications</h4>
          <p>See important updates and alerts.</p>
        </div>
      </a>
    </div>

    <!-- Profile -->
    <div class="col-md-4 mb-4">
      <a href="customer_profile.php" class="text-decoration-none">
        <div class="card shadow-sm text-center p-4">
          <h4>👨‍💼 My Profile</h4>
          <p>View and update your details.</p>
        </div>
      </a>
    </div>
  </div>

  <!-- Decorative Banner -->
  <div class="mt-5 p-5 text-center text-white" 
       style="background: linear-gradient(135deg, #007acc, #00c6ff); border-radius: 12px;">
    <h3>⚡ Powering a Greener Future with <strong>Synergy Akshay Urja</strong></h3>
    <p class="mb-3">Your trusted partner in renewable energy and sustainable solutions.</p>
  </div>

  <!-- Quote Section -->
  <div class="mt-5 p-4 text-center" style="font-style: italic; color: #555;">
    <h4>“The future will be green, or not at all.” 🌱</h4>
    <p>- Synergy Akshay Urja</p>
  </div>
</div>

</body>
</html>
