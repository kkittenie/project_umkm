<?php
session_start();
include 'config.php';

// Get order ID from URL
$transaction_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($transaction_id <= 0) {
    header('Location: index.php');
    exit();
}

// Fetch transaction details
$sql_transaction = "SELECT t.*, u.fullname, u.email, u.phone, u.address 
                   FROM transaction t 
                   JOIN users u ON t.id_user = u.id 
                   WHERE t.id_transaction = '$transaction_id' AND t.id_user = '{$_SESSION['user_id']}'";
$result = mysqli_query($db, $sql_transaction);

if (mysqli_num_rows($result) == 0) {
    header('Location: index.php');
    exit();
}

$transaction = mysqli_fetch_assoc($result);

// Fetch order items
$sql_details = "SELECT d.*, p.name, p.price, p.photo 
               FROM detail d 
               JOIN product p ON d.id_product = p.id 
               WHERE d.id_transaction = '$transaction_id'";
$details_result = mysqli_query($db, $sql_details);
$order_items = [];
while ($row = mysqli_fetch_assoc($details_result)) {
    $order_items[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>Rasa. - Invoice #<?= $transaction_id ?></title>
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
    <link rel="stylesheet" href="css/flaticon.css">
    <link rel="stylesheet" href="css/style.css?v=1.0">
</head>

<body>
    <div class="no-print">
        <?php include "layout/header.php" ?>

        <section class="hero-wrap hero-wrap-2" style="background-image: url('images/bg2.jpg');"
            data-stellar-background-ratio="0.5">
            <div class="overlay"></div>
            <div class="container">
                <div class="row no-gutters slider-text align-items-end justify-content-center">
                    <div class="col-md-9 ftco-animate mb-5 text-center">
                        <p class="breadcrumbs mb-0">
                            <span class="mr-2"><a href="index.php">Home <i class="fa fa-chevron-right"></i></a></span>
                            <span>Invoice <i class="fa fa-chevron-right"></i></span>
                        </p>
                        <h2 class="mb-0 bread">Invoice</h2>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10 ftco-animate">
                    <!-- Invoice Header -->
                    <div class="invoice-header d-flex justify-content-between align-items-start mb-4">
                        <div>
                            <h1 class="h2 mb-0">INVOICE</h1>
                            <p class="text-muted">Invoice #<?= str_pad($transaction_id, 6, '0', STR_PAD_LEFT) ?></p>
                        </div>
                        <div class="text-right">
                            <h3 class="text-primary mb-0">Rasa.</h3>
                            <p class="mb-0">Indonesian Cuisine</p>
                            <small class="text-muted">Date:
                                <?= date('M d, Y', strtotime($transaction['date'])) ?></small>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <!-- Billing Information -->
                            <div class="invoice-details mb-4">
                                <h5 class="mb-3">Bill To:</h5>
                                <p class="mb-1"><strong><?= htmlspecialchars($transaction['fullname']) ?></strong></p>
                                <p class="mb-1"><?= htmlspecialchars($transaction['address']) ?></p>
                                <p class="mb-1">Phone: <?= htmlspecialchars($transaction['phone']) ?></p>
                                <p class="mb-0">Email: <?= htmlspecialchars($transaction['email']) ?></p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Order Information -->
                            <div class="invoice-details mb-4">
                                <h5 class="mb-3">Order Information:</h5>
                                <p class="mb-1"><strong>Order Date:</strong>
                                    <?= date('M d, Y H:i', strtotime($transaction['date'])) ?></p>
                                <p class="mb-1"><strong>Payment Method:</strong>
                                    <?= ucwords(str_replace('_', ' ', $transaction['payment_method'])) ?></p>
                                <span class="<?= ($transaction['status']) ?>"><?= ucfirst($transaction['status']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <?php if (!empty($order_items) && is_array($order_items)): ?>
                        <div class="invoice-details mb-4">
                            <h5 class="mb-4">Order Items:</h5>
                            <?php foreach ($order_items as $item): ?>
                                <div class="invoice-item mb-3 p-3" style="border: 1px solid #dee2e6; border-radius: 5px;">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <div class="img mr-3"
                                                    style="width: 60px; height: 60px; background-image: url('images/<?= $item['photo'] ?>'); background-size: cover; background-position: center; border-radius: 5px;">
                                                </div>
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                                    <small class="text-muted">Quantity: <?= $item['amount'] ?></small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <span>IDR <?= number_format($item['price'], 0, ',', '.') ?></span>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <strong>IDR
                                                <?= number_format($item['price'] * $item['amount'], 0, ',', '.') ?></strong>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- Order Total -->
                        <div class="invoice-total">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="no-print">
                                        <button onclick="window.print()" class="btn btn-primary mr-2 py-3 px-4">
                                            <i class="fa fa-print"></i> Print Invoice
                                        </button>
                                        <a href="index.php" class="btn btn-secondary py-3 px-4">Continue Shopping</a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span>
                                                <span>IDR
                                                    <?= number_format($transaction['total_price'], 0, ',', '.') ?></span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>Total:</strong>
                                                <strong class="text-primary">IDR
                                                    <?= number_format($transaction['total_price'], 0, ',', '.') ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Fallback when order items parsing fails -->
                        <div class="invoice-details mb-4">
                            <h5 class="mb-4">Order Summary:</h5>
                            <div class="invoice-item p-3" style="border: 1px solid #dee2e6; border-radius: 5px;">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h6 class="mb-1">Order Total</h6>
                                        <small class="text-muted">Detailed items not available</small>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <strong class="text-primary">IDR
                                            <?= number_format($transaction['total_price'], 0, ',', '.') ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="no-print text-center">
                            <button onclick="window.print()" class="btn btn-primary mr-2 py-3 px-4">
                                <i class="fa fa-print"></i> Print Invoice
                            </button>
                            <a href="index.php" class="btn btn-secondary py-3 px-4">Continue Shopping</a>
                        </div>
                    <?php endif; ?>

                    <!-- Thank You Message -->
                    <div class="no-print text-center mt-5 pt-4">
                        <h4 class="text-primary">Thank you for your order!</h4>
                        <p class="text-muted">We appreciate your business and hope you enjoy your meal.</p>
                        <?php if ($transaction['payment_method'] === 'bank_transfer'): ?>
                            <div class="alert alert-info mt-3">
                                <strong>Bank Transfer Instructions:</strong><br>
                                Please transfer the total amount to our bank account and send the proof of payment to our
                                WhatsApp.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="no-print">
        <?php include "layout/footer.php" ?>
    </div>

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

    	<!-- js sweet alert -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		$(document).ready(function () {
			// Load cart dropdown on page load
			loadCartDropdown();

			// Add to cart functionality
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
						// Sweet success alert for cart
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
						// Sweet error alert for cart
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
										<p class="mb-0">
										  <a href="#" class="price">IDR ${Number(item.price).toLocaleString('id-ID')}</a>
											  <span class="quantity ml-3">Quantity: ${item.quantity.toString().padStart(2, '0')}</span>
										</p>
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
	</script>


    <style>
        @media print {

            .btn,
            .hero-wrap,
            nav,
            footer,
            #ftco-loader,
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .invoice-header {
                border-bottom: 2px solid #000;
                padding-bottom: 20px;
                margin-bottom: 30px;
            }

            .invoice-details h5 {
                color: #333 !important;
                border-bottom: 1px solid #ccc;
                padding-bottom: 5px;
            }

            .invoice-item {
                border: 1px solid #ccc !important;
                margin-bottom: 10px;
            }
        }

        .invoice-header {
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 20px;
        }

        .invoice-details h5 {
            color: #333;
            font-weight: 600;
        }

        .invoice-item {
            transition: all 0.3s ease;
        }

        .invoice-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .text-primary {
            color: #F96D00 !important;
        }
    </style>

</body>

</html>