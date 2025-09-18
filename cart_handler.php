<?php
session_start();
include 'config.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

if ($_POST['action'] == 'add_to_cart') {
    $product_id = (int) $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

    // Get product details from database
    $query = "SELECT * FROM product WHERE id = $product_id";
    $result = mysqli_query($db, $query);
    $product = mysqli_fetch_assoc($result);

    if ($product) {
        $found = false;
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $product_id) {
                $_SESSION['cart'][$key]['quantity'] += $quantity;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $_SESSION['cart'][] = array(
                'id' => $product['id'],
                'name' => $product['name'],
                'price' => $product['price'],
                'photo' => $product['photo'],
                'category' => isset($product['category_name']) ? $product['category_name'] : '',
                'quantity' => $quantity
            );
        }

        echo json_encode(['success' => true, 'message' => 'Product added to cart', 'cart_count' => count($_SESSION['cart'])]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Product not found']);
    }
}

if ($_POST['action'] == 'remove_from_cart') {
    $product_id = (int) $_POST['product_id'];

    foreach ($_SESSION['cart'] as $key => $item) {
        if ($item['id'] == $product_id) {
            unset($_SESSION['cart'][$key]);
            $_SESSION['cart'] = array_values($_SESSION['cart']);
            break;
        }
    }

    echo json_encode(['success' => true, 'message' => 'Product removed from cart', 'cart_count' => count($_SESSION['cart'])]);
}

if ($_POST['action'] == 'update_cart') {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];

    if ($quantity <= 0) {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $product_id) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart'] = array_values($_SESSION['cart']);
                break;
            }
        }
    } else {
        foreach ($_SESSION['cart'] as $key => $item) {
            if ($item['id'] == $product_id) {
                $_SESSION['cart'][$key]['quantity'] = $quantity;
                break;
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Cart updated', 'cart_count' => count($_SESSION['cart'])]);
}

if ($_POST['action'] == 'get_cart') {
    echo json_encode(['cart' => $_SESSION['cart'], 'cart_count' => count($_SESSION['cart'])]);
}
?>