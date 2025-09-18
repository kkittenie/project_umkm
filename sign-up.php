<?php
include "config.php";
session_start();

// Redirect jika sudah login
if (isset($_SESSION["is_login"])) {
    header("Location: account.php");
    exit();
}

if (isset($_POST["sign-up"])) {
    $fullname = $_POST["fullname"];
    $username = $_POST["username"];
    $phone = $_POST["phone"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $password = $_POST["password"];

    if (empty($fullname) || empty($username) || empty($phone) || empty($email) || empty($password)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'All fields must be filled in!',
                    confirmButtonColor: '#F96D00',
                    customClass: {
                        popup: 'swal-font-spectral'
                    }
                });
            });
        </script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Email invalid!',
                    confirmButtonColor: '#F96D00',
                    customClass: {
                        popup: 'swal-font-spectral'
                    }
                });
            });
        </script>";
    } elseif (strlen($password) < 6) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Please enter a minimum of 6 characters passwords.',
                    confirmButtonColor: '#F96D00',
                    customClass: {
                        popup: 'swal-font-spectral'
                    }
                });
            });
        </script>";
    } else {
        // Check if username or email already exists
        $check_query = "SELECT username, email FROM users WHERE username='$username' OR email='$email'";
        $check_result = mysqli_query($db, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $existing_data = mysqli_fetch_assoc($check_result);
            if ($existing_data['username'] === $username) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Username Already Taken!',
                            text: 'This username is already in use. Please choose a different one.',
                            confirmButtonColor: '#F96D00',
                            customClass: {
                                popup: 'swal-font-spectral'
                            }
                        });
                    });
                </script>";
            } elseif ($existing_data['email'] === $email) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Email Already Registered!',
                            text: 'This email is already registered. Please use a different email or try logging in.',
                            confirmButtonColor: '#F96D00',
                            customClass: {
                                popup: 'swal-font-spectral'
                            }
                        });
                    });
                </script>";
            }
        } else {
            // Proceed with insertion since no duplicates found
            $sql = "INSERT INTO users (fullname, username, phone, email, address, password) 
                    VALUES ('$fullname', '$username', '$phone', '$email', '$address', '$password')";
            
            if (mysqli_query($db, $sql)) {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Registration successful! 🎉',
                            confirmButtonText: 'Go to Login',
                            confirmButtonColor: '#F96D00',
                            customClass: {
                                popup: 'swal-font-spectral'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'login.php';
                            }
                        });
                    });
                </script>";
            } else {
                echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed!',
                            text: 'An unexpected error occurred. Please try again later.',
                            confirmButtonColor: '#F96D00',
                            customClass: {
                                popup: 'swal-font-spectral'
                            }
                        });
                    });
                </script>";
            }
        }

        mysqli_close($db);
    }
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

$cart_count = count($_SESSION['cart']);
?>


<body>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <title>Rasa. - Sign up</title>
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
                                        class="fa fa-chevron-right"></i></a></span> <span>Sign Up <i
                                    class="fa fa-chevron-right"></i></span></p>
                        <h2 class="mb-0 bread">Sign Up</h2>
                    </div>
                </div>
            </div>
        </section>

        <section class="ftco-section">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-10 ftco-animate">
                        <div class="row align-items-end">
                            <div class="col-md-12">
                                <form action="sign-up.php" method="POST">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="fullname">Full Name</label>
                                                <input type="text" class="form-control" name="fullname" id="fullname"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="username">Username</label>
                                                <input type="text" class="form-control" name="username" id="username"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <input type="text" class="form-control" name="phone" id="phone"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email Address</label>
                                                <input type="email" class="form-control" name="email" id="email"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="address">Address</label>
                                                <input type="text" class="form-control" name="address" id="address"
                                                    required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="password">Password</label>
                                                <input type="password" class="form-control" name="password"
                                                    id="password" required minlength="6">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group mt-4">
                                                <button type="submit" class="btn btn-primary py-3 px-4"
                                                    name="sign-up">Sign Up</button>
                                            </div>
                                        </div>
                                        <div class="col-md-12 text-center mt-3">
                                            <p>Already have an account? <a href="login.php">Login here</a></p>
                                        </div>
                                    </div>
                                </form>
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
        <script
            src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
        <!-- SweetAlert2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="js/main.js"></script>

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
                            position: 'top-end',
                            customClass: {
                                popup: 'swal-font-spectral'
                            }
                        });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message,
                                confirmButtonColor: '#F96D00',
                                customClass: {
                                    popup: 'swal-font-spectral'
                                }
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
                                    <p class="mb-0"><a href="#" class="price">IDR ${Number(item.price).toLocaleString('id-ID')}</span></p>
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
            .swal-font-spectral {
                font-family: 'Spectral', serif !important;
            }
            
            .swal2-popup.swal-font-spectral {
                font-family: 'Spectral', serif !important;
            }
            
            .swal2-title {
                font-family: 'Spectral', serif !important;
                font-weight: 500 !important;
            }
            
            .swal2-html-container {
                font-family: 'Spectral', serif !important;
                font-weight: 300 !important;
            }
        </style>

    </body>

    </html>