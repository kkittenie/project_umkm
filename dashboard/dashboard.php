<?php
include '../config.php';
session_start();

if (!isset($_SESSION['admin'])) {
  header('Location: login.php');
}

$current_page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

// Contact messages handling
if ($current_page === 'messages') {
  $action = $_GET['action'] ?? '';
  
  if ($action === 'mark_read' && isset($_GET['id'])) {
    $message_id = (int) $_GET['id'];
    mysqli_query($db, "UPDATE contact_messages SET status = 'read' WHERE id = $message_id");
    $_SESSION['alert'] = "Message marked as read!";
    header("Location: dashboard.php?page=messages");
    exit;
  }
  
  if ($action === 'delete' && isset($_GET['id'])) {
    $message_id = (int) $_GET['id'];
    $result = mysqli_query($db, "DELETE FROM contact_messages WHERE id = $message_id");
    if ($result) {
      $_SESSION['alert'] = "Message deleted successfully!";
    } else {
      $_SESSION['error'] = "Error deleting message.";
    }
    header("Location: dashboard.php?page=messages");
    exit;
  }
  
  if (isset($_POST['bulk_mark_read'])) {
    if (isset($_POST['message_ids']) && is_array($_POST['message_ids'])) {
      $ids = implode(',', array_map('intval', $_POST['message_ids']));
      mysqli_query($db, "UPDATE contact_messages SET status = 'read' WHERE id IN ($ids)");
      $_SESSION['alert'] = "Selected messages marked as read!";
    }
    header("Location: dashboard.php?page=messages");
    exit;
  }
  
  if (isset($_POST['add_admin_note'])) {
    $message_id = (int) $_POST['message_id'];
    $admin_note = mysqli_real_escape_string($db, $_POST['admin_note']);
    
    mysqli_query($db, "UPDATE contact_messages SET admin_notes = '$admin_note', status = 'replied' WHERE id = $message_id");
    $_SESSION['alert'] = "Admin note added successfully!";
    header("Location: dashboard.php?page=messages");
    exit;
  }
}

if ($current_page === 'customers') {
  $action = $_GET['action'] ?? '';

  if ($action === 'delete' && isset($_GET['id'])) {
    $customer_id = (int) $_GET['id'];

    $check_transactions = mysqli_query($db, "SELECT COUNT(*) as count FROM transaction WHERE id_user = $customer_id");
    $transaction_count = mysqli_fetch_assoc($check_transactions)['count'];

    if ($transaction_count > 0) {
      $_SESSION['error'] = "Cannot delete customer with existing orders. Customer has $transaction_count order(s).";
    } else {
      $result = mysqli_query($db, "DELETE FROM users WHERE id = $customer_id AND role = 'user'");
      if ($result && mysqli_affected_rows($db) > 0) {
        $_SESSION['alert'] = "Customer deleted successfully!";
      } else {
        $_SESSION['error'] = "Customer not found or cannot be deleted.";
      }
    }
    header("Location: dashboard.php?page=customers");
    exit;
  }

  if ($action === 'reset_password' && isset($_GET['id'])) {
    $customer_id = (int) $_GET['id'];

    $new_password = 'reset_' . bin2hex(random_bytes(4)); 

    $result = mysqli_query($db, "UPDATE users SET password = '$new_password' WHERE id = $customer_id AND role = 'user'");

    if ($result && mysqli_affected_rows($db) > 0) {
      $_SESSION['alert'] = "Password reset successfully! New password: $new_password";
      $_SESSION['reset_password'] = $new_password;
    } else {
      $_SESSION['error'] = "Customer not found or password reset failed.";
    }
    header("Location: dashboard.php?page=customers");
    exit;
  }

  if ($action === 'toggle_status' && isset($_GET['id'])) {
    $customer_id = (int) $_GET['id'];
    $_SESSION['alert'] = "Customer status updated!";
    header("Location: dashboard.php?page=customers");
    exit;
  }
}

if (isset($_POST['bulk_password_reset']) && $current_page === 'customers') {
  if (isset($_POST['customer_ids']) && is_array($_POST['customer_ids'])) {
    $reset_results = [];

    foreach ($_POST['customer_ids'] as $customer_id) {
      $customer_id = (int) $customer_id;

      $name_result = mysqli_query($db, "SELECT fullname FROM users WHERE id = $customer_id AND role = 'user'");
      $customer = mysqli_fetch_assoc($name_result);

      if ($customer) {
        $new_password = 'reset_' . bin2hex(random_bytes(4));

        $result = mysqli_query($db, "UPDATE users SET password = '$new_password' WHERE id = $customer_id AND role = 'user'");

        if ($result && mysqli_affected_rows($db) > 0) {
          $reset_results[] = [
            'name' => $customer['fullname'],
            'password' => $new_password,
            'success' => true
          ];
        } else {
          $reset_results[] = [
            'name' => $customer['fullname'],
            'success' => false
          ];
        }
      }
    }

    $_SESSION['bulk_reset_results'] = $reset_results;
    $_SESSION['alert'] = "Bulk password reset completed for " . count($reset_results) . " customers.";
    header("Location: dashboard.php?page=customers");
    exit;
  } else {
    $_SESSION['error'] = "No customers selected for password reset.";
  }
}

if (isset($_POST['update_order_status'])) {
  $transaction_id = (int) $_POST['order_id'];
  $new_status = $_POST['new_status'];

  $allowed_statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

  if (in_array($new_status, $allowed_statuses)) {
    $result = mysqli_query($db, "UPDATE transaction SET status = '$new_status' WHERE id_transaction = $transaction_id");
    
    if ($result && mysqli_affected_rows($db) > 0) {
      $_SESSION['alert'] = "Order #$transaction_id status updated to " . ucfirst($new_status);
    } else {
      $_SESSION['error'] = "No transaction found with ID $transaction_id";
    }
  } else {
    $_SESSION['error'] = "Invalid status: $new_status";
  }
}

// Get contact messages data
$contact_messages = [];
if ($current_page === 'messages') {
  $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
  
  if ($status_filter === 'all') {
    $sql = "SELECT * FROM contact_messages ORDER BY date_submitted DESC";
  } else {
    $sql = "SELECT * FROM contact_messages WHERE status = '$status_filter' ORDER BY date_submitted DESC";
  }
  
  $result = mysqli_query($db, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $contact_messages[] = $row;
  }
}

$customers = [];
if ($current_page === 'customers') {
  $search = isset($_GET['search']) ? $_GET['search'] : '';

  if (!empty($search)) {
    $search_term = "%$search%";
    $sql = "SELECT u.*, 
                   COUNT(t.id_transaction) as total_orders, 
                   COALESCE(SUM(t.total_price), 0) as total_spent,
                   MAX(t.date) as last_order_date
            FROM users u 
            LEFT JOIN transaction t ON u.id = t.id_user 
            WHERE u.role = 'user' 
            AND (u.fullname LIKE '$search_term' OR u.email LIKE '$search_term' OR u.username LIKE '$search_term')
            GROUP BY u.id 
            ORDER BY u.id DESC";
  } else {
    $sql = "SELECT u.*, 
                   COUNT(t.id_transaction) as total_orders, 
                   COALESCE(SUM(t.total_price), 0) as total_spent,
                   MAX(t.date) as last_order_date
            FROM users u 
            LEFT JOIN transaction t ON u.id = t.id_user 
            WHERE u.role = 'user' 
            GROUP BY u.id 
            ORDER BY u.id DESC";
  }
  
  $result = mysqli_query($db, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $customers[] = $row;
  }
}

