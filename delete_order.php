<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// ✅ Only employees allowed
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'employee') {
    header("Location: login.php");
    exit();
}

// ✅ Validate order_id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("❌ Invalid request.");
}

$order_id = intval($_GET['id']);

// ✅ Begin a transaction (so delete + stock update are atomic)
$conn->begin_transaction();

try {
    // Fetch order details (include status)
    $stmt = $conn->prepare("SELECT product_id, quantity, status FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$order) {
        throw new Exception("Order not found.");
    }

    // ✅ Restore stock only if order is not 'cancelled'
    if ($order['status'] !== 'cancelled') {
        dec_product_stock_on_order($conn, (int)$order['product_id'], (int)$order['quantity']);
    }

    // ✅ Delete the order
    $delete = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
    $delete->bind_param("i", $order_id);
    $delete->execute();
    $delete->close();

    // ✅ Commit the changes
    $conn->commit();

    header("Location: view_orders.php?msg=✅ Order+deleted+successfully");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $error_msg = urlencode("❌ Delete failed: " . $e->getMessage());
    header("Location: view_orders.php?msg=$error_msg");
    exit();
}
?>
