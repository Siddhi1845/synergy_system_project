<?php
include 'db.php';
session_start();

// ✅ Allow only employees
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view_customers.php");
    exit();
}

$id = intval($_GET['id']);

// --- Fetch customer data ---
$stmt = $conn->prepare("SELECT * FROM customers WHERE customer_id = ?");
if (!$stmt) {
    die("SQL error: " . $conn->error);
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$customer = $result->fetch_assoc();
$stmt->close();

if (!$customer) {
    die("❌ Customer not found.");
}

// --- Handle update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'] ?? '';
    $last_name  = $_POST['last_name'] ?? '';
    $email      = $_POST['email'] ?? '';
    $phone      = $_POST['phone'] ?? '';
    $mobile_no  = $_POST['mobile_no'] ?? '';
    $address    = $_POST['address'] ?? '';

    $update = $conn->prepare("UPDATE customers 
        SET first_name=?, last_name=?, email=?, phone=?, mobile_no=?, address=? 
        WHERE customer_id=?");

    if (!$update) {
        die("SQL prepare failed: " . $conn->error);
    }

    $update->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $mobile_no, $address, $id);

    if ($update->execute()) {
        header("Location: view_customers.php?msg=updated");
        exit();
    } else {
        echo "❌ Error updating customer: " . $update->error;
    }

    $update->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Customer</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <h2>Edit Customer</h2>
  <form method="POST">
    <div class="row">
      <div class="col-md-6 mb-3">
        <label>First Name</label>
        <input type="text" name="first_name" class="form-control" value="<?= htmlspecialchars($customer['first_name']) ?>" required>
      </div>
      <div class="col-md-6 mb-3">
        <label>Last Name</label>
        <input type="text" name="last_name" class="form-control" value="<?= htmlspecialchars($customer['last_name']) ?>" required>
      </div>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($customer['email']) ?>" required>
    </div>
    <div class="row">
      <div class="col-md-6 mb-3">
        <label>Phone</label>
        <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($customer['phone']) ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label>Mobile No</label>
        <input type="text" name="mobile_no" class="form-control" value="<?= htmlspecialchars($customer['mobile_no']) ?>">
      </div>
    </div>
    <div class="mb-3">
      <label>Address</label>
      <textarea name="address" class="form-control"><?= htmlspecialchars($customer['address']) ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="view_customers.php" class="btn btn-secondary">Cancel</a>
  </form>
</div>
</body>
</html>
