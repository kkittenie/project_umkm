<?php
session_start();
include 'config.php';

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = array();
}

$limit = 6;
$page = $_GET['page'] ?? 1;
$offset = ($page - 1) * $limit;


$selected_category = isset($_GET['id_category']) ? $_GET['id_category'] : '';
$selected_category_name = '';
if (!empty($selected_category)) {
    $cat_name_query = "SELECT category_name FROM category WHERE id_category = '" . mysqli_real_escape_string($db, $selected_category) . "'";
    $cat_name_result = mysqli_query($db, $cat_name_query);
    if ($cat_name_row = mysqli_fetch_assoc($cat_name_result)) {
        $selected_category_name = $cat_name_row['category_name'];
    }
}


// Build query based on category filter
if (!empty($selected_category)) {
	$where_clause = "WHERE p.id_category = '$selected_category'";

	$count_where_clause = "WHERE id_category = '$selected_category'";
} else {
	$where_clause = '';
	$count_where_clause = '';
}

$total_result = mysqli_query($db, "SELECT COUNT(*) AS total FROM product $count_where_clause");
$total_row = mysqli_fetch_assoc($total_result);
$total_products = $total_row['total'];
$total_pages = ceil($total_products / $limit);

$query = "SELECT p.*, c.category_name 
          FROM product p 
          JOIN category c ON p.id_category = c.id_category 
          $where_clause 
          LIMIT $offset, $limit";
$result = mysqli_query($db, $query);

$category_query = "SELECT DISTINCT id_category FROM product WHERE id_category IS NOT NULL AND id_category != '' ORDER BY id_category";
$category_result = mysqli_query($db, $category_query);
$categories = array();
while ($cat_row = mysqli_fetch_assoc($category_result)) {
	$categories[] = $cat_row['id_category'];
}

$category_counts = array();
foreach ($categories as $category) {
	$count_query = "SELECT COUNT(*) as count FROM product WHERE id_category = '" . mysqli_real_escape_string($db, $category) . "'";
	$count_result = mysqli_query($db, $count_query);
	$count_row = mysqli_fetch_assoc($count_result);
	$category_counts[$category] = $count_row['count'];
}

$cart_count = count($_SESSION['cart']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<title>Rasa. - Products</title>
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
									class="fa fa-chevron-right"></i></a></span> <span>Products <i
								class="fa fa-chevron-right"></i></span></p>
					<h2 class="mb-0 bread">
						Products<?php if (!empty($selected_category_name))
							echo ' - ' . ucfirst($selected_category_name); ?>
					</h2>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section">
		<div class="container">
			<div class="row">
				<div class="col-md-9">
					<div class="row mb-4">
						<div class="col-md-12 d-flex justify-content-between align-items-center">
							<?php if (!empty($selected_category)): ?>
							<?php endif; ?>
						</div>
					</div>
					<div class="row">
						<?php if (mysqli_num_rows($result) > 0): ?>
							<?php while ($row = mysqli_fetch_assoc($result)): ?>
								<div class="col-md-4 d-flex">
									<div class="product ftco-animate">
										<div class="img d-flex align-items-center justify-content-center"
											style="background-image: url('images/<?= $row['photo'] ?>');">
											<div class="desc">
												<p class="meta-prod d-flex">
													<a href="#"
														class="d-flex align-items-center justify-content-center add-to-cart-btn"
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
							<?php endwhile; ?>
						<?php else: ?>
							<div class="col-md-12 text-center">
								<div class="alert alert-info">
									<h4>No products found</h4>
									<p>There are no products
										available<?php if (!empty($selected_category_name))
											echo ' in the "' . ucfirst($selected_category_name) . '" category'; ?>.
									</p>
									<?php if (!empty($selected_category)): ?>
										<a href="?" class="btn btn-primary">View All Products</a>
									<?php endif; ?>
								</div>
							</div>
						<?php endif; ?>
					</div>

					<?php if ($total_pages > 1): ?>
						<div class="row mt-5">
							<div class="col text-center">
								<div class="block-27">
									<ul>
										<?php if ($page > 1): ?>
											<li><a
													href="?<?php echo http_build_query(array_merge($_GET, array('page' => $page - 1))); ?>">&lt;</a>
											</li>
										<?php endif; ?>

										<?php for ($i = 1; $i <= $total_pages; $i++): ?>
											<?php if ($i == $page): ?>
												<li class="active"><span><?= $i ?></span></li>
											<?php else: ?>
												<li><a
														href="?<?php echo http_build_query(array_merge($_GET, array('page' => $i))); ?>"><?= $i ?></a>
												</li>
											<?php endif; ?>
										<?php endfor; ?>

										<?php if ($page < $total_pages): ?>
											<li><a
													href="?<?php echo http_build_query(array_merge($_GET, array('page' => $page + 1))); ?>">&gt;</a>
											</li>
										<?php endif; ?>
									</ul>
								</div>
							</div>
						</div>
					<?php endif; ?>

				</div>

				<div class="col-md-3">
					<div class="sidebar-box ftco-animate">
						<div class="categories">
							<h3>Category</h3>
							<ul class="p-0">
								<li>
									<a href="?"
										class="<?php echo empty($selected_category) ? 'active-category' : ''; ?>">
										All Products <span class="fa fa-chevron-right"></span>
									</a>
								</li>
								<?php
								$cat_query = "SELECT c.id_category, c.category_name, COUNT(p.id) as total 
            								  FROM category c 
            								  LEFT JOIN product p ON c.id_category = p.id_category 
            								  GROUP BY c.id_category, c.category_name";
								$cat_result = mysqli_query($db, $cat_query);
								while ($cat = mysqli_fetch_assoc($cat_result)) {
									?>
									<li>
										<a href="?id_category=<?= $cat['id_category'] ?>">
											<?= $cat['category_name'] ?> <span>(<?= $cat['total'] ?>)</span>
										</a>
									</li>
								<?php } ?>
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
										<p class="mb-0"><a href="#" class="price">IDR ${item.price.toLocaleString('id-ID')}</a><span class="quantity ml-3">Quantity: ${item.quantity.toString().padStart(2, '0')}</span></p>								</div>
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