<?php
session_start();
include 'db.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch user
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $stored_password = $row['password'];
        $entered_password = $password;

        // Check if stored password is hashed
        $is_hash = preg_match('/^\$2[aby]\$.{56}$/', $stored_password);

        if ($is_hash) {
            if (password_verify($entered_password, $stored_password)) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                if ($row['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: employee_dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password.";
            }
        } else {
            if ($entered_password === $stored_password) {
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['role'] = $row['role'];
                if ($row['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: employee_dashboard.php");
                }
                exit();
            } else {
                $error = "Invalid password.";
            }
        }
    } else {
        $error = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
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
      padding: 50px;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .brand {
      display: flex;
      align-items: center;
      margin-bottom: 30px;
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
      <img src="assets/Login1.jpg" alt="Login Illustration">
    </div>

    <!-- Right Side: Login Form -->
    <div class="login-form">
      
      <!-- Logo + Name -->
      <div class="brand">
        <img src="assets/Logo.jpeg" alt="Logo">
        <span>Synergy <strong>Akshay Urja</strong></span>
      </div>

      

      <?php if (!empty($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Login</button>
      </form>
    </div>

  </div>
</body>
</html>