$transactions = [];
if ($current_page === 'orders') {
  $status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

  if ($status_filter === 'all') {
    $sql = "SELECT t.*, u.fullname, u.email, u.phone, u.address 
            FROM transaction t 
            JOIN users u ON t.id_user = u.id 
            ORDER BY t.date DESC";
  } else {
    $sql = "SELECT t.*, u.fullname, u.email, u.phone, u.address 
            FROM transaction t 
            JOIN users u ON t.id_user = u.id 
            WHERE t.status = '$status_filter' 
            ORDER BY t.date DESC";
  }
  
  $result = mysqli_query($db, $sql);
  while ($row = mysqli_fetch_assoc($result)) {
    $transactions[] = $row;
  }
}

$stats = [];
if ($current_page === 'dashboard') {
  $result = mysqli_query($db, "SELECT status, COUNT(*) as count FROM transaction GROUP BY status");
  while ($row = mysqli_fetch_assoc($result)) {
    $stats[$row['status']] = $row['count'];
  }

  $result = mysqli_query($db, "SELECT SUM(total_price) as total_revenue FROM transaction WHERE status IN ('confirmed', 'processing', 'shipped', 'delivered')");
  $revenue_row = mysqli_fetch_assoc($result);
  $stats['total_revenue'] = $revenue_row['total_revenue'] ?? 0;

  $result = mysqli_query($db, "SELECT COUNT(*) as today_orders FROM transaction WHERE DATE(date) = CURDATE()");
  $today_row = mysqli_fetch_assoc($result);
  $stats['today_orders'] = $today_row['today_orders'] ?? 0;

  $result = mysqli_query($db, "SELECT COUNT(*) as total_customers FROM users WHERE role = 'user'");
  $customer_row = mysqli_fetch_assoc($result);
  $stats['total_customers'] = $customer_row['total_customers'] ?? 0;
  
  // Add contact messages stats
  $result = mysqli_query($db, "SELECT COUNT(*) as total_messages FROM contact_messages");
  $messages_row = mysqli_fetch_assoc($result);
  $stats['total_messages'] = $messages_row['total_messages'] ?? 0;
  
  $result = mysqli_query($db, "SELECT COUNT(*) as unread_messages FROM contact_messages WHERE status = 'unread'");
  $unread_row = mysqli_fetch_assoc($result);
  $stats['unread_messages'] = $unread_row['unread_messages'] ?? 0;
}

$action = $_GET['action'] ?? '';

$name = '';
$price = '';
$stock = '';
$category = '';
$photo = '';
$description = '';

