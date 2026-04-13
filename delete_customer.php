<?php
include 'db.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['role'] != 'employee') {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: view_customers.php");
        exit();
    } else {
        echo "Error deleting customer: " . $stmt->error;
    }
}
?>
