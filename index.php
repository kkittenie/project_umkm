<?php
session_start();
include 'config.php';

// Initialize cart and wishlist if they don't exist
if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = array();
}

$cart_count = count($_SESSION['cart']);

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['username']);


?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Rasa. - Main Page</title>
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
	<link rel="stylesheet" href="css/style.css">

	<!-- SweetAlert2 CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>
	
<?php include "layout/header.php"?>

	<div class="hero-wrap" style="background-image: url('images/bg2.jpg');" data-stellar-background-ratio="0.5">
		<div class="overlay"></div>
		<div class="container">
			<div class="row no-gutters slider-text align-items-center justify-content-center">
				<div class="col-md-8 ftco-animate d-flex align-items-end">
					<div class="text w-100 text-center">
						<h1 class="mb-4">Good <span>Dish</span> for Good <span>Moments</span>.</h1>
						<p><a href="#shop" class="btn btn-primary py-2 px-4">Shop Now</a> <a href="about.php"
								class="btn btn-white btn-outline-white py-2 px-4">Read more</a></p>
					</div>
				</div>
			</div>
		</div>
	</div>

	<section class="ftco-intro">
		<div class="container">
			<div class="row no-gutters">
				<div class="col-md-4 d-flex">
					<div class="intro d-lg-flex w-100 ftco-animate">
						<div class="icon">
							<span class="flaticon-support"></span>
						</div>
						<div class="text">
							<h2>Online Support</h2>
							<p>Our team is always here — to answer your questions, take your orders, and help you find
								the perfect flavor.</p>
						</div>
					</div>
				</div>
				<div class="col-md-4 d-flex">
					<div class="intro color-1 d-lg-flex w-100 ftco-animate">
						<div class="icon">
							<span class="flaticon-cashback"></span>
						</div>
						<div class="text">
							<h2>Money Back Guarantee</h2>
							<p>Not satisfied? We've got your back. Enjoy hassle-free returns and a full refund — no
								questions asked.</p>
						</div>
					</div>
				</div>
				<div class="col-md-4 d-flex">
					<div class="intro color-2 d-lg-flex w-100 ftco-animate">
						<div class="icon">
							<span class="flaticon-free-delivery"></span>
						</div>
						<div class="text">
							<h2>Free Shipping &amp; Return</h2>
							<p>Fast, free, and reliable. Every order comes with free shipping and a simple return
								process for your peace of mind.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section ftco-no-pb">
		<div class="container">
			<div class="row">
				<div class="col-md-6 img img-3 d-flex justify-content-center align-items-center"
					style="background-image: url(images/about_1.jpg);">
				</div>
				<div class="col-md-6 wrap-about pl-md-5 ftco-animate py-5">
					<div class="heading-section">
						<span class="subheading">Since 1905</span>
						<h2 class="mb-4">Desire Meets A New Taste</h2>
						<p>Taste is more than a sensation — it's a memory, a story, a journey. For over a century, we've
							brought people together through dishes that honor heritage and celebrate creativity.</p>
						<p>Our ingredients are handpicked, our techniques time-honored, and every plate is crafted with
							passion — from farm to flame to fork. Whether it's your first visit or your hundredth, we
							invite you to rediscover the joy of food that feels like home</p>
						<p class="year">
							<strong class="number" data-number="115">0</strong>
							<span>Years of Experience In Business</span>
						</p>
					</div>

				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section" id="shop">
		<div class="container">
			<div class="row justify-content-center pb-5">
				<div class="col-md-7 heading-section text-center ftco-animate">
					<span class="subheading">Our Delightful offerings</span>
					<h2>Tastefully Yours</h2>
				</div>
			</div>
			<div class="row">

				<?php
				include 'config.php';
				$query = "SELECT p.*, c.category_name 
        				  FROM product p 
        				  JOIN category c ON p.id_category = c.id_category 
        				  LIMIT 4";
				$result = mysqli_query($db, $query);
				while ($row = mysqli_fetch_array($result)) {
					?>

					<div class="col-md-3 d-flex">
						<div class="product ftco-animate">
							<div class="img d-flex align-items-center justify-content-center"
								style="background-image: url('images/<?= $row['photo'] ?>');">
								<div class="desc">
									<p class="meta-prod d-flex">
										<a href="#" class="d-flex align-items-center justify-content-center add-to-cart-btn"
											data-product-id="<?= $row['id'] ?>">
											<span class="flaticon-shopping-bag"></span>
										</a>
										<a href="product-single.php?id=<?= $row['id'] ?>"
											class="d-flex align-items-center justify-content-center">
											<span class="flaticon-visibility"></span>
										</a>
									</p>
								</div>
							</div>
							<div class="text text-center">
								<span class="category"><?= $row['category_name'] ?></span>
								<h2><?= $row['name'] ?></h2>
								<span class="price">IDR <?= number_format($row['price'], 0, ',', '.') ?></span>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>

			<div class="row justify-content-center">
				<div class="col-md-4">
					<a href="product.php" class="btn btn-primary d-block">View All Products <span
							class="fa fa-long-arrow-right"></span></a>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section testimony-section img" style="background-image: url(images/carousel_2.jpg);">
		<div class="overlay"></div>
		<div class="container">
			<div class="row justify-content-center mb-5">
				<div class="col-md-7 text-center heading-section heading-section-white ftco-animate">
					<span class="subheading">Testimonial</span>
					<h2 class="mb-3">Happy Clients</h2>
				</div>
			</div>
			<div class="row ftco-animate">
				<div class="col-md-12">
					<div class="carousel-testimony owl-carousel ftco-owl">
						<div class="item">
							<div class="testimony-wrap py-4">
								<div class="icon d-flex align-items-center justify-content-center"><span
										class="fa fa-quote-left"></div>
								<div class="text">
									<p class="mb-4">Far far away, behind the word mountains, far from the countries
										Vokalia and Consonantia, there live the blind texts.</p>
									<div class="d-flex align-items-center">
										<div class="user-img" style="background-image: url(images/person_1.jpg)"></div>
										<div class="pl-3">
											<p class="name">Roger Scott</p>
											<span class="position">Marketing Manager</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="item">
							<div class="testimony-wrap py-4">
								<div class="icon d-flex align-items-center justify-content-center"><span
										class="fa fa-quote-left"></div>
								<div class="text">
									<p class="mb-4">Far far away, behind the word mountains, far from the countries
										Vokalia and Consonantia, there live the blind texts.</p>
									<div class="d-flex align-items-center">
										<div class="user-img" style="background-image: url(images/person_2.jpg)"></div>
										<div class="pl-3">
											<p class="name">Roger Scott</p>
											<span class="position">Marketing Manager</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="item">
							<div class="testimony-wrap py-4">
								<div class="icon d-flex align-items-center justify-content-center"><span
										class="fa fa-quote-left"></div>
								<div class="text">
									<p class="mb-4">Far far away, behind the word mountains, far from the countries
										Vokalia and Consonantia, there live the blind texts.</p>
									<div class="d-flex align-items-center">
										<div class="user-img" style="background-image: url(images/person_3.jpg)"></div>
										<div class="pl-3">
											<p class="name">Roger Scott</p>
											<span class="position">Marketing Manager</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="item">
							<div class="testimony-wrap py-4">
								<div class="icon d-flex align-items-center justify-content-center"><span
										class="fa fa-quote-left"></div>
								<div class="text">
									<p class="mb-4">Far far away, behind the word mountains, far from the countries
										Vokalia and Consonantia, there live the blind texts.</p>
									<div class="d-flex align-items-center">
										<div class="user-img" style="background-image: url(images/person_1.jpg)"></div>
										<div class="pl-3">
											<p class="name">Roger Scott</p>
											<span class="position">Marketing Manager</span>
										</div>
									</div>
								</div>
							</div>
						</div>
						<div class="item">
							<div class="testimony-wrap py-4">
								<div class="icon d-flex align-items-center justify-content-center"><span
										class="fa fa-quote-left"></div>
								<div class="text">
									<p class="mb-4">Far far away, behind the word mountains, far from the countries
										Vokalia and Consonantia, there live the blind texts.</p>
									<div class="d-flex align-items-center">
										<div class="user-img" style="background-image: url(images/person_2.jpg)"></div>
										<div class="pl-3">
											<p class="name">Roger Scott</p>
											<span class="position">Marketing Manager</span>
										</div>
									</div>
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

</body>

</html>