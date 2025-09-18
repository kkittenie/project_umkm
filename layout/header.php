<?php
include 'config.php';

if (!isset($_SESSION['cart'])) {
	$_SESSION['cart'] = array();
}

$cart_count = count($_SESSION['cart']);

$is_logged_in = isset($_SESSION['user_id']) || isset($_SESSION['username']);

$current_page = basename($_SERVER['PHP_SELF'], '.php');

?>

<!DOCTYPE html>
<html lang="en">

<head>
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

	<style>
		/* Hide cart and profile on mobile when menu is collapsed */
		@media (max-width: 991.98px) {
			.mobile-user-actions {
				display: none !important;
			}
			
			/* Show cart and profile only when menu is expanded on mobile */
			.navbar-collapse.show ~ .mobile-user-actions,
			.navbar-collapse.collapsing ~ .mobile-user-actions {
				display: none !important;
			}
		}
		
		/* Ensure proper spacing on desktop */
		@media (min-width: 992px) {
			.mobile-user-actions {
				display: flex !important;
			}
		}
	</style>

</head>

<body>
	<div class="wrap">
		<div class="container">
			<div class="row">
				<div class="col-md-6 d-flex align-items-center">
					<p class="mb-0 phone pl-md-2">
						<a href="#" class="mr-2"><span class="fa fa-phone mr-1"></span> +62 882 0007 68044</a>
						<a href="#"><span class="fa fa-paper-plane mr-1"></span> rasa@gmail.com</a>
					</p>
				</div>
				<div class="col-md-6 d-flex justify-content-md-end">
					<div class="social-media mr-4">
						<p class="mb-0 d-flex">
							<a href="#" class="d-flex align-items-center justify-content-center"><span
									class="fa fa-facebook"><i class="sr-only">Facebook</i></span></a>
							<a href="#" class="d-flex align-items-center justify-content-center"><span
									class="fa fa-twitter"><i class="sr-only">Twitter</i></span></a>
							<a href="https://instagram.com/_shortccake"
								class="d-flex align-items-center justify-content-center"><span
									class="fa fa-instagram"><i class="sr-only">Instagram</i></span></a>
							<a href="#" class="d-flex align-items-center justify-content-center"><span
									class="fa fa-dribbble"><i class="sr-only">Dribbble</i></span></a>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>

		<nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
			<div class="container">
				<a class="navbar-brand" href="index.php">Rasa.<span></span></a>
				
				<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav"
					aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
					<span class="oi oi-menu"></span> Menu
				</button>

				<div class="collapse navbar-collapse" id="ftco-nav">
					<ul class="navbar-nav ml-auto">
						<li class="nav-item <?= ($current_page == 'index') ? 'active' : '' ?>">
							<a href="index.php" class="nav-link">Home</a>
						</li>
						<li class="nav-item <?= ($current_page == 'about') ? 'active' : '' ?>">
							<a href="about.php" class="nav-link">About</a>
						</li>
						<li class="nav-item dropdown <?= (in_array($current_page, ['product', 'cart', 'checkout', 'orders'])) ? 'active' : '' ?>">
							<a class="nav-link dropdown-toggle" href="#" id="dropdown04" data-toggle="dropdown"
								aria-haspopup="true" aria-expanded="false">Products</a>
							<div class="dropdown-menu" aria-labelledby="dropdown04">
								<a class="dropdown-item" href="product.php">Products</a>
								<a class="dropdown-item" href="cart.php">Cart</a>
								<a class="dropdown-item" href="checkout.php">Checkout</a>
								<a class="dropdown-item" href="orders.php">Orders</a>
							</div>
						</li>
						<li class="nav-item <?= ($current_page == 'contact') ? 'active' : '' ?>">
							<a href="contact.php" class="nav-link">Contact</a>
						</li>
						
						<!-- Mobile-only cart and profile links in collapsed menu -->
						<li class="nav-item d-lg-none">
							<?php if ($is_logged_in): ?>
								<a href="account.php" class="nav-link">
									<i class="fa fa-user mr-2"></i> My Profile
								</a>
							<?php else: ?>
								<a href="sign-up.php" class="nav-link">
									<i class="fa fa-user mr-2"></i> Sign Up / Login
								</a>
							<?php endif; ?>
						</li>
						<li class="nav-item d-lg-none">
							<a href="cart.php" class="nav-link">
								<i class="flaticon-shopping-bag mr-2"></i> Cart (<?= $cart_count ?>)
							</a>
						</li>
						<?php if ($is_logged_in): ?>
						<li class="nav-item d-lg-none">
							<a href="logout.php" class="nav-link">
								<i class="fa fa-sign-out mr-2"></i> Logout
							</a>
						</li>
						<?php endif; ?>
					</ul>
				</div>
				
				<!-- Desktop-only cart and profile dropdowns -->
				<div class="order-lg-last d-flex align-items-center mobile-user-actions">
					<!-- Profile Dropdown Section -->
					<div class="btn-group mr-3">
						<?php if ($is_logged_in): ?>
							<!-- Profile Dropdown for Logged In Users -->
							<a href="#" class="btn-user dropdown-toggle d-flex align-items-center justify-content-center" 
							   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<span class="fa fa-user profile-icon"></span>
							</a>
							<div class="dropdown-menu dropdown-menu-right">
								<a class="dropdown-item" href="account.php">
									<i class="fa fa-user mr-2"></i> My Profile
								</a>
								<div class="dropdown-divider"></div>
								<a class="dropdown-item" href="logout.php">
									<i class="fa fa-sign-out mr-2"></i> Logout
								</a>
							</div>
						<?php else: ?>
							<!-- Sign Up/Login for Guest Users -->
							<a href="sign-up.php" class="btn-user d-flex align-items-center justify-content-center">
								<span class="fa fa-user profile-icon"></span>
							</a>
						<?php endif; ?>
					</div>
					
					<!-- Cart Dropdown Section -->
					<div class="btn-group">
						<a href="#" class="btn-cart dropdown-toggle dropdown-toggle-split" data-toggle="dropdown"
							aria-haspopup="true" aria-expanded="false">
							<span class="flaticon-shopping-bag"></span>
							<div class="d-flex justify-content-center align-items-center"><small
									id="cart-count"><?= $cart_count ?></small></div>
						</a>
						<div class="dropdown-menu dropdown-menu-right" id="cart-dropdown">
						</div>
					</div>
				</div>
			</div>
	</nav>

	<script>
		// Optional: Auto-close mobile menu when clicking on menu items
		document.addEventListener('DOMContentLoaded', function() {
			const navLinks = document.querySelectorAll('.navbar-nav .nav-link');
			const navbarCollapse = document.querySelector('.navbar-collapse');
			
			navLinks.forEach(link => {
				link.addEventListener('click', function() {
					if (window.innerWidth < 992) {
						// Close the mobile menu
						if (navbarCollapse.classList.contains('show')) {
							const toggleButton = document.querySelector('.navbar-toggler');
							toggleButton.click();
						}
					}
				});
			});
		});
	</script>
</body>

</html>