if ($action == "edit" && $current_page === 'products') {
  $id = (int)$_GET['id'];
  $result = mysqli_query($db, "SELECT * FROM product WHERE id = $id");
  
  if ($row = mysqli_fetch_assoc($result)) {
    $name = $row['name'];
    $price = $row['price'];
    $stock = $row['stock'];
    $category = $row['id_category'];
    $photo = $row['photo'];
    $description = $row['description'];
  } else {
    $_SESSION['error'] = 'Product not found.';
    header("Location: dashboard.php?page=products");
    exit;
  }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_action'])) {
  $name = mysqli_real_escape_string($db, trim($_POST['name']));
  $price = (float)$_POST['price'];
  $stock = (int)$_POST['stock'];
  $category = (int)$_POST['category'];
  $description = mysqli_real_escape_string($db, trim($_POST['description']));
  
  if (empty($name) || $price < 0 || $stock < 0 || $category <= 0) {
    $_SESSION['error'] = 'Please fill all fields with valid values.';
    header("Location: dashboard.php?page=products");
    exit;
  }

  $photo_uploaded = false;
  if (!empty($_FILES['photo']['name'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
    $file_info = pathinfo($_FILES['photo']['name']);
    $file_extension = strtolower($file_info['extension']);
    
    if (in_array('image/' . $file_extension, $allowed_types) || in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
      $photo = time() . '_' . uniqid() . '.' . $file_extension;
      $upload_path = "../images/" . $photo;
      
      if (!is_dir("../images/")) {
        mkdir("../images/", 0755, true);
      }
      
      if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
        $photo_uploaded = true;
      } else {
        $_SESSION['error'] = 'Failed to upload image. Check folder permissions.';
        header("Location: dashboard.php?page=products");
        exit;
      }
    } else {
      $_SESSION['error'] = 'Only JPG, JPEG, PNG and GIF files are allowed.';
      header("Location: dashboard.php?page=products");
      exit;
    }
  }

  if ($action == "edit") {
    $id = (int)$_GET['id'];

    if (!$photo_uploaded) {
      $result = mysqli_query($db, "SELECT photo FROM product WHERE id = $id");
      $row = mysqli_fetch_assoc($result);
      $photo = $row['photo'];
    }

    $query = "UPDATE product SET name='$name', price=$price, stock=$stock, photo='$photo', id_category=$category, description='$description' WHERE id=$id";
    $result = mysqli_query($db, $query);
    
    if ($result) {
      $_SESSION['alert'] = 'Product updated successfully!';
    } else {
      $_SESSION['error'] = 'Error updating product: ' . mysqli_error($db);
    }
    
  } else {
    if (!$photo_uploaded) {
      $_SESSION['error'] = 'Product image is required for new products.';
      header("Location: dashboard.php?page=products");
      exit;
    }

    $query = "INSERT INTO product (name, price, stock, photo, id_category, description) VALUES ('$name', $price, $stock, '$photo', $category, '$description')";
    $result = mysqli_query($db, $query);
    
    if ($result) {
      $_SESSION['alert'] = 'Product added successfully!';
    } else {
      $_SESSION['error'] = 'Error adding product: ' . mysqli_error($db);
    }
  }
  
  header("Location: dashboard.php?page=products");
  exit;
}

if ($action == "delete" && $current_page === 'products') {
  $id = (int)$_GET['id'];
  
  $check_details = mysqli_query($db, "SELECT COUNT(*) as count FROM detail WHERE id_product = $id");
  $detail_count = mysqli_fetch_assoc($check_details)['count'];
  
  if ($detail_count > 0) {
    $_SESSION['error'] = "Cannot delete product. This product is referenced in $detail_count order detail(s). Consider marking it as inactive instead.";
  } else {
    $result = mysqli_query($db, "DELETE FROM product WHERE id = $id");
    
    if ($result && mysqli_affected_rows($db) > 0) {
      $_SESSION['alert'] = 'Product deleted successfully!';
    } else {
      $_SESSION['error'] = 'Product not found or already deleted.';
    }
  }
  
  header("Location: dashboard.php?page=products");
  exit;
}

if ($action == "toggle_status" && $current_page === 'products') {
  $id = (int)$_GET['id'];
  
  $check_column = mysqli_query($db, "SHOW COLUMNS FROM product LIKE 'status'");
  
  if (mysqli_num_rows($check_column) > 0) {
    $result = mysqli_query($db, "SELECT status FROM product WHERE id = $id");
    $product = mysqli_fetch_assoc($result);
    
    if ($product) {
      $new_status = ($product['status'] == 'active') ? 'inactive' : 'active';
      
      $update_result = mysqli_query($db, "UPDATE product SET status = '$new_status' WHERE id = $id");
      
      if ($update_result) {
        $status_text = ($new_status == 'active') ? 'activated' : 'deactivated';
        $_SESSION['alert'] = "Product $status_text successfully!";
      } else {
        $_SESSION['error'] = 'Error updating product status: ' . mysqli_error($db);
      }
    } else {
      $_SESSION['error'] = 'Product not found.';
    }
  } else {
    $_SESSION['error'] = 'Status functionality not available. Please add status column to product table.';
  }
  
  header("Location: dashboard.php?page=products");
  exit;
}
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Rasa. - Admin Dashboard</title>

  <link href="../assets/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --primary-color: #f96d00;
      --primary-dark: #e55a00;
      --sidebar-bg: #2c3e50;
      --sidebar-hover: #34495e;
      --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      background: #f8fafc;
    }

    .navbar {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--primary-dark) 100%) !important;
      box-shadow: var(--card-shadow);
    }

    .navbar-brand {
      font-family: 'Spectral', serif;
      font-weight: 700;
      font-size: 1.5rem;
    }

    .sidebar {
      background: var(--sidebar-bg) !important;
      min-height: 100vh;
      box-shadow: var(--card-shadow);
    }

    .sidebar .nav-link {
      color: #bdc3c7 !important;
      padding: 12px 20px;
      border-radius: 8px;
      margin: 2px 8px;
      transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover {
      background: var(--sidebar-hover);
      color: white !important;
      transform: translateX(4px);
    }

    .sidebar .nav-link.active {
      background: var(--primary-color);
      color: white !important;
    }

    .sidebar .nav-link i {
      width: 20px;
      margin-right: 10px;
    }

    .main-content {
      background: #f8fafc;
      min-height: 100vh;
    }

    .page-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 30px 0;
      margin-bottom: 30px;
      border-radius: 0 0 20px 20px;
    }

    .stat-card {
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: var(--card-shadow);
      border: none;
      transition: transform 0.3s ease;
    }

    .stat-card:hover {
      transform: translateY(-2px);
    }

    .stat-card .stat-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: white;
      margin-bottom: 15px;
    }

    .order-card {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 20px;
      border: none;
      overflow: hidden;
      transition: transform 0.2s ease;
    }

    .order-card:hover {
      transform: translateY(-2px);
    }

    .order-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-bottom: 1px solid #dee2e6;
      padding: 20px;
    }

    .order-content {
      padding: 20px;
    }

    .status-badge {
      padding: 8px 12px;
      border-radius: 20px;
      font-size: 0.85em;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .status-pending, .status-unread {
      background: #fff3cd;
      color: #856404;
      border: 1px solid #ffeaa7;
    }

    .status-confirmed, .status-read {
      background: #d1edff;
      color: #0c5460;
      border: 1px solid #74b9ff;
    }

    .status-processing {
      background: #e2e3ff;
      color: #383874;
      border: 1px solid #a29bfe;
    }

    .status-shipped {
      background: #f3e2f3;
      color: #6a1b9a;
      border: 1px solid #d63384;
    }

    .status-delivered, .status-replied {
      background: #d4edda;
      color: #155724;
      border: 1px solid #00b894;
    }

    .status-cancelled {
      background: #f8d7da;
      color: #721c24;
      border: 1px solid #fd79a8;
    }

    .product-form {
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 30px;
    }

    .form-control {
      border-radius: 8px;
      border: 2px solid #e9ecef;
      padding: 12px 15px;
      transition: all 0.3s ease;
    }

    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.2rem rgba(249, 109, 0, 0.25);
    }

    .btn {
      border-radius: 8px;
      padding: 10px 20px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background: var(--primary-color);
      border-color: var(--primary-color);
    }

    .btn-primary:hover {
      background: var(--primary-dark);
      border-color: var(--primary-dark);
      transform: translateY(-1px);
    }

    .table {
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--card-shadow);
    }

    .table thead th {
      background: var(--primary-color);
      color: white;
      border: none;
      padding: 15px;
      font-weight: 600;
    }

    .table td {
      padding: 15px;
      vertical-align: middle;
      border-color: #f1f3f4;
    }

    .filter-section {
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 20px;
    }

    .order-items-preview {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-top: 10px;
    }

    .order-item-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 0;
      border-bottom: 1px solid #dee2e6;
    }

    .order-item-row:last-child {
      border-bottom: none;
    }

    .page-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .breadcrumb {
      background: transparent;
      padding: 0;
    }

    .breadcrumb-item+.breadcrumb-item::before {
      color: rgba(255, 255, 255, 0.7);
    }

    .quick-action-btn {
      border-radius: 20px;
      padding: 6px 12px;
      font-size: 0.85em;
      margin: 0 2px;
    }

    .invoice-item {
      border: 1px solid #dee2e6;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 10px;
      transition: all 0.3s ease;
    }

    .invoice-item:hover {
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      transform: translateY(-2px);
    }

    .text-primary {
      color: #F96D00 !important;
    }

    .customer-checkbox, .message-checkbox {
      width: 16px;
      height: 16px;
      flex-shrink: 0;
    }

    .customer-card, .message-card {
      background: white;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 15px;
      border: none;
      overflow: hidden;
      transition: transform 0.2s ease;
    }

    .customer-card:hover, .message-card:hover {
      transform: translateY(-2px);
    }

    .customer-avatar {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--primary-color), var(--primary-dark));
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: bold;
      font-size: 18px;
    }

    .search-section {
      background: white;
      padding: 20px;
      border-radius: 12px;
      box-shadow: var(--card-shadow);
      margin-bottom: 20px;
    }

    .message-header {
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-bottom: 1px solid #dee2e6;
      padding: 20px;
    }

    .message-content {
      padding: 20px;
    }

    .admin-note-section {
      background: #f8f9fa;
      border-radius: 8px;
      padding: 15px;
      margin-top: 15px;
      border-left: 4px solid var(--primary-color);
    }
  </style>
</head>

