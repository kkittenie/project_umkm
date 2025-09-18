<?php
session_start();
include 'config.php';

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = array();
}

$cart_items = $_SESSION['cart'];
$cart_count = count($cart_items);

$subtotal = 0;
foreach ($cart_items as $item) {
	$subtotal += $item['price'] * $item['quantity'];
}
$discount = 0;
$total = $subtotal - $discount;
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Rasa. - Cart</title>
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
									class="fa fa-chevron-right"></i></a></span> <span>Cart <i
								class="fa fa-chevron-right"></i></span></p>
					<h2 class="mb-0 bread">My Cart</h2>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section">
		<div class="container">
			<div class="row">
				<div class="table-wrap">
					<table class="table">
						<thead class="thead-primary">
							<tr>
								<th>&nbsp;</th>
								<th>&nbsp;</th>
								<th>Product</th>
								<th>Price</th>
								<th>Quantity</th>
								<th>Total</th>
								<th>&nbsp;</th>
							</tr>
						</thead>
						<tbody id="cart-table-body">
							<?php if (empty($cart_items)): ?>
								<tr>
									<td colspan="7" class="text-center">Your cart is empty</td>
								</tr>
							<?php else: ?>
								<?php foreach ($cart_items as $index => $item): ?>
									<tr class="alert cart-item" role="alert" data-product-id="<?= $item['id'] ?>">
										<td>
											<label class="checkbox-wrap checkbox-primary">
												<input type="checkbox" class="item-checkbox" checked>
												<span class="checkmark"></span>
											</label>
										</td>
										<td>
											<div class="img" style="background-image: url(images/<?= $item['photo'] ?>);"></div>
										</td>
										<td>
											<div class="email">
												<span><?= $item['name'] ?></span>
												<span><?= $item['category'] ?></span>
											</div>
										</td>
										<td class="item-price"> IDR
											<?= number_format($item['price'], 0, ',', '.') ?>
										</td>
										<td class="quantity">
											<div class="input-group">
												<input type="number" name="quantity"
													class="quantity-input form-control input-number"
													value="<?= $item['quantity'] ?>" min="1" max="100"
													data-product-id="<?= $item['id'] ?>">
											</div>
										</td>
										<td class="item-total">
											IDR <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>
										</td>
										<td>
											<button type="button" class="close remove-item" data-product-id="<?= $item['id'] ?>"
												aria-label="Close">
												<span aria-hidden="true"><i class="fa fa-close"></i></span>
											</button>
										</td>
									</tr>
								<?php endforeach; ?>
							<?php endif; ?>
						</tbody>
					</table>
				</div>
			</div>
			<div class="row justify-content-end">
				<div class="col col-lg-5 col-md-6 mt-5 cart-wrap ftco-animate">
					<div class="cart-total mb-3">
						<h3>Cart Totals</h3>
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
					<p class="text-center">
						<a href="checkout.php" class="btn btn-primary py-3 px-4" id="checkout-btn" <?= empty($cart_items) ? 'style="display:none;"' : '' ?>>
							Proceed to Checkout
						</a>
					</p>
				</div>
			</div>
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

	<script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.all.min.js"></script>

	<script>
		$(document).ready(function () {
			loadCartDropdown();

			$('.remove-item').click(function () {
				var productId = $(this).data('product-id');
				var $row = $(this).closest('tr');
				var productName = $row.find('.email span').first().text();

				Swal.fire({
					title: 'Remove Item?',
					text: `Are you sure you want to remove "${productName}" from your cart?`,
					icon: 'warning',
					showCancelButton: true,
					confirmButtonColor: '#F96D00',
					cancelButtonColor: '#6c757d',
					confirmButtonText: 'Yes, remove it!',
					cancelButtonText: 'Cancel'
				}).then((result) => {
					if (result.isConfirmed) {
						Swal.fire({
							title: 'Removing...',
							text: 'Please wait while we remove the item from your cart.',
							allowOutsideClick: false,
							showConfirmButton: false,
							didOpen: () => {
								Swal.showLoading();
							}
						});

						$.post('cart_handler.php', {
							action: 'remove_from_cart',
							product_id: productId
						}, function (response) {
							var data = JSON.parse(response);
							if (data.success) {
								$row.fadeOut(300, function () {
									$(this).remove();
									updateCartTotals();
									$('#cart-count').text(data.cart_count);
									loadCartDropdown();

									if ($('#cart-table-body tr:visible').length === 0) {
										$('#cart-table-body').html('<tr><td colspan="7" class="text-center">Your cart is empty</td></tr>');
										$('#checkout-btn').hide();
									}

									Swal.fire({
										icon: 'success',
										title: 'Item Removed!',
										text: `"${productName}" has been removed from your cart.`,
										timer: 2000,
										timerProgressBar: true,
										showConfirmButton: false,
										toast: true,
										position: 'top-end'
									});
								});
							} else {
								Swal.fire({
									icon: 'error',
									title: 'Error',
									text: 'Failed to remove item from cart. Please try again.',
									confirmButtonColor: '#F96D00'
								});
							}
						}).fail(function() {
							Swal.fire({
								icon: 'error',
								title: 'Network Error',
								text: 'Unable to connect to server. Please check your connection and try again.',
								confirmButtonColor: '#F96D00'
							});
						});
					}
				});
			});

			$('.quantity-input').change(function () {
				var productId = $(this).data('product-id');
				var quantity = parseInt($(this).val());
				var $row = $(this).closest('tr');
				var productName = $row.find('.email span').first().text();

				if (quantity < 1) {
					$(this).val(1);
					quantity = 1;
					Swal.fire({
						icon: 'warning',
						title: 'Invalid Quantity',
						text: 'Quantity must be at least 1.',
						timer: 2000,
						timerProgressBar: true,
						showConfirmButton: false,
						toast: true,
						position: 'top-end'
					});
					return;
				}

				if (quantity > 100) {
					$(this).val(100);
					quantity = 100;
					Swal.fire({
						icon: 'warning',
						title: 'Maximum Quantity Exceeded',
						text: 'Maximum quantity allowed is 100.',
						timer: 2000,
						timerProgressBar: true,
						showConfirmButton: false,
						toast: true,
						position: 'top-end'
					});
				}

				$.post('cart_handler.php', {
					action: 'update_cart',
					product_id: productId,
					quantity: quantity
				}, function (response) {
					var data = JSON.parse(response);
					if (data.success) {
						var price = parseInt($row.find('.item-price').text().replace(/[^\d]/g, ''));
						var itemTotal = price * quantity;
						$row.find('.item-total').text('IDR ' + itemTotal.toLocaleString('id-ID'));

						updateCartTotals();
						$('#cart-count').text(data.cart_count);
						loadCartDropdown();

						Swal.fire({
							icon: 'success',
							title: 'Quantity Updated!',
							text: `"${productName}" quantity updated to ${quantity}.`,
							timer: 1500,
							timerProgressBar: true,
							showConfirmButton: false,
							toast: true,
							position: 'top-end'
						});
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Update Failed',
							text: data.message || 'Failed to update quantity. Please try again.',
							confirmButtonColor: '#F96D00'
						});
						$(this).val($(this).data('prev-value') || 1);
					}
				}).fail(function() {
					Swal.fire({
						icon: 'error',
						title: 'Network Error',
						text: 'Unable to connect to server. Please check your connection and try again.',
						confirmButtonColor: '#F96D00'
					});
					$(this).val($(this).data('prev-value') || 1);
				});
			});

			$('.quantity-input').focus(function() {
				$(this).data('prev-value', $(this).val());
			});

			function updateCartTotals() {
				var subtotal = 0;
				$('.cart-item').each(function () {
					var itemTotal = parseInt($(this).find('.item-total').text().replace(/[^\d]/g, ''));
					subtotal += itemTotal;
				});

				var discount = 0; 
				var total = subtotal - discount;

				$('#subtotal').text('IDR ' + subtotal.toLocaleString('id-ID'));
				$('#discount').text('IDR ' + discount.toLocaleString('id-ID'));
				$('#total').text('IDR ' + total.toLocaleString('id-ID'));
			}

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
				}).fail(function() {
					console.log('Failed to load cart dropdown');
				});
			}
		});
	</script>

	<style>
		.swal2-popup {
			font-family: 'Spectral', serif;
		}

		.swal2-confirm {
			background-color: #F96D00 !important;
		}

		.swal2-confirm:focus {
			box-shadow: 0 0 0 3px rgba(249, 109, 0, 0.5) !important;
		}

		.swal2-cancel {
			background-color: #6c757d !important;
		}

		.quantity-input {
			transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
		}

		.quantity-input:focus {
			border-color: #F96D00;
			box-shadow: 0 0 0 0.2rem rgba(249, 109, 0, 0.25);
		}
	</style>

</body>

</html>