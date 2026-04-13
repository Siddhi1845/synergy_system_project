<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $mobile_no = $_POST['mobile_no'];
    $address = $_POST['address'];

    $stmt = $conn->prepare("INSERT INTO customers 
        (first_name, last_name, email, password, phone, mobile_no, address, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssssss", $first_name, $last_name, $email, $password, $phone, $mobile_no, $address);

    if ($stmt->execute()) {
        header("Location: customer_login.php");
        exit();
    } else {
        $error = "Error: " . $stmt->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Register</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      height: 100vh;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #f8f9fa;
    }
    .register-container {
      display: flex;
      width: 100%;
      max-width: 1100px;
      height: 90vh;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.15);
      overflow: hidden;
    }
    .register-image {
      background: #f0f4ff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex: 1;
      padding: 20px;
    }
    .register-image img {
      max-width: 90%;
      max-height: 80%;
    }
    .register-form {
      flex: 1;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start; /* Align from top */
      overflow-y: auto;
    }
    .brand {
      display: flex;
      align-items: center;
      margin-bottom: 20px; /* Spacing below logo */
    }
    .brand img {
      height: 40px;
      margin-right: 10px;
    }
    .brand span {
      font-size: 20px;
      font-weight: 600;
      color: #1a1a1a;
    }
    .brand span strong {
      color: #007acc;
    }
  </style>
</head>
<body>
  <div class="register-container">

    <!-- Left Side: Illustration -->
    <div class="register-image">
      <img src="assets/Register1.jpg" alt="Register Illustration">
    </div>

    <!-- Right Side: Register Form -->
    <div class="register-form">
      
      <!-- Logo + Name -->
      <div class="brand">
        <img src="assets/Logo.jpeg" alt="Logo">
        <span>Synergy <strong>Akshay Urja</strong></span>
      </div>

      <h4 class="mb-4">Create Your Account 📝</h4>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Mobile No</label>
            <input type="text" name="mobile_no" class="form-control">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control"></textarea>
        </div>
        <button type="submit" class="btn btn-primary w-100">Register</button>
        <p class="text-center mt-3">
          Already have an account? <a href="customer_login.php">Login</a>
        </p>
      </form>
    </div>
  </div>
</body>
</html>
