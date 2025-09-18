<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'place_order') {
    $user_id = $_SESSION['user_id'];
    
    $billing = $_POST['billing'];
    $payment_method = $_POST['payment_method'];
    $cart_items = json_decode($_POST['cart_items'], true);
    $subtotal = floatval($_POST['subtotal']);
    $discount = floatval($_POST['discount']);
    $total = floatval($_POST['total']);

    if (empty($billing['firstname']) || empty($billing['lastname']) || 
        empty($billing['streetaddress']) || empty($billing['phone']) || 
        empty($billing['email']) || empty($payment_method)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        exit();
    }

    if (empty($cart_items)) {
        echo json_encode(['success' => false, 'message' => 'Cart is empty']);
        exit();
    }

    mysqli_autocommit($db, false);
    
    try {
        $sql_transaction = "INSERT INTO transaction (id_user, date, total_price, payment_method) 
                           VALUES (?, NOW(), ?, ?)";
        $stmt = mysqli_prepare($db, $sql_transaction);
        if (!$stmt) {
            throw new Exception("Prepare failed: " . mysqli_error($db));
        }
        
        mysqli_stmt_bind_param($stmt, "ids", $user_id, $total, $payment_method);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Error inserting transaction: " . mysqli_stmt_error($stmt));
        }

        $transaction_id = mysqli_insert_id($db);

        foreach ($cart_items as $item) {
            $product_id = intval($item['id']);
            $quantity = intval($item['quantity']);

            $check_stock_sql = "SELECT stock FROM product WHERE id = ?";
            $stmt_check = mysqli_prepare($db, $check_stock_sql);
            mysqli_stmt_bind_param($stmt_check, "i", $product_id);
            mysqli_stmt_execute($stmt_check);
            $result = mysqli_stmt_get_result($stmt_check);
            $product_data = mysqli_fetch_assoc($result);
            
            if (!$product_data) {
                throw new Exception("Product not found: " . $product_id);
            }
            
            if ($product_data['stock'] < $quantity) {
                throw new Exception("Insufficient stock for product ID: " . $product_id);
            }

            $sql_detail = "INSERT INTO detail (id_transaction, id_product, amount) 
                          VALUES (?, ?, ?)";
            $stmt_detail = mysqli_prepare($db, $sql_detail);
            if (!$stmt_detail) {
                throw new Exception("Prepare detail failed: " . mysqli_error($db));
            }
            
            mysqli_stmt_bind_param($stmt_detail, "iii", $transaction_id, $product_id, $quantity);
            
            if (!mysqli_stmt_execute($stmt_detail)) {
                throw new Exception("Error inserting detail: " . mysqli_stmt_error($stmt_detail));
            }

            $sql_stock = "UPDATE product SET stock = stock - ? WHERE id = ?";
            $stmt_stock = mysqli_prepare($db, $sql_stock);
            if (!$stmt_stock) {
                throw new Exception("Prepare stock update failed: " . mysqli_error($db));
            }
            
            mysqli_stmt_bind_param($stmt_stock, "ii", $quantity, $product_id);
            
            if (!mysqli_stmt_execute($stmt_stock)) {
                throw new Exception("Error updating stock: " . mysqli_stmt_error($stmt_stock));
            }
        }

        mysqli_commit($db);
        
        $_SESSION['cart'] = [];
        
        echo json_encode([
            'success' => true, 
            'message' => 'Order placed successfully',
            'order_id' => $transaction_id,
            'transaction_id' => $transaction_id
        ]);
        
    } catch (Exception $e) {
        mysqli_rollback($db);
        
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage()
        ]);
    }
    
    mysqli_autocommit($db, true);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>