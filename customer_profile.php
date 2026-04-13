<?php
session_start();
include 'db.php';

// ✅ Only allow logged-in customers
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];

// Fetch customer details
$sql = "SELECT * FROM customers WHERE customer_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$customer = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile - Synergy System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; font-family: "Segoe UI", sans-serif; }
    .profile-card { border-radius: 12px; background: #fff; padding: 30px; }
    .profile-header { background: linear-gradient(135deg, #198754, #0d6efd); padding: 40px 20px; border-radius: 12px 12px 0 0; color: #fff; text-align: center; }
    .profile-header h2 { margin: 0; }
    .profile-header p { margin: 5px 0 0; opacity: 0.9; }
    .profile-details { padding: 30px; }
    .profile-details h5 { color: #198754; font-weight: 600; }
    .detail-item { margin-bottom: 15px; }
    .detail-item strong { color: #333; }
  </style>
</head>
<body>
<div class="container mt-5">
  <div class="profile-card shadow-lg">
    
    <!-- Header -->
    <div class="profile-header">
      <h2>👤 <?= htmlspecialchars($customer['first_name']." ".$customer['last_name']) ?></h2>
      <p>Welcome to your profile</p>
    </div>

    <!-- Profile Details -->
    <div class="profile-details">
      <?php if ($customer): ?>
        <div class="detail-item">
          <h5>📧 Email</h5>
          <p><?= htmlspecialchars($customer['email']) ?></p>
        </div>
        <div class="detail-item">
          <h5>📱 Mobile</h5>
          <p><?= htmlspecialchars($customer['mobile_no']) ?></p>
        </div>
        <div class="detail-item">
          <h5>🏠 Address</h5>
          <p><?= htmlspecialchars($customer['address']) ?></p>
        </div>
      <?php else: ?>
        <div class="alert alert-danger">❌ Customer profile not found!</div>
      <?php endif; ?>
    </div>

    <!-- Footer Buttons -->
    <div class="text-center pb-3">
      <a href="customer_dashboard.php" class="btn btn-secondary">⬅ Back to Dashboard</a>
      <a href="customer_profile_edit.php" class="btn btn-success">✏️ Edit Profile</a>
    </div>
  </div>
</div>
</body>
</html>