<body>

  <header class="navbar navbar-dark sticky-top flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3" href="dashboard.php">
      <i class="fas fa-utensils me-2"></i>RASA.
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse"
      data-bs-target="#sidebarMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="navbar-nav">
      <div class="nav-item text-nowrap">
        <a class="nav-link px-3" href="../index.php">
          <i class="fas fa-sign-out-alt me-1"></i>Sign out
        </a>
      </div>
    </div>
  </header>

  <div class="container-fluid">
    <div class="row">
      <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="position-sticky pt-3">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a class="nav-link <?= $current_page === 'dashboard' ? 'active' : '' ?>"
                href="dashboard.php?page=dashboard">
                <i class="fas fa-tachometer-alt"></i>Dashboard
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page === 'messages' ? 'active' : '' ?>" href="dashboard.php?page=messages">
                <i class="fas fa-envelope"></i>Messages
                <?php
                $unread_result = $db->query("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'unread'");
                $unread_count = $unread_result->fetch_assoc()['count'];
                if ($unread_count > 0): ?>
                  <span class="badge bg-danger rounded-pill ms-auto"><?= $unread_count ?></span>
                <?php endif; ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page === 'orders' ? 'active' : '' ?>" href="dashboard.php?page=orders">
                <i class="fas fa-receipt"></i>Orders
                <?php
                $pending_result = $db->query("SELECT COUNT(*) as count FROM transaction WHERE status = 'pending' OR status IS NULL");
                $pending_count = $pending_result->fetch_assoc()['count'];
                if ($pending_count > 0): ?>
                  <span class="badge bg-danger rounded-pill ms-auto"><?= $pending_count ?></span>
                <?php endif; ?>
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page === 'products' ? 'active' : '' ?>"
                href="dashboard.php?page=products">
                <i class="fas fa-box"></i>Products
              </a>
            </li>
            <li class="nav-item">
              <a class="nav-link <?= $current_page === 'customers' ? 'active' : '' ?>"
                href="dashboard.php?page=customers">
                <i class="fas fa-users"></i>Customers
              </a>
            </li>
          </ul>
        </div>
      </nav>

      <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">

        <?php if ($current_page === 'dashboard'): ?>
          <div class="page-header">
            <div class="container-fluid">
              <h1 class="page-title">Dashboard</h1>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item active">Dashboard</li>
                </ol>
              </nav>
            </div>
          </div>

          <div class="container-fluid">
            <div class="row mb-4">
              <div class="col-md-3 mb-3">
                <div class="stat-card">
                  <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-shopping-cart"></i>
                  </div>
                  <h6 class="text-muted">Today's Orders</h6>
                  <h2 class="mb-0"><?= $stats['today_orders'] ?? 0 ?></h2>
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <div class="stat-card">
                  <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <i class="fas fa-envelope"></i>
                  </div>
                  <h6 class="text-muted">Unread Messages</h6>
                  <h2 class="mb-0"><?= $stats['unread_messages'] ?? 0 ?></h2>
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <div class="stat-card">
                  <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                    <i class="fas fa-users"></i>
                  </div>
                  <h6 class="text-muted">Total Customers</h6>
                  <h2 class="mb-0"><?= $stats['total_customers'] ?? 0 ?></h2>
                </div>
              </div>
              <div class="col-md-3 mb-3">
                <div class="stat-card">
                  <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                    <i class="fas fa-dollar-sign"></i>
                  </div>
                  <h6 class="text-muted">Total Revenue</h6>
                  <h2 class="mb-0">IDR <?= number_format($stats['total_revenue'] ?? 0, 0, ',', '.') ?></h2>
                </div>
              </div>
            </div>

            <div class="row">
              <div class="col-12">
                <div class="stat-card">
                  <h5 class="mb-3">Quick Actions</h5>
                  <div class="d-flex flex-wrap gap-2">
                    <?php if ($stats['unread_messages'] > 0): ?>
                      <a href="dashboard.php?page=messages&status=unread" class="btn btn-danger">
                        <i class="fas fa-envelope me-1"></i>Review Unread Messages (<?= $stats['unread_messages'] ?>)
                      </a>
                    <?php endif; ?>
                    <a href="dashboard.php?page=orders&status=pending" class="btn btn-warning">
                      <i class="fas fa-clock me-1"></i>Review Pending Orders
                    </a>
                    <a href="dashboard.php?page=products" class="btn btn-primary">
                      <i class="fas fa-plus me-1"></i>Add New Product
                    </a>
                    <a href="dashboard.php?page=customers" class="btn btn-info">
                      <i class="fas fa-users me-1"></i>Manage Customers
                    </a>
                    <a href="dashboard.php?page=messages" class="btn btn-success">
                      <i class="fas fa-comments me-1"></i>View All Messages
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>

        <?php elseif ($current_page === 'messages'): ?>
          <div class="page-header">
            <div class="container-fluid">
              <h1 class="page-title">Contact Messages</h1>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php" class="text-white">Dashboard</a></li>
                  <li class="breadcrumb-item active">Messages</li>
                </ol>
              </nav>
            </div>
          </div>

          <div class="container-fluid">
            <div class="filter-section">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h5 class="mb-0">Messages Overview</h5>
                  <small class="text-muted">Total Messages: <?= count($contact_messages) ?></small>
                </div>
                <div class="col-md-6">
                  <select class="form-select" id="status-filter" onchange="filterMessages()">
                    <option value="all" <?= ($_GET['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Messages</option>
                    <option value="unread" <?= ($_GET['status'] ?? '') === 'unread' ? 'selected' : '' ?>>Unread</option>
                    <option value="read" <?= ($_GET['status'] ?? '') === 'read' ? 'selected' : '' ?>>Read</option>
                    <option value="replied" <?= ($_GET['status'] ?? '') === 'replied' ? 'selected' : '' ?>>Replied</option>
                  </select>
                </div>
              </div>

              <?php if (!empty($contact_messages)): ?>
                <div class="row mt-3">
                  <div class="col-12">
                    <hr>
                    <div class="d-flex align-items-center gap-3">
                      <h6 class="mb-0">Bulk Actions:</h6>
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleSelectAllMessages()">
                        <i class="fas fa-check-square"></i> Select All
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-success" onclick="bulkMarkAsRead()" disabled
                        id="bulk-read-btn">
                        <i class="fas fa-eye"></i> Mark Selected as Read
                      </button>
                      <span class="text-muted small">Selected: <span id="selected-messages-count">0</span></span>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <?php if (empty($contact_messages)): ?>
              <div class="stat-card text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>No messages found</h4>
                <p class="text-muted">No messages match the current filter.</p>
              </div>
            <?php else: ?>
              <?php foreach ($contact_messages as $message): ?>
                <?php $status_class = 'status-' . $message['status']; ?>
                <div class="message-card">
                  <div class="message-header">
                    <div class="row align-items-center">
                      <div class="col-md-1">
                        <input type="checkbox" class="form-check-input message-checkbox" value="<?= $message['id'] ?>"
                          onchange="updateMessageBulkActions()">
                      </div>
                      <div class="col-md-3">
                        <h6 class="mb-1 fw-bold"><?= htmlspecialchars($message['name']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($message['email']) ?></small>
                      </div>
                      <div class="col-md-4">
                        <h6 class="mb-1"><?= htmlspecialchars($message['subject']) ?></h6>
                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($message['date_submitted'])) ?></small>
                      </div>
                      <div class="col-md-2 text-center">
                        <span class="status-badge <?= $status_class ?>">
                          <?= ucfirst($message['status']) ?>
                        </span>
                      </div>
                      <div class="col-md-2 text-end">
                        <button class="btn btn-outline-primary quick-action-btn" data-bs-toggle="collapse"
                          data-bs-target="#message-<?= $message['id'] ?>">
                          <i class="fas fa-eye"></i> Details
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="collapse" id="message-<?= $message['id'] ?>">
                    <div class="message-content">
                      <div class="row">
                        <div class="col-md-8">
                          <h6><i class="fas fa-comment me-2"></i>Message:</h6>
                          <div class="bg-light p-3 rounded">
                            <p class="mb-0"><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                          </div>
                          
                          <?php if (!empty($message['admin_notes'])): ?>
                            <div class="admin-note-section mt-3">
                              <h6><i class="fas fa-sticky-note me-2"></i>Admin Notes:</h6>
                              <p class="mb-0"><?= nl2br(htmlspecialchars($message['admin_notes'])) ?></p>
                            </div>
                          <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                          <h6><i class="fas fa-cog me-2"></i>Actions:</h6>
                          <div class="d-flex flex-column gap-2">
                            <?php if ($message['status'] === 'unread'): ?>
                              <a href="dashboard.php?page=messages&action=mark_read&id=<?= $message['id'] ?>" 
                                 class="btn btn-sm btn-success">
                                <i class="fas fa-eye me-1"></i>Mark as Read
                              </a>
                            <?php endif; ?>
                            
                            <button class="btn btn-sm btn-info" data-bs-toggle="collapse" 
                                    data-bs-target="#reply-<?= $message['id'] ?>">
                              <i class="fas fa-reply me-1"></i>Add Note
                            </button>
                            
                            <a href="dashboard.php?page=messages&action=delete&id=<?= $message['id'] ?>" 
                               class="btn btn-sm btn-danger btn-delete-message">
                              <i class="fas fa-trash me-1"></i>Delete
                            </a>
                          </div>
                          
                          <div class="collapse mt-3" id="reply-<?= $message['id'] ?>">
                            <form method="POST">
                              <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                              <div class="mb-2">
                                <textarea name="admin_note" class="form-control form-control-sm" 
                                          rows="3" placeholder="Add admin note..."
                                          ><?= htmlspecialchars($message['admin_notes'] ?? '') ?></textarea>
                              </div>
                              <button type="submit" name="add_admin_note" class="btn btn-sm btn-primary">
                                <i class="fas fa-save me-1"></i>Save Note
                              </button>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        <?php elseif ($current_page === 'customers'): ?>
          <div class="page-header">
            <div class="container-fluid">
              <h1 class="page-title">Customer Management</h1>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php" class="text-white">Dashboard</a></li>
                  <li class="breadcrumb-item active">Customers</li>
                </ol>
              </nav>
            </div>
          </div>

          <div class="container-fluid">
            <div class="search-section">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h5 class="mb-0">Customer Overview</h5>
                  <small class="text-muted">Total Customers: <?= count($customers) ?></small>
                </div>
                <div class="col-md-6">
                  <form method="GET" class="d-flex gap-2">
                    <input type="hidden" name="page" value="customers">
                    <input type="text" class="form-control" name="search"
                      placeholder="Search by name, email, or username..."
                      value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-search"></i>
                    </button>
                    <?php if (!empty($_GET['search'])): ?>
                      <a href="dashboard.php?page=customers" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                      </a>
                    <?php endif; ?>
                  </form>
                </div>
              </div>

              <?php if (!empty($customers)): ?>
                <div class="row mt-3">
                  <div class="col-12">
                    <hr>
                    <div class="d-flex align-items-center gap-3">
                      <h6 class="mb-0">Bulk Actions:</h6>
                      <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleSelectAll()">
                        <i class="fas fa-check-square"></i> Select All
                      </button>
                      <button type="button" class="btn btn-sm btn-outline-warning" onclick="bulkResetPassword()" disabled
                        id="bulk-reset-btn">
                        <i class="fas fa-key"></i> Reset Selected Passwords
                      </button>
                      <span class="text-muted small">Selected: <span id="selected-count">0</span></span>
                    </div>
                  </div>
                </div>
              <?php endif; ?>
            </div>

            <?php if (empty($customers)): ?>
              <div class="stat-card text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h4>No customers found</h4>
                <p class="text-muted">
                  <?= !empty($_GET['search']) ? 'No customers match your search criteria.' : 'No customers registered yet.' ?>
                </p>
              </div>
            <?php else: ?>
              <?php foreach ($customers as $customer): ?>
                <div class="customer-card">
                  <div class="card-body p-4">
                    <div class="row align-items-center">
                      <div class="col-md-1">
                        <div class="d-flex align-items-center gap-2">
                          <input type="checkbox" class="form-check-input customer-checkbox" value="<?= $customer['id'] ?>"
                            onchange="updateBulkActions()">
                          <div class="customer-avatar">
                            <?= strtoupper(substr($customer['fullname'], 0, 1)) ?>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-2">
                        <div>
                          <h6 class="mb-1 fw-bold"><?= htmlspecialchars($customer['fullname']) ?></h6>
                          <small class="text-muted">@<?= htmlspecialchars($customer['username']) ?></small>
                        </div>
                      </div>
                      <div class="col-md-2">
                        <small class="text-muted d-block">Email</small>
                        <span><?= htmlspecialchars($customer['email']) ?></span>
                      </div>
                      <div class="col-md-2">
                        <small class="text-muted d-block">Phone</small>
                        <span><?= htmlspecialchars($customer['phone']) ?></span>
                      </div>
                      <div class="col-md-2 text-center">
                        <div class="row">
                          <div class="col-6">
                            <h5 class="mb-0 text-primary"><?= $customer['total_orders'] ?></h5>
                            <small class="text-muted">Orders</small>
                          </div>
                          <div class="col-6">
                            <h5 class="mb-0 text-success">IDR <?= number_format($customer['total_spent'], 0, ',', '.') ?></h5>
                            <small class="text-muted">Spent</small>
                          </div>
                        </div>
                      </div>
                      <div class="col-md-3 text-end">
                        <div class="btn-group">
                          <button class="btn btn-outline-primary quick-action-btn" data-bs-toggle="collapse"
                            data-bs-target="#customer-<?= $customer['id'] ?>">
                            <i class="fas fa-eye"></i> Details
                          </button>
                          <button class="btn btn-outline-warning quick-action-btn"
                            onclick="resetCustomerPassword(<?= $customer['id'] ?>, '<?= htmlspecialchars($customer['fullname']) ?>')">
                            <i class="fas fa-key"></i> Reset
                          </button>
                        </div>
                      </div>
                    </div>

                    <div class="collapse mt-3" id="customer-<?= $customer['id'] ?>">
                      <div class="border-top pt-3">
                        <div class="row">
                          <div class="col-md-6">
                            <h6><i class="fas fa-user me-2"></i>Personal Information:</h6>
                            <div class="bg-light p-3 rounded">
                              <p class="mb-2"><strong>Full Name:</strong> <?= htmlspecialchars($customer['fullname']) ?></p>
                              <p class="mb-2"><strong>Username:</strong> <?= htmlspecialchars($customer['username']) ?></p>
                              <p class="mb-2"><strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?></p>
                              <p class="mb-2"><strong>Phone:</strong> <?= htmlspecialchars($customer['phone']) ?></p>
                              <p class="mb-0"><strong>Address:</strong>
                                <?= !empty($customer['address']) ? htmlspecialchars($customer['address']) : 'Not provided' ?>
                              </p>
                            </div>
                          </div>

                          <div class="col-md-6">
                            <h6><i class="fas fa-chart-line me-2"></i>Order Statistics:</h6>
                            <div class="bg-light p-3 rounded">
                              <div class="row text-center">
                                <div class="col-4">
                                  <h4 class="text-primary mb-1"><?= $customer['total_orders'] ?></h4>
                                  <small class="text-muted">Total Orders</small>
                                </div>
                                <div class="col-4">
                                  <h4 class="text-success mb-1">IDR
                                    <?= number_format($customer['total_spent'], 0, ',', '.') ?>
                                  </h4>
                                  <small class="text-muted">Total Spent</small>
                                </div>
                                <div class="col-4">
                                  <h4 class="text-info mb-1">IDR
                                    <?= $customer['total_orders'] > 0 ? number_format($customer['total_spent'] / $customer['total_orders'], 0, ',', '.') : '0' ?>
                                  </h4>
                                  <small class="text-muted">Avg. Order</small>
                                </div>
                              </div>

                              <?php if ($customer['last_order_date']): ?>
                                <hr>
                                <p class="mb-0">
                                  <strong>Last Order:</strong>
                                  <span
                                    class="text-muted"><?= date('M d, Y H:i', strtotime($customer['last_order_date'])) ?></span>
                                </p>
                              <?php endif; ?>
                            </div>

                            <?php if ($customer['total_orders'] > 0): ?>
                              <div class="mt-3">
                                <a href="dashboard.php?page=orders&customer=<?= $customer['id'] ?>"
                                  class="btn btn-outline-primary btn-sm">
                                  <i class="fas fa-receipt me-1"></i>View Orders
                                </a>
                              </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        <?php elseif ($current_page === 'orders'): ?>
          <div class="page-header">
            <div class="container-fluid">
              <h1 class="page-title">Orders Management</h1>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php" class="text-white">Dashboard</a></li>
                  <li class="breadcrumb-item active">Orders</li>
                </ol>
              </nav>
            </div>
          </div>

          <div class="container-fluid">
            <div class="filter-section">
              <div class="row align-items-center">
                <div class="col-md-6">
                  <h5 class="mb-0">Orders Overview</h5>
                  <small class="text-muted">Total Orders: <?= count($transactions) ?></small>
                </div>
                <div class="col-md-6">
                  <select class="form-select" id="status-filter" onchange="filterOrders()">
                    <option value="all" <?= ($_GET['status'] ?? 'all') === 'all' ? 'selected' : '' ?>>All Orders</option>
                    <option value="pending" <?= ($_GET['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="confirmed" <?= ($_GET['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                    <option value="processing" <?= ($_GET['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                    <option value="shipped" <?= ($_GET['status'] ?? '') === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                    <option value="delivered" <?= ($_GET['status'] ?? '') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                    <option value="cancelled" <?= ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                  </select>
                </div>
              </div>
            </div>

            <?php if (empty($transactions)): ?>
              <div class="stat-card text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h4>No orders found</h4>
                <p class="text-muted">No orders match the current filter.</p>
              </div>
            <?php else: ?>
              <?php foreach ($transactions as $transaction): ?>
                <?php
                $sql_details = "SELECT d.*, p.name, p.price, p.photo 
                               FROM detail d 
                               JOIN product p ON d.id_product = p.id 
                               WHERE d.id_transaction = '{$transaction['id_transaction']}'";
                $details_result = mysqli_query($db, $sql_details);
                $order_items = [];
                while ($row = mysqli_fetch_assoc($details_result)) {
                  $order_items[] = $row;
                }

                $status_class = 'status-' . ($transaction['status'] ?? 'pending');
                ?>

                <div class="order-card">
                  <div class="order-header">
                    <div class="row align-items-center">
                      <div class="col-md-2">
                        <h6 class="mb-1 fw-bold">Invoice #<?= str_pad($transaction['id_transaction'], 6, '0', STR_PAD_LEFT) ?></h6>
                        <small class="text-muted"><?= date('M d, Y H:i', strtotime($transaction['date'])) ?></small>
                      </div>
                      <div class="col-md-3">
                        <h6 class="mb-1"><?= htmlspecialchars($transaction['fullname']) ?></h6>
                        <small class="text-muted"><?= htmlspecialchars($transaction['email']) ?></small>
                      </div>
                      <div class="col-md-2 text-center">
                        <h5 class="mb-0 text-primary">IDR <?= number_format($transaction['total_price'], 0, ',', '.') ?></h5>
                        <small class="text-muted"><?= ucwords(str_replace('_', ' ', $transaction['payment_method'])) ?></small>
                      </div>
                      <div class="col-md-2 text-center">
                        <span class="status-badge <?= $status_class ?>">
                          <?= ucfirst($transaction['status'] ?? 'Pending') ?>
                        </span>
                      </div>
                      <div class="col-md-3 text-end">
                        <button class="btn btn-outline-primary quick-action-btn" data-bs-toggle="collapse"
                          data-bs-target="#order-<?= $transaction['id_transaction'] ?>">
                          <i class="fas fa-eye"></i> Details
                        </button>
                      </div>
                    </div>
                  </div>

                  <div class="collapse" id="order-<?= $transaction['id_transaction'] ?>">
                    <div class="order-content">
                      <div class="row">
                        <div class="col-md-6">
                          <h6><i class="fas fa-user me-2"></i>Customer Details:</h6>
                          <div class="bg-light p-3 rounded">
                            <p class="mb-1"><strong>Name:</strong> <?= htmlspecialchars($transaction['fullname']) ?></p>
                            <p class="mb-1"><strong>Address:</strong> <?= htmlspecialchars($transaction['address']) ?></p>
                            <p class="mb-1"><strong>Phone:</strong> <?= htmlspecialchars($transaction['phone']) ?></p>
                            <p class="mb-0"><strong>Email:</strong> <?= htmlspecialchars($transaction['email']) ?></p>
                          </div>
                        </div>

                        <div class="col-md-6">
                          <h6><i class="fas fa-shopping-bag me-2"></i>Order Items:</h6>
                          <?php if (!empty($order_items) && is_array($order_items)): ?>
                            <div class="order-items-preview">
                              <?php foreach ($order_items as $item): ?>
                                <div class="invoice-item mb-3">
                                  <div class="row align-items-center">
                                    <div class="col-md-6">
                                      <div class="d-flex align-items-center">
                                        <img src="../images/<?= $item['photo'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" 
                                             class="me-3" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                        <div>
                                          <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                          <small class="text-muted">IDR <?= number_format($item['price'], 0, ',', '.') ?> each</small>
                                        </div>
                                      </div>
                                    </div>
                                    <div class="col-md-3 text-center">
                                      <span class="fw-bold"><?= $item['quantity'] ?>x</span>
                                    </div>
                                    <div class="col-md-3 text-end">
                                      <h6 class="mb-0 text-primary">IDR <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?></h6>
                                    </div>
                                  </div>
                                </div>
                              <?php endforeach; ?>
                            </div>
                          <?php else: ?>
                            <div class="bg-light p-3 rounded">
                              <p class="text-muted mb-0">No items found for this order.</p>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>

                      <div class="row mt-4">
                        <div class="col-md-12">
                          <h6><i class="fas fa-cog me-2"></i>Order Management:</h6>
                          <div class="bg-light p-3 rounded">
                            <form method="POST" class="d-flex align-items-center gap-3">
                              <input type="hidden" name="order_id" value="<?= $transaction['id_transaction'] ?>">
                              <div class="flex-grow-1">
                                <label class="form-label mb-1">Update Status:</label>
                                <select name="new_status" class="form-select">
                                  <option value="pending" <?= ($transaction['status'] ?? 'pending') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                  <option value="confirmed" <?= ($transaction['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                  <option value="processing" <?= ($transaction['status'] ?? '') === 'processing' ? 'selected' : '' ?>>Processing</option>
                                  <option value="shipped" <?= ($transaction['status'] ?? '') === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                  <option value="delivered" <?= ($transaction['status'] ?? '') === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                  <option value="cancelled" <?= ($transaction['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                </select>
                              </div>
                              <div class="align-self-end">
                                <button type="submit" name="update_order_status" class="btn btn-primary">
                                  <i class="fas fa-save me-1"></i>Update
                                </button>
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

        <?php elseif ($current_page === 'products'): ?>
          <div class="page-header">
            <div class="container-fluid">
              <h1 class="page-title">Product Management</h1>
              <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                  <li class="breadcrumb-item"><a href="dashboard.php" class="text-white">Dashboard</a></li>
                  <li class="breadcrumb-item active">Products</li>
                </ol>
              </nav>
            </div>
          </div>

          <div class="container-fluid">
            <?php if ($action == "edit" || $action == "add" || empty($action)): ?>
              <div class="product-form">
                <h5 class="mb-4">
                  <i class="fas fa-<?= $action == "edit" ? "edit" : "plus" ?> me-2"></i>
                  <?= $action == "edit" ? "Edit Product" : "Add New Product" ?>
                </h5>
                
                <form method="POST" enctype="multipart/form-data">
                  <input type="hidden" name="product_action" value="1">
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" value="<?= htmlspecialchars($name) ?>" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Price (IDR) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="price" value="<?= $price ?>" min="0" step="0.01" required>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="stock" value="<?= $stock ?>" min="0" required>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="category" required>
                          <option value="">Select Category</option>
                          <?php
                          $categories_result = mysqli_query($db, "SELECT * FROM category ORDER BY name");
                          while ($cat = mysqli_fetch_assoc($categories_result)): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category == $cat['id'] ? 'selected' : '' ?>>
                              <?= htmlspecialchars($cat['name']) ?>
                            </option>
                          <?php endwhile; ?>
                        </select>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row">
                    <div class="col-md-6">
                      <div class="mb-3">
                        <label class="form-label">Product Image <?= $action != "edit" ? '<span class="text-danger">*</span>' : '(Leave empty to keep current)' ?></label>
                        <input type="file" class="form-control" name="photo" accept="image/*" <?= $action != "edit" ? 'required' : '' ?>>
                        <?php if ($action == "edit" && !empty($photo)): ?>
                          <small class="text-muted">Current: <?= htmlspecialchars($photo) ?></small>
                        <?php endif; ?>
                      </div>
                    </div>
                    <div class="col-md-6">
                      <?php if ($action == "edit" && !empty($photo)): ?>
                        <div class="mb-3">
                          <label class="form-label">Current Image</label>
                          <div>
                            <img src="../images/<?= $photo ?>" alt="Current product image" 
                                 style="max-width: 100px; max-height: 100px; object-fit: cover; border-radius: 8px;">
                          </div>
                        </div>
                      <?php endif; ?>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label">Product Description</label>
                    <textarea class="form-control" name="description" rows="4" placeholder="Enter product description..."><?= htmlspecialchars($description) ?></textarea>
                  </div>
                  
                  <div class="d-flex justify-content-end gap-2">
                    <a href="dashboard.php?page=products" class="btn btn-secondary">
                      <i class="fas fa-times me-1"></i>Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-save me-1"></i><?= $action == "edit" ? "Update Product" : "Add Product" ?>
                    </button>
                  </div>
                </form>
              </div>
            <?php endif; ?>

            <?php if ($action != "edit" && $action != "add"): ?>
              <div class="filter-section">
                <div class="row align-items-center">
                  <div class="col-md-6">
                    <h5 class="mb-0">Products Overview</h5>
                    <small class="text-muted">Manage your product catalog</small>
                  </div>
                  <div class="col-md-6 text-end">
                    <a href="dashboard.php?page=products&action=add" class="btn btn-primary">
                      <i class="fas fa-plus me-1"></i>Add New Product
                    </a>
                  </div>
                </div>
              </div>

              <?php
              $products_result = mysqli_query($db, "SELECT p.*, c.name as category_name FROM product p LEFT JOIN category c ON p.id_category = c.id ORDER BY p.id DESC");
              $products = [];
              while ($row = mysqli_fetch_assoc($products_result)) {
                $products[] = $row;
              }
              ?>

              <?php if (empty($products)): ?>
                <div class="stat-card text-center py-5">
                  <i class="fas fa-box fa-3x text-muted mb-3"></i>
                  <h4>No products found</h4>
                  <p class="text-muted">Start by adding your first product.</p>
                  <a href="dashboard.php?page=products&action=add" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i>Add Product
                  </a>
                </div>
              <?php else: ?>
                <div class="table-responsive">
                  <table class="table">
                    <thead>
                      <tr>
                        <th>Image</th>
                        <th>Product Details</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($products as $product): ?>
                        <tr>
                          <td>
                            <img src="../images/<?= $product['photo'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px;">
                          </td>
                          <td>
                            <h6 class="mb-1"><?= htmlspecialchars($product['name']) ?></h6>
                            <?php if (!empty($product['description'])): ?>
                              <small class="text-muted"><?= htmlspecialchars(substr($product['description'], 0, 100)) ?><?= strlen($product['description']) > 100 ? '...' : '' ?></small>
                            <?php endif; ?>
                          </td>
                          <td>
                            <span class="badge bg-secondary"><?= htmlspecialchars($product['category_name'] ?? 'No Category') ?></span>
                          </td>
                          <td>
                            <h6 class="mb-0 text-primary">IDR <?= number_format($product['price'], 0, ',', '.') ?></h6>
                          </td>
                          <td>
                            <span class="badge bg-<?= $product['stock'] > 0 ? ($product['stock'] > 10 ? 'success' : 'warning') : 'danger' ?>">
                              <?= $product['stock'] ?> units
                            </span>
                          </td>
                          <td>
                            <?php
                            $has_status = isset($product['status']);
                            $status = $has_status ? $product['status'] : 'active';
                            $status_class = $status === 'active' ? 'success' : 'secondary';
                            ?>
                            <span class="badge bg-<?= $status_class ?>">
                              <?= ucfirst($status) ?>
                            </span>
                          </td>
                          <td>
                            <div class="btn-group">
                              <a href="dashboard.php?page=products&action=edit&id=<?= $product['id'] ?>" 
                                 class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                              </a>
                              <?php if ($has_status): ?>
                                <a href="dashboard.php?page=products&action=toggle_status&id=<?= $product['id'] ?>" 
                                   class="btn btn-outline-warning btn-sm">
                                  <i class="fas fa-<?= $status === 'active' ? 'eye-slash' : 'eye' ?>"></i>
                                </a>
                              <?php endif; ?>
                              <a href="dashboard.php?page=products&action=delete&id=<?= $product['id'] ?>" 
                                 class="btn btn-outline-danger btn-sm btn-delete-product">
                                <i class="fas fa-trash"></i>
                              </a>
                            </div>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            <?php endif; ?>
          </div>

        <?php endif; ?>

      </main>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="../assets/dist/js/bootstrap.bundle.min.js"></script>

  <!-- Custom JavaScript -->
  <script>
    // Alert handling
    <?php if (isset($_SESSION['alert'])): ?>
      Swal.fire({
        title: 'Success!',
        text: '<?= $_SESSION['alert'] ?>',
        icon: 'success',
        confirmButtonColor: '#f96d00'
      });
      <?php unset($_SESSION['alert']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      Swal.fire({
        title: 'Error!',
        text: '<?= $_SESSION['error'] ?>',
        icon: 'error',
        confirmButtonColor: '#f96d00'
      });
      <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    // Bulk reset results
    <?php if (isset($_SESSION['bulk_reset_results'])): ?>
      let resetResults = <?= json_encode($_SESSION['bulk_reset_results']) ?>;
      let successCount = resetResults.filter(r => r.success).length;
      let failCount = resetResults.filter(r => !r.success).length;
      
      let resultText = `Successfully reset ${successCount} password(s)`;
      if (failCount > 0) {
        resultText += `, ${failCount} failed`;
      }
      
      let detailsHtml = '<div class="text-start mt-3"><small>';
      resetResults.forEach(result => {
        if (result.success) {
          detailsHtml += `<strong>${result.name}:</strong> ${result.password}<br>`;
        } else {
          detailsHtml += `<strong>${result.name}:</strong> Failed<br>`;
        }
      });
      detailsHtml += '</small></div>';
      
      Swal.fire({
        title: 'Bulk Password Reset Complete',
        html: resultText + detailsHtml,
        icon: successCount > 0 ? 'success' : 'error',
        confirmButtonColor: '#f96d00',
        width: '600px'
      });
      <?php unset($_SESSION['bulk_reset_results']); ?>
    <?php endif; ?>

    // Filter functions
    function filterOrders() {
      const status = document.getElementById('status-filter').value;
      window.location.href = `dashboard.php?page=orders&status=${status}`;
    }

    function filterMessages() {
      const status = document.getElementById('status-filter').value;
      window.location.href = `dashboard.php?page=messages&status=${status}`;
    }

    // Customer bulk actions
    function toggleSelectAll() {
      const checkboxes = document.querySelectorAll('.customer-checkbox');
      const allChecked = Array.from(checkboxes).every(cb => cb.checked);
      
      checkboxes.forEach(cb => {
        cb.checked = !allChecked;
      });
      
      updateBulkActions();
    }

    function updateBulkActions() {
      const checkedBoxes = document.querySelectorAll('.customer-checkbox:checked');
      const bulkBtn = document.getElementById('bulk-reset-btn');
      const countSpan = document.getElementById('selected-count');
      
      if (checkedBoxes.length > 0) {
        bulkBtn.disabled = false;
        countSpan.textContent = checkedBoxes.length;
      } else {
        bulkBtn.disabled = true;
        countSpan.textContent = '0';
      }
    }

    function bulkResetPassword() {
      const checkedBoxes = document.querySelectorAll('.customer-checkbox:checked');
      const customerIds = Array.from(checkedBoxes).map(cb => cb.value);
      
      if (customerIds.length === 0) {
        Swal.fire('Error!', 'Please select customers first.', 'error');
        return;
      }
      
      Swal.fire({
        title: 'Confirm Bulk Password Reset',
        text: `Are you sure you want to reset passwords for ${customerIds.length} customer(s)?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f96d00',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Reset Passwords'
      }).then((result) => {
        if (result.isConfirmed) {
          const form = document.createElement('form');
          form.method = 'POST';
          form.style.display = 'none';
          
          customerIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'customer_ids[]';
            input.value = id;
            form.appendChild(input);
          });
          
          const submitInput = document.createElement('input');
          submitInput.type = 'hidden';
          submitInput.name = 'bulk_password_reset';
          submitInput.value = '1';
          form.appendChild(submitInput);
          
          document.body.appendChild(form);
          form.submit();
        }
      });
    }

    function resetCustomerPassword(customerId, customerName) {
      Swal.fire({
        title: 'Reset Password',
        text: `Are you sure you want to reset the password for ${customerName}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f96d00',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Reset Password'
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = `dashboard.php?page=customers&action=reset_password&id=${customerId}`;
        }
      });
    }

    // Message bulk actions
    function toggleSelectAllMessages() {
      const checkboxes = document.querySelectorAll('.message-checkbox');
      const allChecked = Array.from(checkboxes).every(cb => cb.checked);
      
      checkboxes.forEach(cb => {
        cb.checked = !allChecked;
      });
      
      updateMessageBulkActions();
    }

    function updateMessageBulkActions() {
      const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
      const bulkBtn = document.getElementById('bulk-read-btn');
      const countSpan = document.getElementById('selected-messages-count');
      
      if (checkedBoxes.length > 0) {
        bulkBtn.disabled = false;
        countSpan.textContent = checkedBoxes.length;
      } else {
        bulkBtn.disabled = true;
        countSpan.textContent = '0';
      }
    }

    function bulkMarkAsRead() {
      const checkedBoxes = document.querySelectorAll('.message-checkbox:checked');
      const messageIds = Array.from(checkedBoxes).map(cb => cb.value);
      
      if (messageIds.length === 0) {
        Swal.fire('Error!', 'Please select messages first.', 'error');
        return;
      }
      
      const form = document.createElement('form');
      form.method = 'POST';
      form.style.display = 'none';
      
      messageIds.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'message_ids[]';
        input.value = id;
        form.appendChild(input);
      });
      
      const submitInput = document.createElement('input');
      submitInput.type = 'hidden';
      submitInput.name = 'bulk_mark_read';
      submitInput.value = '1';
      form.appendChild(submitInput);
      
      document.body.appendChild(form);
      form.submit();
    }

    // Delete confirmations
    document.querySelectorAll('.btn-delete-product').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.href;
        
        Swal.fire({
          title: 'Delete Product',
          text: 'Are you sure you want to delete this product? This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, Delete'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = href;
          }
        });
      });
    });

    document.querySelectorAll('.btn-delete-message').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.href;
        
        Swal.fire({
          title: 'Delete Message',
          text: 'Are you sure you want to delete this message? This action cannot be undone.',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#dc3545',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, Delete'
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = href;
          }
        });
      });
    });
  </script>

</body>
</html>