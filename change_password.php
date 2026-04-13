<?php
session_start();
include 'db.php'; // adjust if your connection file name is different

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password     = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 4) {
        $error = "Choose a longer password.";
    } else {
        // fetch stored password
        $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows !== 1) {
            $error = "User not found.";
        } else {
            $row = $res->fetch_assoc();
            $stored = $row['password'];

            // Detect bcrypt hashes starting with $2y$, $2a$, $2b$ (common)
            $is_hash = preg_match('/^\$2[aby]\$.{56}$/', $stored);

            if ($is_hash) {
                // stored password is hashed -> use password_verify
                if (!password_verify($current_password, $stored)) {
                    $error = "Current password is incorrect.";
                } else {
                    // We'll update hashed -> keep hashing
                    $new_hashed = password_hash($new_password, PASSWORD_DEFAULT);
                    $u = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $u->bind_param("si", $new_hashed, $user_id);
                    $u->execute();
                    if ($u->affected_rows >= 0) $success = "Password changed successfully.";
                    else $error = "Database update error.";
                }
            } else {
                // stored password is plain text -> compare directly
                if ($current_password !== $stored) {
                    $error = "Current password is incorrect.";
                } else {
                    // Option 1: keep storing plaintext (not secure)
                    // $store_value = $new_password;

                    // Option 2 (recommended): switch this account to hashed passwords now
                    $store_value = password_hash($new_password, PASSWORD_DEFAULT);

                    $u = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                    $u->bind_param("si", $store_value, $user_id);
                    $u->execute();
                    if ($u->affected_rows >= 0) $success = "Password changed successfully (and stored securely).";
                    else $error = "Database update error.";
                }
            }
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Change Password</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="card p-4 shadow">
    <h3>Change Password</h3>
    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($success): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <form method="post">
      <div class="mb-3">
        <label>Current Password</label>
        <input type="password" name="current_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>New Password</label>
        <input type="password" name="new_password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" class="form-control" required>
      </div>
      <button class="btn btn-primary">Update Password</button>
      <a href="employee_dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
  </div>
</div>
</body>
</html>
