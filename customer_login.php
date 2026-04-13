<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT customer_id, first_name, last_name, password FROM customers WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $customer = $result->fetch_assoc();

    if ($customer && password_verify($password, $customer['password'])) {
        $_SESSION['customer_id'] = $customer['customer_id'];
        $_SESSION['customer_name'] = $customer['first_name'] . " " . $customer['last_name'];
        header("Location: customer_dashboard.php");
        exit();
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Customer Login</title>
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
    .login-container {
      display: flex;
      width: 100%;
      max-width: 1100px;
      height: 85vh;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.15);
      overflow: hidden;
    }
    .login-image {
      background: #f0f4ff;
      display: flex;
      align-items: center;
      justify-content: center;
      flex: 1;
      padding: 20px;
    }
    .login-image img {
      max-width: 90%;
      max-height: 80%;
    }
    .login-form {
      flex: 1;
      padding: 40px;
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
    }
    .brand {
      display: flex;
      align-items: center;
      margin-bottom: 25px;
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
  <div class="login-container">

    <!-- Left Side: Illustration -->
    <div class="login-image">
      <img src="assets/Login.jpg" alt="Customer Login Illustration">
    </div>

    <!-- Right Side: Login Form -->
    <div class="login-form">
      
      <!-- Logo + Name -->
      <div class="brand">
        <img src="assets/Logo.jpeg" alt="Logo">
        <span>Synergy <strong>Akshay Urja</strong></span>
      </div>

      <h4 class="mb-4">Customer Login 🔐</h4>

      <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
        <p class="text-center mt-3">
          Don’t have an account? <a href="customer_register.php">Register</a>
        </p>
      </form>
    </div>
  </div>
</body>
</html>
