<?php
include 'config.php';
session_start(); // Add this line - it was missing!

if (isset($_GET['id'])) {
	$id = intval($_GET['id']);
	$query = "SELECT * FROM product WHERE id = $id";
	$result = mysqli_query($db, $query);

	if ($result && mysqli_num_rows($result) > 0) {
		$data = mysqli_fetch_assoc($result);
	} else {
		echo "Produk tidak ditemukan.";
		exit;
	}
} else {
	echo "ID produk tidak diberikan.";
	exit;
}

// Initialize cart and wishlist if they don't exist
if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = array();
}
if (!isset($_SESSION['wishlist'])) {
	$_SESSION['wishlist'] = array();
}

$cart_count = count($_SESSION['cart']);
$in_wishlist = in_array($data['id'], $_SESSION['wishlist']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Rasa. - Product Details</title>
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

	<!-- SweetAlert2 CSS -->
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
									class="fa fa-chevron-right"></i></a></span> <span><a href="product.php">Products <i
									class="fa fa-chevron-right"></i></a></span> <span>Product Details <i
								class="fa fa-chevron-right"></i></span></p>
					<h2 class="mb-0 bread">Product Details</h2>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 mb-5 ftco-animate">
					<a href="images/<?= htmlspecialchars($data['photo']) ?>" class="image-popup prod-img-bg">
						<img src="images/<?= htmlspecialchars($data['photo']) ?>" class="img-fluid"
							alt="<?= htmlspecialchars($data['name']) ?>">
					</a>
				</div>
				<div class="col-lg-6 product-details pl-md-5 ftco-animate">
					<h3><?= htmlspecialchars($data['name']) ?></h3>
					<div class="rating d-flex">
						<p class="text-left mr-4">
							<a href="#" class="mr-2">5.0</a>
							<a href="#"><span class="fa fa-star"></span></a>
							<a href="#"><span class="fa fa-star"></span></a>
							<a href="#"><span class="fa fa-star"></span></a>
							<a href="#"><span class="fa fa-star"></span></a>
							<a href="#"><span class="fa fa-star"></span></a>
						</p>
						<p class="text-left mr-4">
							<a href="#" class="mr-2" style="color: #000;">100 <span
									style="color: #bbb;">Rating</span></a>
						</p>
						<p class="text-left">
							<a href="#" class="mr-2" style="color: #000;">500 <span style="color: #bbb;">Sold</span></a>
						</p>
					</div>
					<p class="price"><span>IDR <?= number_format($data['price']) ?></span></p>
					<p><?= nl2br(htmlspecialchars($data['description'])) ?></p>

					<div class="row mt-4">
						<div class="input-group col-md-6 d-flex mb-3">
							<span class="input-group-btn mr-2">
								<button type="button" class="quantity-left-minus btn" data-type="minus" data-field="">
									<i class="fa fa-minus"></i>
								</button>
							</span>
							<input type="text" id="quantity" name="quantity" class="quantity form-control input-number"
								value="1" min="1" max="100">
							<span class="input-group-btn ml-2">
								<button type="button" class="quantity-right-plus btn" data-type="plus" data-field="">
									<i class="fa fa-plus"></i>
								</button>
							</span>
						</div>
						<div class="w-100"></div>
						<div class="col-md-12">
							<p style="color: #000;">Available</p>
						</div>
					</div>
					<p>
						<a href="#" class="btn btn-primary py-3 px-5 mr-2 add-to-cart-btn"
							data-product-id="<?= $data['id'] ?>">Add to Cart</a>
					</p> 
				</div>
			</div>

			<div class="row mt-5">
				<div class="col-md-12 nav-link-wrap">
					<div class="nav nav-pills d-flex text-center" id="v-pills-tab" role="tablist"
						aria-orientation="vertical">
						<a class="nav-link ftco-animate active mr-lg-1" id="v-pills-1-tab" data-toggle="pill"
							href="#v-pills-1" role="tab" aria-controls="v-pills-1" aria-selected="true">Description</a>

						<a class="nav-link ftco-animate" id="v-pills-3-tab" data-toggle="pill" href="#v-pills-3"
							role="tab" aria-controls="v-pills-3" aria-selected="false">Reviews</a>

					</div>
				</div>
				<div class="col-md-12 tab-wrap">

					<div class="tab-content bg-light" id="v-pills-tabContent">

						<div class="tab-pane fade show active" id="v-pills-1" role="tabpanel"
							aria-labelledby="day-1-tab">
							<div class="p-4">
								<h3 class="mb-4"><?= htmlspecialchars($data['name']) ?></h3>
								<p><?= nl2br(htmlspecialchars($data['description'])) ?></p>

							</div>
						</div>
						<div class="tab-pane fade" id="v-pills-3" role="tabpanel" aria-labelledby="v-pills-day-3-tab">
							<div class="row p-4">
								<div class="col-md-7">
									<h3 class="mb-4">23 Reviews</h3>
									<div class="review">
										<div class="user-img" style="background-image: url(images/person_1.jpg)"></div>
										<div class="desc">
											<h4>
												<span class="text-left">Jacob Webb</span>
												<span class="text-right">25 April 2020</span>
											</h4>
											<p class="star">
												<span>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
												</span>
												<span class="text-right"><a href="#" class="reply"><i
															class="icon-reply"></i></a></span>
											</p>
											<p>When she reached the first hills of the Italic Mountains, she had a last
												view back on the skyline of her hometown Bookmarksgrov</p>
										</div>
									</div>
									<div class="review">
										<div class="user-img" style="background-image: url(images/person_2.jpg)"></div>
										<div class="desc">
											<h4>
												<span class="text-left">Jacob Webb</span>
												<span class="text-right">25 April 2020</span>
											</h4>
											<p class="star">
												<span>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
												</span>
												<span class="text-right"><a href="#" class="reply"><i
															class="icon-reply"></i></a></span>
											</p>
											<p>When she reached the first hills of the Italic Mountains, she had a last
												view back on the skyline of her hometown Bookmarksgrov</p>
										</div>
									</div>
									<div class="review">
										<div class="user-img" style="background-image: url(images/person_3.jpg)"></div>
										<div class="desc">
											<h4>
												<span class="text-left">Jacob Webb</span>
												<span class="text-right">25 April 2020</span>
											</h4>
											<p class="star">
												<span>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
													<i class="fa fa-star"></i>
												</span>
												<span class="text-right"><a href="#" class="reply"><i
															class="icon-reply"></i></a></span>
											</p>
											<p>When she reached the first hills of the Italic Mountains, she had a last
												view back on the skyline of her hometown Bookmarksgrov</p>
										</div>
									</div>
								</div>
								<div class="col-md-4">
									<div class="rating-wrap">
										<h3 class="mb-4">Give a Review</h3>
										<p class="star">
											<span>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												(98%)
											</span>
											<span>20 Reviews</span>
										</p>
										<p class="star">
											<span>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												(85%)
											</span>
											<span>10 Reviews</span>
										</p>
										<p class="star">
											<span>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												(98%)
											</span>
											<span>5 Reviews</span>
										</p>
										<p class="star">
											<span>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												(98%)
											</span>
											<span>0 Reviews</span>
										</p>
										<p class="star">
											<span>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												<i class="fa fa-star"></i>
												(98%)
											</span>
											<span>0 Reviews</span>
										</p>
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
	<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
	<script src="js/main.js"></script>

	<!-- SweetAlert2 JS -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		$(document).ready(function () {
			// Load cart dropdown on page load
			loadCartDropdown();

			// Quantity controls
			var quantitiy = 0;
			$('.quantity-right-plus').click(function (e) {
				e.preventDefault();
				var quantity = parseInt($('#quantity').val());
				$('#quantity').val(quantity + 1);
			});

			$('.quantity-left-minus').click(function (e) {
				e.preventDefault();
				var quantity = parseInt($('#quantity').val());
				if (quantity > 1) {  // Changed from 0 to 1 to prevent 0 quantity
					$('#quantity').val(quantity - 1);
				}
			});

			// Add to cart functionality with quantity support
			$('.add-to-cart-btn').click(function (e) {
				e.preventDefault();
				var productId = $(this).data('product-id');
				var quantity = parseInt($('#quantity').val()) || 1;

				$.post('cart_handler.php', {
					action: 'add_to_cart',
					product_id: productId,
					quantity: quantity
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
		.wishlist-btn.in-wishlist .flaticon-heart {
			color: red;
		}
	</style>

</body>

</html>