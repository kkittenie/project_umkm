<?php
session_start();
include 'config.php';

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = array();
}

$cart_count = count($_SESSION['cart']);

?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Rasa. - Contact</title>
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

	<!-- SweetAlert2 for alerts -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

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
									class="fa fa-chevron-right"></i></a></span> <span>Contact Us <i
								class="fa fa-chevron-right"></i></span></p>
					<h2 class="mb-0 bread">Contact Us</h2>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section bg-light">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-md-12">
					<div class="wrapper px-md-4">
						<div class="row mb-5">
							<div class="col-md-3">
								<div class="dbox w-100 text-center">
									<div class="icon d-flex align-items-center justify-content-center">
										<span class="fa fa-map-marker"></span>
									</div>
									<div class="text">
										<p><span>Address:</span> Jalan Melati, Kota Cirebon, Indonesia</p>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="dbox w-100 text-center">
									<div class="icon d-flex align-items-center justify-content-center">
										<span class="fa fa-phone"></span>
									</div>
									<div class="text">
										<p><span>Phone:</span> <a href="tel://1234567920">+62 882 0007 68044</a></p>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="dbox w-100 text-center">
									<div class="icon d-flex align-items-center justify-content-center">
										<span class="fa fa-paper-plane"></span>
									</div>
									<div class="text">
										<p><span>Email:</span> <a href="mailto:rasa@gmail.com">rasa@gmail.com</a>
										</p>
									</div>
								</div>
							</div>
							<div class="col-md-3">
								<div class="dbox w-100 text-center">
									<div class="icon d-flex align-items-center justify-content-center">
										<span class="fa fa-globe"></span>
									</div>
									<div class="text">
										<p><span>Website</span> <a href="#">rasa.com</a></p>
									</div>
								</div>
							</div>
						</div>
						<div class="row no-gutters justify-content-center">
							<div class="col-md-7">
								<div class="contact-wrap w-100 p-md-5 p-4">
									<h3 class="mb-4">Contact Us</h3>
									<form method="POST" action="contact_handler.php" id="contactForm" name="contactForm" class="contactForm">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label class="label" for="name">Full Name</label>
													<input type="text" class="form-control" name="name" id="name"
														placeholder="Name" required>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label class="label" for="email">Email Address</label>
													<input type="email" class="form-control" name="email" id="email"
														placeholder="Email" required>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="label" for="subject">Subject</label>
													<input type="text" class="form-control" name="subject"
														id="subject" placeholder="Subject" required>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label class="label" for="message">Message</label>
													<textarea name="message" class="form-control" id="message"
														cols="30" rows="4" placeholder="Message" required></textarea>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<input type="submit" value="Send Message"
														class="btn btn-primary" id="submitBtn">
													<div class="submitting"></div>
												</div>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
					</div>
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
	<script src="js/main.js"></script>

	<script>
		$(document).ready(function () {
			loadCartDropdown();

			// Contact form submission with loading state
			$('#contactForm').on('submit', function() {
				$('#submitBtn').prop('disabled', true).val('Sending...');
				$('.submitting').html('<div class="spinner-border spinner-border-sm" role="status"><span class="sr-only">Loading...</span></div>');
			});

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
						alert(data.message);
					} else {
						alert(data.message);
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

	<!-- Alert Messages -->
	<?php if (isset($_SESSION['success'])): ?>
		<script>
			Swal.fire({
				icon: 'success',
				title: 'Message Sent!',
				text: '<?= $_SESSION['success'] ?>',
				confirmButtonColor: '#F96D00',
				timer: 5000,
				timerProgressBar: true
			});
		</script>
		<?php unset($_SESSION['success']); ?>
	<?php endif; ?>

	<?php if (isset($_SESSION['error'])): ?>
		<script>
			Swal.fire({
				icon: 'error',
				title: 'Oops...',
				text: '<?= $_SESSION['error'] ?>',
				confirmButtonColor: '#F96D00'
			});
		</script>
		<?php unset($_SESSION['error']); ?>
	<?php endif; ?>

</body>

</html>