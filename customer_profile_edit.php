<?php
session_start();
include 'db.php';

// ✅ Only logged-in customers
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$customer_id = $_SESSION['customer_id'];
$message = "";

// ✅ Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name  = trim($_POST['last_name']);
    $email      = trim($_POST['email']);
    $mobile     = trim($_POST['mobile']);
    $address    = trim($_POST['address']);

    $sql = "UPDATE customers 
            SET first_name=?, last_name=?, email=?, mobile_no=?, address=? 
            WHERE customer_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $mobile, $address, $customer_id);

    if ($stmt->execute()) {
        $message = "✅ Profile updated successfully!";
    } else {
        $message = "❌ Error updating profile: " . $stmt->error;
    }
    $stmt->close();
}

// ✅ Fetch current customer data
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
  <title>Edit My Profile - Synergy System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background: #f0f2f5; font-family: "Segoe UI", sans-serif; }
    .card { border-radius: 12px; }
    .card-header { background: linear-gradient(135deg, #198754, #0d6efd); color: #fff; border-radius: 12px 12px 0 0; }
    .form-label { font-weight: 600; }
  </style>
</head>
<body>
<div class="container mt-5">
  <div class="card shadow-lg">
    <div class="card-header text-center">
      <h3>✏️ Edit My Profile</h3>
    </div>
    <div class="card-body">
      <?php if ($message): ?>
        <div class="alert alert-info"><?= $message ?></div>
      <?php endif; ?>

      <?php if ($customer): ?>
      <form method="POST">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($customer['first_name']) ?>" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($customer['last_name']) ?>" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Mobile</label>
          <input type="text" name="mobile" class="form-control" value="<?= htmlspecialchars($customer['mobile_no']) ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" rows="3" required><?= htmlspecialchars($customer['address']) ?></textarea>
        </div>

        <div class="text-center">
          <button type="submit" class="btn btn-success">💾 Save Changes</button>
          <a href="customer_profile.php" class="btn btn-secondary">⬅ Cancel</a>
        </div>
      </form>
      <?php else: ?>
        <div class="alert alert-danger">❌ Profile not found!</div>
      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>
