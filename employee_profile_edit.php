<?php
session_start();
include 'db.php';

// Check employee login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch employee details
$sql = "SELECT * FROM employee_details WHERE user_id='$user_id'";
$result = $conn->query($sql);
$employee = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $residential_address = $_POST['residential_address'];
    $permanent_address = $_POST['permanent_address'];

    $update = "UPDATE employee_details 
               SET email_id='$email', mobile_no='$mobile',
                   residential_address='$residential_address',
                   permanent_address='$permanent_address'
               WHERE user_id='$user_id'";

    if ($conn->query($update)) {
        header("Location: employee_dashboard.php?success=1");
        exit();
    } else {
        echo "Error updating profile: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Profile</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <div class="card shadow-sm p-4">
      <h3>Edit Profile</h3>
      <form method="POST">
        <div class="mb-3">
          <label>Email</label>
          <input type="email" name="email" class="form-control" value="<?php echo $employee['email_id']; ?>" required>
        </div>
        <div class="mb-3">
          <label>Mobile</label>
          <input type="text" name="mobile" class="form-control" value="<?php echo $employee['mobile_no']; ?>" required>
        </div>
        <div class="mb-3">
          <label>Residential Address</label>
          <textarea name="residential_address" class="form-control"><?php echo $employee['residential_address']; ?></textarea>
        </div>
        <div class="mb-3">
          <label>Permanent Address</label>
          <textarea name="permanent_address" class="form-control"><?php echo $employee['permanent_address']; ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="employee_dashboard.php" class="btn btn-secondary">Cancel</a>
      </form>
    </div>
  </div>
</body>
</html>
