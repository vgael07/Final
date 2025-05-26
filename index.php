<?php
    require_once("includes/connect.php");

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Query to get categories with products
    $sql = "SELECT 
            c.id as category_id,
            c.name as category_name,
            c.image as category_image,
            COUNT(p.id) as product_count,
            MIN(p.created_at) as latest_product
        FROM categories c
        LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active'
        GROUP BY c.id, c.name, c.image
        HAVING product_count > 0
        ORDER BY latest_product DESC
        LIMIT 4";
    
    $result = $conn->query($sql);

    // Fetch one featured product per category
    $featured_sql = "WITH RankedProducts AS (
                        SELECT p.*, c.name as category_name,
                        ROW_NUMBER() OVER (PARTITION BY p.category_id ORDER BY p.created_at DESC) as rn
                        FROM products p
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE p.status = 'active'
                    )
                    SELECT * FROM RankedProducts WHERE rn = 1
                    ORDER BY category_name ASC";
    
    $featured_stmt = $conn->prepare($featured_sql);
    $featured_stmt->execute();
    $featured_products = $featured_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Goldilocks - Philippines</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
</head>

<body class="d-flex flex-column h-100">
    <main class="flex-shrink-0">
        <!-- Navigation-->
        <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #ffd700;">
            <div class="container px-5">
                <img src="Goldilocks1.png" alt="Logo" width="200" height="50">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
                    aria-expanded="false" aria-label="Toggle navigation"><span
                        class="navbar-toggler-icon"></span></button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="index.html"><b>MENU</b></a></li>
                        <li class="nav-item"><a class="nav-link" href="about.html"><b>LOCATION</b></a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.html"><b>PROMO</b></a></li>
                        <li class="nav-item"><a class="nav-link" href="pricing.html"><b>FRANCHISING</b></a></li>
                        <li class="nav-item"><a class="nav-link" href="pricing.html"><b>CAREERS</b></a></li>
                        <li class="nav-item"><a class="nav-link" href="pricing.html"><b>ORDER ONLINE</b></a></li>
                        <li class="nav-item"><img src="search2.png" alt="Logo" width="23" height="23"></li>
                        <li class="nav-item"><a class="nav-link" href="pricing.html" img src="search.png"></a></li>
                    </ul>
                </div>
            </div>
        </nav>
        <!-- Header-->
        <header>
            <a href="order.php">
                <img src="Corp-Desktop_1.jpg" alt="Banner" style="width: 100%; height: auto;">
            </a>
        </header>


            <div class="py-5 bg-light">
            <div class="container px-5 my-5">
                <div class="row gx-5 justify-content-center">
                <div class="col-lg-10 col-xl-7">
                    <div class="text-center">
                    <h5 class="mb-4">FEATURED SELECTIONS</h5>
                    <div id="mainCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">

                        <div class="carousel-item active">
                            <div class="row justify-content-center text-center g-3">
                            <div class="col">
                                <img src="cake1.png" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Cake 1">
                            </div>&nbsp;
                            <div class="col">
                                <img src="cake2.png" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Cake 2">
                            </div>&nbsp;
                            <div class="col">
                                <img src="boneless.png" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Boneless">
                            </div>&nbsp;
                            <div class="col">
                                <img src="bbq2.png" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="BBQ">
                            </div>&nbsp;
                            <div class="col">
                                <img src="kare-kare.png" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Kare-Kare">
                            </div>&nbsp;
                            </div>
                        </div>

                        <div class="carousel-item">
                            <div class="row justify-content-center text-center g-3">
                            <div class="col">
                                <img src="picture6.jpg" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Cake 1">
                            </div>&nbsp;
                            <div class="col">
                                <img src="picture7.jpg" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Cake 2">
                            </div>&nbsp;
                            <div class="col">
                                <img src="picture8.png" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Boneless">
                            </div>&nbsp;
                            <div class="col">
                                <img src="picture9.jpg" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="BBQ">
                            </div>&nbsp;
                            <div class="col">
                                <img src="picture10.jpg" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Kare-Kare">
                            </div>&nbsp;
                            </div>
                        </div>

                            <div class="carousel-item">
                            <div class="row justify-content-center text-center g-3">
                            <div class="col">
                                <img src="picture9.jpg" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Cake 1">
                            </div>&nbsp;
                            <div class="col">
                                <img src="picture10.jpg" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Cake 2">
                            </div>&nbsp;
                            <div class="col">
                                <img src="picture11.jpg" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Boneless">
                            </div>&nbsp;
                            <div class="col">
                                <img src="picture12.png" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="BBQ">
                            </div>&nbsp;
                            <div class="col">
                                <img src="picture13.jpg" class="rounded-circle mx-auto d-block img-thumbnail" style="width: 200px; height: 130px; object-fit: cover;" alt="Kare-Kare">
                            </div>&nbsp;
                            </div>
                        </div>

                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#mainCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#mainCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                        </button>

                    </div>
                    </div>
                </div>
                </div>
            </div>
            </div>

        <!-- Features section-->
        <section class="py-5" id="features">
            <div class="container my-5">
            <div class="row justify-content-center g-4 text-center">
                <!-- Card 1 -->
                <div class="col-md-4">
                <a href="cakes.html" class="image-card-link">
                    <img src="cake1.png" alt="Cakes" class="custom-image shadow-sm">
                </a>
                </div>

                <!-- Card 2 -->
                <div class="col-md-4">
                <a href="foodshop.html" class="image-card-link">
                    <img src="picture14.png" alt="Foodshop" class="custom-image shadow-sm">
                </a>
                </div>

                <!-- Card 3 -->
                <div class="col-md-4">
                <a href="celebration.html" class="image-card-link">
                    <img src="picture15.jpg" alt="Celebrate" class="custom-image shadow-sm">
                </a>
                </div>
            </div>
            </div>
        </section>
                    <!-- Testimonial section-->
            <div class="banner-photo">
                <div class="logo">
                    <img src="https://www.goldilocks.com.ph/upload/files/GDS%20PNG%20no%20txt.png" alt="Goldilocks Logo">
                    <div>Order from Goldilocks Delivery and have your <br>
                        favorite Pinoy products delivered at your doorstep!</div>
                    <a href="order.php" class="order-btn draw">Order Now</a>
                </div>
            </div>
            <div class="banner-photo" data-original="" style="background-image: url('https://www.goldilocks.com.ph/upload/files/Custom%20Cakes%20Banner%20Web.png');">
                    <div class="banner-wrapper-left">
                        <div class="logo">
                        <img src="https://www.goldilocks.com.ph/upload/files/2023/Goldilocks%20Custom%20Cakes.png">
                        </div>
                     <div class="banner-text">Celebrate every milestone with a customized cake from Goldilocks</div>
                         <a href="https://www.goldilocks.com.ph/custom-cakes" class="btn btn-default order-btn">Order Now</a>
                        <button class="draw meet" onclick="window.open('https://www.goldilocks.com.ph/custom-cakes')">Learn More </button>
                    </div>
            </div>
    </main>
    <!-- Footer-->
  <footer class="footer">
            <div class="container px-5">
                <div class="row align-items-center justify-content-between flex-column flex-sm-row">
                    <div class="col-auto"><div class="small m-0 text-white">Copyright &copy; Your Website 2023</div></div>
                    <div class="col-auto">
                        <a class="link-light small" href="#!">Privacy</a>
                        <span class="text-white mx-1">&middot;</span>
                        <a class="link-light small" href="#!">Terms</a>
                        <span class="text-white mx-1">&middot;</span>
                        <a class="link-light small" href="#!">Contact</a>
                    </div>
                </div>
            </div>
  </footer>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
</body>

</html>