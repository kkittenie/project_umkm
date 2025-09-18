<?php
session_start();
include 'config.php';

// Initialize cart and wishlist if they don't exist
if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = array();
}

$cart_items = $_SESSION['cart'];
$cart_count = count($cart_items);

// Calculate totals (same logic as cart page)
$subtotal = 0;
foreach ($cart_items as $item) {
	$subtotal += $item['price'] * $item['quantity'];
}
$discount = 0;
$total = $subtotal - $discount;

// Get user billing information if logged in
$user_data = null;
if (isset($_SESSION['user_id'])) {
	$user_id = $_SESSION['user_id'];
	$query = "SELECT * FROM users WHERE id = '$user_id'";
	$result = mysqli_query($db, $query);

	if ($result && mysqli_num_rows($result) > 0) {
		$user_data = mysqli_fetch_assoc($result);
	}
}

// Process checkout form submission
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }

    $user_id = $_SESSION['user_id'];
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $total_price = $total;

    // Start database transaction for data consistency
    mysqli_autocommit($db, false);
    
    try {
        // Insert into transaction table
        $sql_transaction = "INSERT INTO transaction (id_user, date, total_price, payment_method) 
                           VALUES ('$user_id', NOW(), '$total_price', '$payment_method')";
        
        if (!mysqli_query($db, $sql_transaction)) {
            throw new Exception("Error inserting transaction: " . mysqli_error($db));
        }

        $transaction_id = mysqli_insert_id($db);

        // Insert each cart item into detail table and update stock
        foreach ($cart_items as $item) {
            $product_id = $item['id'];
            $quantity = $item['quantity'];

            // Check current stock first
            $check_stock_sql = "SELECT stock FROM product WHERE id = '$product_id'";
            $stock_result = mysqli_query($db, $check_stock_sql);
            $product_data = mysqli_fetch_assoc($stock_result);
            
            if (!$product_data) {
                throw new Exception("Product not found: " . $product_id);
            }
            
            if ($product_data['stock'] < $quantity) {
                throw new Exception("Insufficient stock for product ID: " . $product_id);
            }

            // Insert into detail table
            $sql_detail = "INSERT INTO detail (id_transaction, id_product, amount) 
                          VALUES ('$transaction_id', '$product_id', '$quantity')";
            if (!mysqli_query($db, $sql_detail)) {
                throw new Exception("Error inserting detail: " . mysqli_error($db));
            }

            // Update product stock
            $sql_stock = "UPDATE product SET stock = stock - $quantity WHERE id = '$product_id'";
            if (!mysqli_query($db, $sql_stock)) {
                throw new Exception("Error updating stock: " . mysqli_error($db));
            }
        }

        // Commit transaction
        mysqli_commit($db);
        
        // Clear cart
        $_SESSION['cart'] = [];
        
        // Set success message for display
        $success_message = "Order placed successfully! Your transaction ID is #" . $transaction_id;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($db);
        $error_message = $e->getMessage();
    }
    
    // Turn autocommit back on
    mysqli_autocommit($db, true);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Rasa. - Checkout</title>
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

	<!-- SweetAlert2 CSS -->
	<link rel="stylesheet"
		href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css">

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
									class="fa fa-chevron-right"></i></a></span> <span>Checkout <i
								class="fa fa-chevron-right"></i></span></p>
					<h2 class="mb-0 bread">Checkout</h2>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section">
		<div class="container">
			<?php if (empty($cart_items)): ?>
				<div class="row justify-content-center">
					<div class="col-md-8 text-center">
						<h3>Your cart is empty</h3>
						<p>Please add items to your cart before proceeding to checkout.</p>
						<a href="index.php" class="btn btn-primary">Continue Shopping</a>
					</div>
				</div>
			<?php else: ?>
				<div class="row justify-content-center">
					<div class="col-xl-10 ftco-animate">
						<form method="POST" class="billing-form" id="checkout-form">
							<h3 class="mb-4 billing-heading">Billing Details</h3>
							<div class="row align-items-end">
								<div class="col-md-6">
									<div class="form-group">
										<label for="firstname">First Name</label>
										<input type="text" class="form-control" name="firstname" id="firstname"
											value="<?= $user_data ? htmlspecialchars($user_data['first_name'] ?? '') : '' ?>"
											required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="lastname">Last Name</label>
										<input type="text" class="form-control" name="lastname" id="lastname"
											value="<?= $user_data ? htmlspecialchars($user_data['last_name'] ?? '') : '' ?>"
											required>
									</div>
								</div>
								<div class="w-100"></div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="streetaddress">Street Address</label>
										<input type="text" class="form-control" name="streetaddress" id="streetaddress"
											placeholder="House number and street name"
											value="<?= $user_data ? htmlspecialchars($user_data['address'] ?? '') : '' ?>"
											required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<input type="text" class="form-control" name="apartment" id="apartment"
											placeholder="Appartment, suite, unit etc: (optional)"
											value="<?= $user_data ? htmlspecialchars($user_data['apartment'] ?? '') : '' ?>">
									</div>
								</div>
								<div class="w-100"></div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="phone">Phone</label>
										<input type="text" class="form-control" name="phone" id="phone"
											value="<?= $user_data ? htmlspecialchars($user_data['phone'] ?? '') : '' ?>"
											required>
									</div>
								</div>
								<div class="col-md-6">
									<div class="form-group">
										<label for="emailaddress">Email Address</label>
										<input type="email" class="form-control" name="emailaddress" id="emailaddress"
											value="<?= $user_data ? htmlspecialchars($user_data['email'] ?? '') : '' ?>"
											required>
									</div>
								</div>
								<div class="w-100"></div>
								<div class="col-md-12">
									<div class="form-group mt-4">
										<?php if ($user_data): ?>
											<div class="alert alert-info">
												<i class="fa fa-info-circle"></i>
												Your billing details have been pre-filled from your account. You can modify them
												if needed.
											</div>
										<?php endif; ?>
									</div>
								</div>
							</div>
						</form><!-- END -->

						<div class="row mt-5 pt-3 d-flex">
							<div class="col-md-6 d-flex">
								<div class="cart-detail cart-total p-3 p-md-4">
									<h3 class="billing-heading mb-4">Cart Total</h3>
									<p class="d-flex">
										<span>Subtotal</span>
										<span id="subtotal">IDR <?= number_format($subtotal, 0, ',', '.') ?></span>
									</p>
									<hr>
									<p class="d-flex total-price">
										<span>Total</span>
										<span id="total">IDR <?= number_format($total, 0, ',', '.') ?></span>
									</p>
								</div>
							</div>
							<div class="col-md-6">
								<div class="cart-detail p-3 p-md-4">
									<form method="POST">
										<h3 class="billing-heading mb-4">Payment Method</h3>
										<div class="form-group">
											<div class="col-md-12">
												<div class="radio">
													<label>
														<input type="radio" name="payment_method" class="mr-2" value="bank_transfer"
															<?= ($user_data && isset($user_data['preferred_payment']) && $user_data['preferred_payment'] == 'bank_transfer') ? 'checked' : '' ?> required>
														Bank Transfer
													</label>
												</div>
											</div>
										</div>

										<div class="form-group">
											<div class="col-md-12">
												<div class="radio">
													<label>
														<input type="radio" name="payment_method" class="mr-2" value="dana"
															<?= ($user_data && isset($user_data['preferred_payment']) && $user_data['preferred_payment'] == 'dana') ? 'checked' : '' ?> required>
														DANA
													</label>
												</div>
											</div>
										</div>

										<div class="form-group">
											<div class="col-md-12">
												<div class="radio">
													<label>
														<input type="radio" name="payment_method" class="mr-2" value="cod"
															<?= ($user_data && isset($user_data['preferred_payment']) && $user_data['preferred_payment'] == 'cod') ? 'checked' : '' ?> required>
														Cash on Delivery (COD)
													</label>
												</div>
											</div>
										</div>

										<div class="form-group">
											<div class="col-md-12">
												<div class="checkbox">
													<label><input type="checkbox" value="" class="mr-2" id="terms" required> I
														have read and accept
														the terms and conditions</label>
												</div>
											</div>
										</div>
										<p><button type="submit" name="place_order" class="btn btn-primary py-3 px-4" id="place-order-btn">Place an order</button></p>
									</form>
								</div>
							</div>
						</div>
					</div> <!-- .col-md-8 -->
				</div>
			<?php endif; ?>
		</div>
	</section>

	<?php include "layout/footer.php" ?>

	<!-- loader -->
	<div id="ftco-loader" class="show fullscreen"><svg class="circular" width="48px" height="48px">
			<circle class="path-bg" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke="#eeeeee" />
			<circle class="path" cx="24" cy="24" r="22" fill="none" stroke-width="4" stroke-miterlimit="10"
				stroke="#F96D00" />
		</svg></div>

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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
	<script src="js/main.js"></script>

	<!-- SweetAlert2 JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.all.min.js"></script>

	<script>
		$(document).ready(function () {
			// Show error message if exists
			<?php if (!empty($error_message)): ?>
				Swal.fire({
					icon: 'error',
					title: 'Order Failed',
					text: '<?= addslashes($error_message) ?>',
					confirmButtonColor: '#F96D00'
				});
			<?php endif; ?>

			// Show success message if exists
			<?php if (!empty($success_message)): ?>
				Swal.fire({
					icon: 'success',
					title: 'Order Placed Successfully!',
					text: '<?= addslashes($success_message) ?>',
					confirmButtonText: 'View Invoice',
					confirmButtonColor: '#F96D00',
					timer: 5000,
					timerProgressBar: true,
					showClass: {
						popup: 'animate__animated animate__fadeInDown'
					},
					hideClass: {
						popup: 'animate__animated animate__fadeOutUp'
					}
				}).then((result) => {
					// Redirect to invoice page when user clicks "View Invoice" or alert auto-closes
					<?php if (isset($transaction_id)): ?>
						window.location.href = 'invoice.php?order_id=<?= $transaction_id ?>';
					<?php else: ?>
						window.location.href = 'index.php';
					<?php endif; ?>
				});
			<?php endif; ?>

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
						// Show success message
						Swal.fire({
							icon: 'success',
							title: 'Added to Cart!',
							text: 'Product has been added to your cart.',
							timer: 2000,
							timerProgressBar: true,
							showConfirmButton: false,
							toast: true,
							position: 'top-end',
							confirmButtonColor: '#F96D00'
						});
						// Refresh page to update totals
						setTimeout(() => {
							location.reload();
						}, 2000);
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Error',
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
		#place-order-btn:disabled {
			opacity: 0.6;
			cursor: not-allowed;
		}

		.alert-info {
			background-color: #d1ecf1;
			border-color: #bee5eb;
			color: #0c5460;
			padding: 10px 15px;
			border: 1px solid transparent;
			border-radius: 4px;
		}

		/* Custom SweetAlert2 styling to match your theme */
		.swal2-popup {
			font-family: 'Spectral', serif;
		}

		.swal2-confirm {
			background-color: #F96D00 !important;
		}

		.swal2-confirm:focus {
			box-shadow: 0 0 0 3px rgba(249, 109, 0, 0.5) !important;
		}
	</style>

</body>

</html>