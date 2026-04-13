<?php
session_start();
include 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: view_products.php?msg=notfound");
    exit();
}

$id = intval($_GET['id']);

// Delete product
$stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
if (!$stmt) {
    die("SQL error: " . $conn->error);
}
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: view_products.php?msg=deleted");
    exit();
} else {
    header("Location: view_products.php?msg=error");
    exit();
}
