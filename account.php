<?php
include "config.php";
session_start();

$update_message = "";
$update_type = "";
$user_data = null;

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
                                    ORDER BY date DESC 
                                    LIMIT 3");
$user_orders = [];
if ($orders_result) {
    while ($row = mysqli_fetch_assoc($orders_result)) {
        $order_id = $row['id'];
        $detail_result = mysqli_query($db, "SELECT SUM(amount) AS total_items FROM detail WHERE id_transaction = '$order_id'");
        $detail_row = mysqli_fetch_assoc($detail_result);
        $row['total_items'] = $detail_row['total_items'] ?? 0;
        $user_orders[] = $row;
    }
}

if (isset($_POST["update-picture"])) {
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $max_size = 5 * 1024 * 1024;

        $file_type = $_FILES['profile_picture']['type'];
        $file_size = $_FILES['profile_picture']['size'];

        if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
            $upload_dir = 'uploads/profile_pictures/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $file_extension = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                if (!empty($user_data['profile_picture']) && file_exists($user_data['profile_picture'])) {
                    unlink($user_data['profile_picture']);
                }

                $result = mysqli_query($db, "UPDATE users SET profile_picture = '$upload_path' WHERE id = '$user_id'");
                if ($result) {
                    $update_message = "Profile picture updated successfully!";
                    $update_type = "success";
                    $user_data['profile_picture'] = $upload_path;
                } else {
                    $update_message = "Failed to save profile picture: " . mysqli_error($db);
                    $update_type = "error";
                }
            } else {
                $update_message = "Failed to upload file. Check folder permissions.";
                $update_type = "error";
            }
        } else {
            $update_message = "Invalid file type or size too large (max 5MB). Only JPG, PNG, GIF allowed.";
            $update_type = "error";
        }
    } else {
        $update_message = "Please select a valid file to upload.";
        $update_type = "error";
    }
}

if (isset($_POST["update-profile"])) {
    $fullname = mysqli_real_escape_string($db, trim($_POST["fullname"]));
    $username = mysqli_real_escape_string($db, trim($_POST["username"]));
    $phone = mysqli_real_escape_string($db, trim($_POST["phone"]));
    $address = mysqli_real_escape_string($db, trim($_POST["address"]));

    if (empty($fullname) || empty($username) || empty($phone) || empty($address)) {
        $update_message = "All fields must be filled!";
        $update_type = "error";
    } else {
        $check_username = mysqli_query($db, "SELECT id FROM users WHERE username = '$username' AND id != '$user_id'");
        
        if (mysqli_num_rows($check_username) > 0) {
            $update_message = "Username already used by another account.";
            $update_type = "error";
        } else {
            $result = mysqli_query($db, "UPDATE users SET fullname = '$fullname', username = '$username', phone = '$phone', address = '$address' WHERE id = '$user_id'");
            
            if ($result) {
                $update_message = "Profile updated successfully!";
                $update_type = "success";
                $user_data['fullname'] = $fullname;
                $user_data['username'] = $username;
                $user_data['phone'] = $phone;
                $user_data['address'] = $address;
            } else {
                $update_message = "Update failed: " . mysqli_error($db);
                $update_type = "error";
            }
        }
    }
}

