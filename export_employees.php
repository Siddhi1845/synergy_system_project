<?php
session_start();
include 'db.php';

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Set headers to force download as CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=employees.csv');

// Open PHP output stream
$output = fopen('php://output', 'w');

// Write column headers
fputcsv($output, ['Employee ID', 'First Name', 'Middle Name', 'Last Name', 'Designation', 'Email', 'Mobile', 'Username']);

// Fetch employees
$sql = "SELECT e.employee_id, e.first_name, e.middle_name, e.last_name, e.designation, e.email_id, e.mobile_no, u.username
        FROM employee_details e
        JOIN users u ON e.user_id = u.user_id
        ORDER BY e.employee_id DESC";
$result = $conn->query($sql);

// Write rows
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}

// Close output
fclose($output);
exit();
?>
