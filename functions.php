<?php
// ==============================
// Stock Update Helper Functions
// ==============================
// These functions replicate the old trigger behavior.
// Call them after order INSERT, UPDATE, or DELETE operations.

if (!function_exists('inc_product_stock_on_order')) {
    function inc_product_stock_on_order($conn, $product_id, $quantity) {
        $sql = "UPDATE products 
                SET sold_qty = sold_qty + ?, 
                    remaining_qty = remaining_qty - ? 
                WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $quantity, $product_id);
        $stmt->execute();
        $stmt->close();
    }
}

if (!function_exists('dec_product_stock_on_order')) {
    function dec_product_stock_on_order($conn, $product_id, $quantity) {
        $sql = "UPDATE products 
                SET sold_qty = sold_qty - ?, 
                    remaining_qty = remaining_qty + ? 
                WHERE product_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $quantity, $quantity, $product_id);
        $stmt->execute();
        $stmt->close();
    }
}

function db_begin($conn) {
    $conn->begin_transaction();
}
function db_commit($conn) {
    $conn->commit();
}
function db_rollback($conn) {
    $conn->rollback();
}
?>