if (isset($_POST["change-password"])) {
    $current_password = trim($_POST["current_password"]);
    $new_password = trim($_POST["new_password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $update_message = "All password fields must be filled!";
        $update_type = "error";
    } elseif ($new_password !== $confirm_password) {
        $update_message = "New passwords do not match!";
        $update_type = "error";
    } elseif (strlen($new_password) < 6) {
        $update_message = "New password must be at least 6 characters!";
        $update_type = "error";
    } else {
        $result = mysqli_query($db, "SELECT password FROM users WHERE id = '$user_id'");
        $stored_password = mysqli_fetch_assoc($result)['password'];

        if ($current_password === $stored_password) {
            $update_result = mysqli_query($db, "UPDATE users SET password = '$new_password' WHERE id = '$user_id'");
            
            if ($update_result) {
                $update_message = "Password changed successfully!";
                $update_type = "success";
            } else {
                $update_message = "Password change failed: " . mysqli_error($db);
                $update_type = "error";
            }
        } else {
            $update_message = "Current password is incorrect!";
            $update_type = "error";
        }
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
    return 'IDR ' . number_format($amount, 0, ',', '.');
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
									class="fa fa-chevron-right"></i></a></span> <span>My Profile <i
								class="fa fa-chevron-right"></i></span></p>
					<h2 class="mb-0 bread">My Profile</h2>
				</div>
			</div>
		</div>
	</section>

	<section class="ftco-section">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-xl-10 ftco-animate">

					<!-- Welcome Section with Profile Picture -->
					<div class="row mb-5">
						<div class="col-md-12">
							<div class="card">
								<div class="card-body text-center py-4">
									<div class="profile-picture-container mb-3">
										<?php if (!empty($user_data['profile_picture']) && file_exists($user_data['profile_picture'])): ?>
											<img src="<?php echo htmlspecialchars($user_data['profile_picture']); ?>"
												alt="Profile Picture" class="profile-picture-large">
										<?php else: ?>
											<div class="profile-picture-placeholder">
												<i class="fa fa-user fa-3x"></i>
											</div>
										<?php endif; ?>
									</div>
									<h3 class="mb-0">Welcome back,
										<?php echo htmlspecialchars($user_data['fullname'] ?? 'User'); ?>!
									</h3>
									<p class="text-muted">Manage your account information and preferences</p>
								</div>
							</div>
						</div>
					</div>

					<!-- Account Tabs -->
					<div class="row">
						<div class="col-md-12">
							<!-- Nav tabs -->
							<ul class="nav nav-tabs mb-4 justify-content-center" id="accountTabs" role="tablist">
								<li class="nav-item" role="presentation">
									<a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile"
										role="tab">
										<i class="fa fa-user mr-2"></i>Profile Information
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" id="picture-tab" data-toggle="tab" href="#picture" role="tab">
										<i class="fa fa-camera mr-2"></i>Profile Picture
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" id="password-tab" data-toggle="tab" href="#password" role="tab">
										<i class="fa fa-lock mr-2"></i>Change Password
									</a>
								</li>
								<li class="nav-item" role="presentation">
									<a class="nav-link" id="orders-tab" data-toggle="tab" href="#orders" role="tab">
										<i class="fa fa-shopping-bag mr-2"></i>Order History
									</a>
								</li>
							</ul>

							<!-- Tab content -->
							<div class="tab-content" id="accountTabsContent">

								<!-- Profile Information Tab -->
								<div class="tab-pane fade show active" id="profile" role="tabpanel">
									<form action="account.php" method="POST">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="fullname">Full Name</label>
													<input type="text" class="form-control" name="fullname"
														id="fullname"
														value="<?php echo htmlspecialchars($user_data['fullname'] ?? ''); ?>"
														required>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="phone">Phone</label>
													<input type="text" class="form-control" name="phone" id="phone"
														value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>"
														required>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="username">Username</label>
													<input type="text" class="form-control" name="username"
														id="username"
														value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>"
														required>
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="address">Address</label>
													<input type="text" class="form-control" name="address" id="address"
														value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>"
														required>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group mt-4">
													<button type="submit" class="btn btn-primary py-3 px-4"
														name="update-profile">Update Profile</button>
												</div>
											</div>
										</div>
									</form>
								</div>

								<!-- Profile Picture Tab -->
								<div class="tab-pane fade" id="picture" role="tabpanel">
									<div class="row justify-content-center">
										<div class="col-md-6">
											<div class="text-center mb-4">
												<div class="profile-picture-container">
													<?php if (!empty($user_data['profile_picture']) && file_exists($user_data['profile_picture'])): ?>
														<img src="<?php echo htmlspecialchars($user_data['profile_picture']); ?>"
															alt="Profile Picture" class="profile-picture-preview"
															id="currentProfilePic">
													<?php else: ?>
														<div class="profile-picture-placeholder-preview"
															id="placeholderPic">
															<i class="fa fa-user fa-5x text-muted"></i>
														</div>
													<?php endif; ?>
												</div>
											</div>

											<form action="account.php" method="POST" enctype="multipart/form-data">
												<div class="form-group">
													<label for="profile_picture">Choose Profile Picture</label>
													<input type="file" class="form-control-file" name="profile_picture"
														id="profile_picture" accept="image/*"
														onchange="previewImage(this)">
													<small class="form-text text-muted">
														Supported formats: JPG, PNG, GIF. Max size: 5MB
													</small>
												</div>
												<div class="form-group text-center">
													<button type="submit" class="btn btn-primary py-3 px-4"
														name="update-picture">
														<i class="fa fa-upload mr-2"></i>Update Profile Picture
													</button>
												</div>
											</form>
										</div>
									</div>
								</div>

								<!-- Change Password Tab -->
								<div class="tab-pane fade" id="password" role="tabpanel">
									<form action="account.php" method="POST">
										<div class="row">
											<div class="col-md-6">
												<div class="form-group">
													<label for="current_password">Current Password</label>
													<input type="password" class="form-control" name="current_password"
														id="current_password" required>
												</div>
											</div>
											<div class="col-md-6"></div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="new_password">New Password</label>
													<input type="password" class="form-control" name="new_password"
														id="new_password" required minlength="6">
												</div>
											</div>
											<div class="col-md-6">
												<div class="form-group">
													<label for="confirm_password">Confirm New Password</label>
													<input type="password" class="form-control" name="confirm_password"
														id="confirm_password" required minlength="6">
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group mt-4">
													<button type="submit" class="btn btn-primary py-3 px-4"
														name="change-password">Change Password</button>
												</div>
											</div>
										</div>
									</form>
								</div>


								<!-- Order History Tab -->
								<div class="tab-pane fade" id="orders" role="tabpanel">
									<div class="row">
										<div class="col-md-12">
											<h4 class="mb-4">Recent Orders</h4>

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
																			class="btn btn-outline-primary btn-sm mb-1">
																			<i class="fa fa-file-text-o mr-1"></i>View Invoice
																		</a>
																		<?php if (in_array(strtolower($order['order_status']), ['pending', 'processing'])): ?>
																			<button class="btn btn-outline-info btn-sm track-order"
																				data-order-id="<?= $order['id'] ?>">
																				<i class="fa fa-map-marker mr-1"></i>Track Order
																			</button>
																		<?php endif; ?>
																	</div>
																</div>
															</div>

															<!-- Order Items Preview -->
															<?php
															// Get order items from detail table instead of JSON
															$order_id = $order['id'];
															$items_sql = "SELECT d.amount, p.name, p.price 
              FROM detail d 
              JOIN product p ON d.id_product = p.id 
              WHERE d.id_transaction = '$order_id' 
              LIMIT 3";
															$items_result = mysqli_query($db, $items_sql);
															$order_items = [];
															while ($item = mysqli_fetch_assoc($items_result)) {
																$order_items[] = $item;
															}

															if (!empty($order_items)):
																?>
																<hr class="my-3">
																<div class="order-items-preview">
																	<h6 class="text-muted mb-2">Items ordered:</h6>
																	<div class="row">
																		<?php
																		$preview_count = 0;
																		foreach ($order_items as $item):
																			if ($preview_count >= 3)
																				break; // Show max 3 items
																			?>
																			<div class="col-md-4">
																				<small class="d-block">
																					<strong><?= htmlspecialchars($item['name']) ?></strong>
																					<br>
																					<span class="text-muted">
																						Qty: <?= $item['amount'] ?> × IDR
																						<?= number_format($item['price'], 0, ',', '.') ?>
																					</span>
																				</small>
																			</div>
																			<?php
																			$preview_count++;
																		endforeach;
																		?>
																		<?php
																		// Check if there are more items beyond the 3 displayed
																		$total_items_sql = "SELECT COUNT(*) as total_count FROM detail WHERE id_transaction = '$order_id'";
																		$total_items_result = mysqli_query($db, $total_items_sql);
																		$total_items_row = mysqli_fetch_assoc($total_items_result);
																		$total_item_count = $total_items_row['total_count'];

																		if ($total_item_count > 3):
																			?>
																			<div class="col-md-12">
																				<small class="text-muted">
																					<i class="fa fa-plus mr-1"></i>
																					And <?= $total_item_count - 3 ?> more items
																				</small>
																			</div>
																		<?php endif; ?>
																	</div>
																</div>
															<?php endif; ?>
														</div>
													</div>
												<?php endforeach; ?>

												<!-- View All Orders Button -->
												<div class="text-center mt-4">
													<a href="orders.php" class="btn btn-primary py-2 px-4">
														<i class="fa fa-list mr-2"></i>View All Orders
													</a>
												</div>
											<?php else: ?>
												<!-- Empty State -->
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

	<!-- SweetAlert2 JS -->
	<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

	<script>
		$(document).ready(function () {
			// Show SweetAlert if there's a message
			<?php if (!empty($update_message)): ?>
				<?php if ($update_type === 'success'): ?>
					Swal.fire({
						icon: 'success',
						title: 'Success!',
						text: '<?php echo addslashes($update_message); ?>',
						timer: 3000,
						timerProgressBar: true,
						showConfirmButton: false,
						toast: true,
						position: 'top-end'
					});
				<?php else: ?>
					Swal.fire({
						icon: 'error',
						title: 'Oops...',
						text: '<?php echo addslashes($update_message); ?>',
						confirmButtonColor: '#F96D00'
					});
				<?php endif; ?>
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
						// Sweet success alert
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
										<p class="mb-0">
										  <a href="#" class="price">IDR ${Number(item.price).toLocaleString('id-ID')}</a>
											  <span class="quantity ml-3">Quantity: ${item.quantity.toString().padStart(2, '0')}</span>
										</p>
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

			// Profile picture preview function
			window.previewImage = function(input) {
				if (input.files && input.files[0]) {
					var reader = new FileReader();

					reader.onload = function (e) {
						var currentPic = $('#currentProfilePic');
						var placeholder = $('#placeholderPic');

						if (currentPic.length) {
							currentPic.attr('src', e.target.result);
						} else if (placeholder.length) {
							placeholder.replaceWith(`<img src="${e.target.result}" alt="Profile Picture" class="profile-picture-preview" id="currentProfilePic">`);
						}
					}

					reader.readAsDataURL(input.files[0]);
				}
			}

			// Password confirmation validation with SweetAlert
			$('#confirm_password').on('keyup', function () {
				var newPassword = $('#new_password').val();
				var confirmPassword = $(this).val();

				if (newPassword !== confirmPassword && confirmPassword.length > 0) {
					$(this).addClass('is-invalid');
					if (!$(this).next('.invalid-feedback').length) {
						$(this).after('<div class="invalid-feedback">Passwords do not match</div>');
					}
				} else {
					$(this).removeClass('is-invalid');
					$(this).next('.invalid-feedback').remove();
				}
			});

			// Form validation with SweetAlert
			$('form').on('submit', function(e) {
				var form = $(this);
				var requiredFields = form.find('input[required]');
				var emptyFields = [];
				
				requiredFields.each(function() {
					if ($(this).val().trim() === '') {
						emptyFields.push($(this).attr('name') || $(this).attr('id'));
					}
				});

				// Check password confirmation for password change form
				if (form.find('input[name="change-password"]').length > 0) {
					var newPassword = $('#new_password').val();
					var confirmPassword = $('#confirm_password').val();
					
					if (newPassword !== confirmPassword) {
						e.preventDefault();
						Swal.fire({
							icon: 'error',
							title: 'Password Mismatch',
							text: 'New passwords do not match!',
							confirmButtonColor: '#F96D00'
						});
						return false;
					}
					
					if (newPassword.length < 6) {
						e.preventDefault();
						Swal.fire({
							icon: 'error',
							title: 'Password Too Short',
							text: 'New password must be at least 6 characters!',
							confirmButtonColor: '#F96D00'
						});
						return false;
					}
				}

				// Check file upload for picture form
				if (form.find('input[name="update-picture"]').length > 0) {
					var fileInput = $('#profile_picture')[0];
					if (!fileInput.files || fileInput.files.length === 0) {
						e.preventDefault();
						Swal.fire({
							icon: 'error',
							title: 'No File Selected',
							text: 'Please select a file to upload!',
							confirmButtonColor: '#F96D00'
						});
						return false;
					}
				}
			});

			// Track Order functionality with SweetAlert
			$('.track-order').on('click', function() {
				var orderId = $(this).data('order-id');
				
				Swal.fire({
					icon: 'info',
					title: 'Order Tracking',
					text: 'Order #' + String(orderId).padStart(6, '0') + ' is currently being processed. You will receive updates via email and SMS.',
					confirmButtonColor: '#F96D00'
				});
			});

			// Debug: Check if jQuery is working
			console.log('jQuery version:', $.fn.jquery);

			// Add to cart functionality (additional instance for consistency)
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
						// Sweet success alert
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
				}).fail(function (xhr, status, error) {
					console.error('Failed to load cart dropdown:', status, error);
				});
			}
		});
	</script>

</body>

</html>