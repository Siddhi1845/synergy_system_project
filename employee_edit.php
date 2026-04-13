<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get employee ID
if (!isset($_GET['id'])) {
    header("Location: admin_dashboard.php");
    exit();
}

$employee_id = intval($_GET['id']);

// Fetch existing details
$sql = "SELECT e.*, u.username 
        FROM employee_details e
        JOIN users u ON e.user_id = u.user_id
        WHERE e.employee_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employee_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    echo "Employee not found!";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $designation = $_POST['designation'];
    $email_id = $_POST['email_id'];
    $mobile_no = $_POST['mobile_no'];
    $residential_address = $_POST['residential_address'];
    $permanent_address = $_POST['permanent_address'];
    $date_of_joining = $_POST['date_of_joining'];
    $username = $_POST['username'];

    // Update employee_details
    $stmt1 = $conn->prepare("UPDATE employee_details 
        SET first_name=?, middle_name=?, last_name=?, designation=?, email_id=?, mobile_no=?, residential_address=?, permanent_address=?, date_of_joining=? 
        WHERE employee_id=?");
    $stmt1->bind_param("sssssssssi", $first_name, $middle_name, $last_name, $designation, $email_id, $mobile_no, $residential_address, $permanent_address, $date_of_joining, $employee_id);

    // Update username in users table
    $stmt2 = $conn->prepare("UPDATE users SET username=? WHERE user_id=?");
    $stmt2->bind_param("si", $username, $employee['user_id']);

    if ($stmt1->execute() && $stmt2->execute()) {
        // ✅ Log action
        $action = "Updated employee: " . $first_name . " " . $last_name;
        $performed_by = $_SESSION['username'];
        $logStmt = $conn->prepare("INSERT INTO activity_logs (action, performed_by) VALUES (?, ?)");
        $logStmt->bind_param("ss", $action, $performed_by);
        $logStmt->execute();
        $logStmt->close();

        echo "<script>alert('Employee updated successfully!'); window.location='admin_dashboard.php';</script>";
    } else {
        echo "Error updating employee!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">✏️ Edit Employee</h2>
    <form method="POST" class="card p-4 shadow">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" value="<?= $employee['first_name'] ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label>Middle Name</label>
                <input type="text" name="middle_name" class="form-control" value="<?= $employee['middle_name'] ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" value="<?= $employee['last_name'] ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label>Designation</label>
            <input type="text" name="designation" class="form-control" value="<?= $employee['designation'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email_id" class="form-control" value="<?= $employee['email_id'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Mobile</label>
            <input type="text" name="mobile_no" class="form-control" value="<?= $employee['mobile_no'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Residential Address</label>
            <textarea name="residential_address" class="form-control" required><?= $employee['residential_address'] ?></textarea>
        </div>

        <div class="mb-3">
            <label>Permanent Address</label>
            <textarea name="permanent_address" class="form-control" required><?= $employee['permanent_address'] ?></textarea>
        </div>

        <div class="mb-3">
            <label>Date of Joining</label>
            <input type="date" name="date_of_joining" class="form-control" value="<?= $employee['date_of_joining'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?= $employee['username'] ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Update Employee</button>
        <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
