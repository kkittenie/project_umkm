<?php
include "config.php";
session_start();

if (!isset($_SESSION["is_login"])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}
$cart_count = count($_SESSION['cart']);

$user_id = $_SESSION["user_id"];
$result = mysqli_query($db, "SELECT fullname, username, phone, address, profile_picture FROM users WHERE id = '$user_id'");
if ($result) {
    $user_data = mysqli_fetch_assoc($result);
}

$orders_result = mysqli_query($db, "SELECT id_transaction as id, date as order_date, total_price, 
                                           status as order_status, payment_method
                                    FROM transaction
                                    WHERE id_user = '$user_id'
                                    ORDER BY date DESC");

$user_orders = [];
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $order_id = $row['id'];
        
        $detail_result = mysqli_query($db, "SELECT SUM(amount) AS total_items FROM detail WHERE id_transaction = '$order_id'");
        $detail_row = mysqli_fetch_assoc($detail_result);
        $row['total_items'] = $detail_row['total_items'] ?? 0;

        $items_result = mysqli_query($db, "SELECT d.amount, p.name, p.price 
                                          FROM detail d 
                                          JOIN product p ON d.id_product = p.id 
                                          WHERE d.id_transaction = '$order_id' 
                                          LIMIT 3");
        $order_items = [];
        while ($item = mysqli_fetch_assoc($items_result)) {
            $order_items[] = [
                'name' => $item['name'],
                'quantity' => $item['amount'],
                'price' => $item['price']
            ];
        }
        $row['order_items'] = $order_items;
        $user_orders[] = $row;
    }
}
function getStatusBadgeClass($status) {
    switch (strtolower($status)) {
        case 'pending':
            return 'badge-warning';
        case 'processing':
        case 'confirmed':
            return 'badge-info';
        case 'shipped':
            return 'badge-primary';
        case 'delivered':
        case 'completed':
            return 'badge-success';
        case 'cancelled':
            return 'badge-danger';
        default:
            return 'badge-secondary';
    }
}
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}
function formatDate($date) {
    return date('d M Y, H:i', strtotime($date));
}

mysqli_close($db);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>Rasa. - My Account</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link
        href="https://fonts.googleapis.com/css2?family=Spectral:ital,wght@0,200;0,300;0,400;0,500;0,700;0,800;1,200;1,300;1,400;1,500;1,700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/animate.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/owl.theme.default.min.css">
    <link rel="stylesheet" href="css/magnific-popup.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/account.css">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

</head>

