<?php
session_start();
include 'db.php';

// check customer login
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// find customer id
$customer_q = $conn->query("SELECT id FROM customers WHERE user_id = '$user_id' LIMIT 1");
$customer = $customer_q->fetch_assoc();
$customer_id = $customer['id'] ?? 0;

if (!$customer_id) {
    die("Customer profile not found!");
}

// product id from URL
if (!isset($_GET['product_id'])) {
    die("Invalid request.");
}
$product_id = intval($_GET['product_id']);

// fetch product details
$pq = $conn->query("SELECT * FROM products WHERE id = $product_id LIMIT 1");
$product = $pq->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// calculate service dates
$today = date("Y-m-d");
$frequency = (int)($product['frequency_service_months'] ?? 12);
$next_date = date("Y-m-d", strtotime("+$frequency months", strtotime($today)));

// insert into service_reminders
$stmt = $conn->prepare("INSERT INTO service_reminders 
    (customer_id, product_id, product_name, next_service_date, recurrence_months, status) 
    VALUES (?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("iissi", $customer_id, $product_id, $product['name'], $next_date, $frequency);

if ($stmt->execute()) {
    $_SESSION['msg'] = "✅ Service request placed successfully!";
} else {
    $_SESSION['msg'] = "❌ Error: " . $conn->error;
}

header("Location: customer_products.php");
exit();
?>
