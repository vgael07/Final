<?php
    require_once("../includes/connect.php");
    session_start();

    // Check if user is logged in and is an admin
    if(!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // Redirect to home page if not admin
        header("Location: ../index.php");
        exit();
    }

    // Get total products count
    $products_query = "SELECT COUNT(*) as total_products FROM products";
    $products_stmt = $conn->query($products_query);
    $total_products = $products_stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

    // Get total categories count
    $categories_query = "SELECT COUNT(*) as total_categories FROM categories";
    $categories_stmt = $conn->query($categories_query);
    $total_categories = $categories_stmt->fetch(PDO::FETCH_ASSOC)['total_categories'];

    // Get total subcategories count
    $subcategories_query = "SELECT COUNT(*) as total_subcategories FROM subcategories";
    $subcategories_stmt = $conn->query($subcategories_query);
    $total_subcategories = $subcategories_stmt->fetch(PDO::FETCH_ASSOC)['total_subcategories'];

    // Get total users count
    $users_query = "SELECT COUNT(*) as total_users FROM users";
    $users_stmt = $conn->query($users_query);
    $total_users = $users_stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Get recent products
    $recent_products_query = "SELECT p.*, c.name as category_name, s.name as subcategory_name 
                            FROM products p 
                            LEFT JOIN categories c ON p.category_id = c.id 
                            LEFT JOIN subcategories s ON p.subcategory_id = s.id 
                            ORDER BY p.created_at DESC 
                            LIMIT 5";
    $recent_products_stmt = $conn->query($recent_products_query);
    $recent_products = $recent_products_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
        <title>Admin Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Bootstrap icons-->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="../css/styles.css" rel="stylesheet" />
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .metric-card {
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
            }
            .metric-card:hover {
                transform: translateY(-5px);
            }
            .metric-icon {
                font-size: 2rem;
                color: #dc3545;
            }
            .metric-value {
                font-size: 1.5rem;
                font-weight: 600;
            }
            .metric-label {
                color: #6c757d;
                font-size: 0.9rem;
            }
            .quick-actions {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 1rem;
                margin-bottom: 2rem;
            }
            .quick-action-card {
                background: white;
                border-radius: 10px;
                padding: 1.5rem;
                text-align: center;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s ease;
            }
            .quick-action-card:hover {
                transform: translateY(-5px);
            }
            .quick-action-icon {
                font-size: 2rem;
                color: #dc3545;
                margin-bottom: 1rem;
            }
        </style>
    </head>
    <body class="sb-nav-fixed">
        <!-- Start of Header -->
        <?php require_once("../includes/header.php"); ?>
        <!-- End of Header -->

        <div id="layoutSidenav">
            <!-- Start of Menu -->
            <?php require_once("../includes/menu.php"); ?>
            <!-- End of Menu -->
             
            <div id="layoutSidenav_content">
                <main>
                    <div class="container-fluid px-4">
                        <h1 class="mt-4">Dashboard</h1>
                        <ol class="breadcrumb mb-4">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ol>

                        <!-- Quick Actions -->
                        <div class="quick-actions">
                            <a href="product_management.php" class="quick-action-card text-decoration-none">
                                <i class="bi bi-box quick-action-icon"></i>
                                <h5>Manage Products</h5>
                                <p class="text-muted">Add, edit, or remove products</p>
                            </a>
                            <a href="category_management.php" class="quick-action-card text-decoration-none">
                                <i class="bi bi-tags quick-action-icon"></i>
                                <h5>Manage Categories</h5>
                                <p class="text-muted">Organize your product categories</p>
                            </a>
                            <a href="subcategory_management.php" class="quick-action-card text-decoration-none">
                                <i class="bi bi-diagram-3 quick-action-icon"></i>
                                <h5>Manage Subcategories</h5>
                                <p class="text-muted">Organize your product subcategories</p>
                            </a>
                            <a href="user_management.php" class="quick-action-card text-decoration-none">
                                <i class="bi bi-people quick-action-icon"></i>
                                <h5>Manage Users</h5>
                                <p class="text-muted">View and manage user accounts</p>
                            </a>
                        </div>

                        <!-- Key Metrics -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="metric-label">Total Products</h6>
                                                <h3 class="metric-value"><?php echo number_format($total_products); ?></h3>
                                            </div>
                                            <i class="bi bi-box metric-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="metric-label">Total Categories</h6>
                                                <h3 class="metric-value"><?php echo number_format($total_categories); ?></h3>
                                            </div>
                                            <i class="bi bi-tags metric-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="metric-label">Total Subcategories</h6>
                                                <h3 class="metric-value"><?php echo number_format($total_subcategories); ?></h3>
                                            </div>
                                            <i class="bi bi-diagram-3 metric-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card metric-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="metric-label">Total Users</h6>
                                                <h3 class="metric-value"><?php echo number_format($total_users); ?></h3>
                                            </div>
                                            <i class="bi bi-people metric-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Products -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title">Recent Products</h5>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th>Product</th>
                                                        <th>Category</th>
                                                        <th>Subcategory</th>
                                                        <th>Price</th>
                                                        <th>Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($recent_products as $product): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                        <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($product['subcategory_name']); ?></td>
                                                        <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $product['status'] === 'active' ? 'success' : 'danger'; ?>">
                                                                <?php echo ucfirst($product['status']); ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php require_once("../includes/modal.php"); ?>
                </main>
                <!-- Start of Footer -->
                <?php require_once("../includes/footer.php"); ?>
                <!-- End of Footer -->
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="../js/scripts.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js" crossorigin="anonymous"></script>
    </body>
</html>