<body>

    <?php include "layout/header.php" ?>

    <section class="hero-wrap hero-wrap-2" style="background-image: url('images/bg2.jpg');"
        data-stellar-background-ratio="0.5">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text align-items-end justify-content-center">
                <div class="col-md-9 ftco-animate mb-5 text-center">
                    <p class="breadcrumbs mb-0"><span class="mr-2"><a href="index.php">Home <i
                                    class="fa fa-chevron-right"></i></a></span> <span>My Orders <i
                                class="fa fa-chevron-right"></i></span></p>
                    <h2 class="mb-0 bread">My Orders</h2>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10 ftco-animate">

                    <div class="tab-pane fade show active" id="orders" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12">
                                <h4 class="mb-4">All Orders</h4>

                                <?php if (!empty($user_orders)): ?>
                                    <?php foreach ($user_orders as $order): ?>
                                        <div class="card mb-3 order-card">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h5 class="mb-1">Order
                                                            #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                                                        <p class="mb-1 order-meta">
                                                            <i class="fa fa-calendar mr-1"></i>
                                                            Date:
                                                            <?= date('M d, Y H:i', strtotime($order['order_date'])) ?>
                                                        </p>
                                                        <p class="mb-1 order-meta">
                                                            <i class="fa fa-shopping-bag mr-1"></i>
                                                            Items: <?= $order['total_items'] ?>
                                                        </p>
                                                        <p class="mb-1 order-meta">
                                                            <i class="fa fa-credit-card mr-1"></i>
                                                            Payment:
                                                            <?= ucwords(str_replace('_', ' ', $order['payment_method'])) ?>
                                                        </p>
                                                        <span
                                                            class="badge status-badge <?= getStatusBadgeClass($order['order_status']) ?>">
                                                            <?= ucfirst($order['order_status']) ?>
                                                        </span>
                                                    </div>
                                                    <div class="text-right">
                                                        <h5 class="mb-2 order-total">IDR
                                                            <?= number_format($order['total_price'], 0, ',', '.') ?>
                                                        </h5>
                                                        <div class="btn-group-vertical btn-group-sm">
                                                            <a href="invoice.php?order_id=<?= $order['id'] ?>"
                                                                class="btn btn-outline-primary btn-sm">
                                                                <i class="fa fa-file-text-o mr-1"></i>View Invoice
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>

                                                <?php if (!empty($order['order_items'])): ?>
                                                    <hr class="my-3">
                                                    <div class="order-items-preview">
                                                        <h6 class="text-muted mb-2">Items ordered:</h6>
                                                        <div class="row">
                                                            <?php
                                                            $preview_count = 0;
                                                            foreach ($order['order_items'] as $item):
                                                                if ($preview_count >= 3)
                                                                    break;
                                                                ?>
                                                                <div class="col-md-4">
                                                                    <small class="d-block">
                                                                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                                                                        <br>
                                                                        <span class="text-muted">
                                                                            Qty: <?= $item['quantity'] ?> × IDR
                                                                            <?= number_format($item['price'], 0, ',', '.') ?>
                                                                        </span>
                                                                    </small>
                                                                </div>
                                                                <?php
                                                                $preview_count++;
                                                            endforeach;
                                                            ?>
                                                            <?php if (count($order['order_items']) > 3): ?>
                                                                <div class="col-md-12">
                                                                    <small class="text-muted">
                                                                        <i class="fa fa-plus mr-1"></i>
                                                                        And <?= count($order['order_items']) - 3 ?> more items
                                                                    </small>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>

                                <?php else: ?>
                                    <div class="text-center mt-5">
                                        <i class="fa fa-shopping-bag fa-5x text-muted mb-3"></i>
                                        <h4 class="text-muted">No orders yet</h4>
                                        <p class="text-muted">Start exploring our delicious menu and place your
                                            first order!</p>
                                        <a href="product.php" class="btn btn-primary py-3 px-4">
                                            <i class="fa fa-utensils mr-2"></i>Browse Menu
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include "layout/footer.php" ?>

    <!-- loader -->
    <div id="ftco-loader" class="show fullscreen">
        <svg class="circular" width="48px" height="48px">
            <circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
            <circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10"
                stroke="#F96D00" />
        </svg>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/jquery-migrate-3.0.1.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.easing.1.3.js"></script>
    <script src="js/jquery.waypoints.min.js"></script>
    <script src="js/jquery.stellar.min.js"></script>
    <script src="js/owl.carousel.min.js"></script>
    <script src="js/jquery.magnific-popup.min.js"></script>
    <script src="js/jquery.animateNumber.min.js"></script>
    <script src="js/scrollax.min.js"></script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBVWaKrjvy3MaE7SQ74_uJiULgl1JY0H2s&sensor=false"></script>
    <script src="js/google-map.js"></script>
    <script src="js/main.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            loadCartDropdown();

            $('.add-to-cart-btn').click(function (e) {
                e.preventDefault();
                var productId = $(this).data('product-id');

                $.post('cart_handler.php', {
                    action: 'add_to_cart',
                    product_id: productId,
                    quantity: 1
                }, function (response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        $('#cart-count').text(data.cart_count);
                        loadCartDropdown();
                        Swal.fire({
                            icon: 'success',
                            title: 'Added to Cart!',
                            text: data.message,
                            timer: 2000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: data.message,
                            confirmButtonColor: '#F96D00'
                        });
                    }
                });
            });


            function loadCartDropdown() {
                $.post('cart_handler.php', {
                    action: 'get_cart'
                }, function (response) {
                    var data = JSON.parse(response);
                    var cartDropdown = $('#cart-dropdown');
                    cartDropdown.empty();

                    if (data.cart.length > 0) {
                        data.cart.forEach(function (item) {
                            cartDropdown.append(`
                            <div class="dropdown-item d-flex align-items-start">
                                <div class="img" style="background-image: url(images/${item.photo});"></div>
                                <div class="text pl-3">
                                    <h4>${item.name}</h4>
                                    <p class="mb-0"><a href="#" class="price">IDR ${Number(item.price).toLocaleString('id-ID')}
                                <span class="quantity ml-3">Quantity: ${item.quantity.toString().padStart(2, '0')}</span></p>
                                </div>
                            </div>
                        `);
                        });
                        cartDropdown.append(`
                        <a class="dropdown-item text-center btn-link d-block w-100" href="cart.php">
                            View All
                            <span class="ion-ios-arrow-round-forward"></span>
                        </a>
                    `);
                    } else {
                        cartDropdown.append('<div class="dropdown-item text-center">Your cart is empty</div>');
                    }
                });
            }
        });


        console.log('jQuery version:', $.fn.jquery);

        loadCartDropdown();

        $('.add-to-cart-btn').click(function (e) {
            e.preventDefault();
            var productId = $(this).data('product-id');
            console.log('Add to cart clicked for product:', productId);

            $.post('cart_handler.php', {
                action: 'add_to_cart',
                product_id: productId,
                quantity: 1
            }, function (response) {
                console.log('Cart response:', response);
                var data = JSON.parse(response);
                if (data.success) {
                    $('#cart-count').text(data.cart_count);
                    loadCartDropdown();
                    Swal.fire({
                        icon: 'success',
                        title: 'Added to Cart!',
                        text: data.message,
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message,
                        confirmButtonColor: '#F96D00'
                    });
                }
            }).fail(function (xhr, status, error) {
                console.error('Cart request failed:', status, error);
                console.error('Response:', xhr.responseText);
            });
        });


        function loadCartDropdown() {
            console.log('Loading cart dropdown...');
            $.post('cart_handler.php', {
                action: 'get_cart'
            }, function (response) {
                console.log('Cart dropdown response:', response);
                var data = JSON.parse(response);
                var cartDropdown = $('#cart-dropdown');
                cartDropdown.empty();

                if (data.cart.length > 0) {
                    data.cart.forEach(function (item) {
                        cartDropdown.append(`
                    <div class="dropdown-item d-flex align-items-start">
                        <div class="img" style="background-image: url(images/${item.photo});"></div>
                        <div class="text pl-3">
                            <h4>${item.name}</h4>
                            <p class="mb-0"><a href="#" class="price">IDR ${item.price}K</a><span class="quantity ml-3">Quantity: ${item.quantity.toString().padStart(2, '0')}</span></p>
                        </div>
                    </div>
                `);
                    });
                    cartDropdown.append(`
                <a class="dropdown-item text-center btn-link d-block w-100" href="cart.php">
                    View All
                    <span class="ion-ios-arrow-round-forward"></span>
                </a>
            `);
                } else {
                    cartDropdown.append('<div class="dropdown-item text-center">Your cart is empty</div>');
                }
            }).fail(function (xhr, status, error) {
                console.error('Failed to load cart dropdown:', status, error);
            });
        }

    </script>
</body>

</html>