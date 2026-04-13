<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
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
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $date_of_joining = $_POST['date_of_joining'];

    // Insert into users table
    $stmt1 = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'employee')");
    $stmt1->bind_param("ss", $username, $password);

    if ($stmt1->execute()) {
        $user_id = $stmt1->insert_id;

        // Insert into employee_details
        $stmt2 = $conn->prepare("INSERT INTO employee_details 
        (user_id, first_name, middle_name, last_name, designation, email_id, mobile_no, residential_address, permanent_address, date_of_joining) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt2->bind_param("isssssssss", $user_id, $first_name, $middle_name, $last_name, $designation, $email_id, $mobile_no, $residential_address, $permanent_address, $date_of_joining);

        if ($stmt2->execute()) {
            // ✅ Log action into activity_logs
            $action = "Added new employee: " . $first_name . " " . $last_name;
            $performed_by = $_SESSION['username'];
            $logStmt = $conn->prepare("INSERT INTO activity_logs (action, performed_by) VALUES (?, ?)");
            $logStmt->bind_param("ss", $action, $performed_by);
            $logStmt->execute();
            $logStmt->close();

            echo "<script>alert('Employee added successfully!'); window.location='admin_dashboard.php';</script>";
        } else {
            echo "Error: " . $stmt2->error;
        }

        $stmt2->close();
    } else {
        echo "Error: " . $stmt1->error;
    }

    $stmt1->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Employee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2 class="mb-4">➕ Add Employee</h2>
    <form method="POST" class="card p-4 shadow">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>First Name</label>
                <input type="text" name="first_name" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label>Middle Name</label>
                <input type="text" name="middle_name" class="form-control">
            </div>
            <div class="col-md-4 mb-3">
                <label>Last Name</label>
                <input type="text" name="last_name" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label>Designation</label>
            <input type="text" name="designation" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email_id" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Mobile</label>
            <input type="text" name="mobile_no" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Residential Address</label>
            <textarea name="residential_address" class="form-control" required></textarea>
        </div>

        <div class="mb-3">
            <label>Permanent Address</label>
            <textarea name="permanent_address" class="form-control" required></textarea>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
        </div>

        <div class="mb-3">
            <label>Date of Joining</label>
            <input type="date" name="date_of_joining" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary">Add Employee</button>
        <a href="admin_dashboard.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
