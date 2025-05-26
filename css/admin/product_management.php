<?php
session_start();
include('../includes/connect.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Fetch main categories for filter
$cat_query = "SELECT * FROM categories ORDER BY name";
$cat_stmt = $conn->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subcategories for filter
$subcat_query = "SELECT s.*, c.name as main_category_name 
                 FROM subcategories s 
                 JOIN categories c ON s.category_id = c.id 
                 ORDER BY c.name, s.name";
$subcat_stmt = $conn->prepare($subcat_query);
$subcat_stmt->execute();
$subcategories = $subcat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Build the query with filters
$where_conditions = [];
$params = [];

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $where_conditions[] = "p.category_id = :category";
    $params[':category'] = $_GET['category'];
}

if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
    $where_conditions[] = "p.subcategory_id = :subcategory";
    $params[':subcategory'] = $_GET['subcategory'];
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $where_conditions[] = "p.status = :status";
    $params[':status'] = $_GET['status'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(p.name LIKE :search OR p.description LIKE :search)";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Pagination settings
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure page is at least 1

// Get total number of products for pagination
$count_query = "SELECT COUNT(*) as total FROM products p $where_clause";
$count_stmt = $conn->prepare($count_query);
foreach ($params as $key => $value) {
    $count_stmt->bindValue($key, $value);
}
$count_stmt->execute();
$total_items = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$total_pages = ceil($total_items / $items_per_page);

// Modify the main query to include pagination
$query = "SELECT p.*, c.name as category_name, s.name as subcategory_name 
          FROM products p 
          LEFT JOIN categories c ON p.category_id = c.id 
          LEFT JOIN subcategories s ON p.subcategory_id = s.id 
          $where_clause 
          ORDER BY p.id DESC 
          LIMIT :offset, :limit";

$stmt = $conn->prepare($query);

// Bind all parameters
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

// Bind pagination parameters
$offset = ($current_page - 1) * $items_per_page;
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add JavaScript for dynamic subcategory filtering
$js_subcategories = json_encode($subcategories);
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
    <title>Product Management</title>
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <!-- Bootstrap icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="../css/styles.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f4f4f4;
        }
        .section-title {
            margin-top: 30px;
            margin-bottom: 15px;
            font-size: 1.5rem;
            font-weight: bold;
            color: #333;
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .product-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            background: #fff;
            margin-bottom: 24px;
        }
        .product-card .card-body {
            padding: 1.5rem;
        }
        .product-actions .btn {
            margin-right: 8px;
        }
        .product-image {
            width: 35px;
            height: 35px;
            object-fit: cover;
            border-radius: 1px;
        }
        .filter-section {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .input-group-text {
            border: 1px solid #dee2e6;
        }
        .input-group .form-control,
        .input-group .form-select {
            border-left: 0;
        }
        .input-group .form-control:focus,
        .input-group .form-select:focus {
            border-color: #dee2e6;
            box-shadow: none;
        }
        .input-group:focus-within {
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .card-header {
            border-bottom: 1px solid rgba(0,0,0,.125);
        }
        .btn-outline-secondary:hover {
            background-color: #f8f9fa;
        }
        .subcategory-select {
            display: none;
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
                <div class="container-fluid px-4 mt-5">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Product Management</li>
                    </ol>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title mb-0">Product Management</h2>
                        <a href="add_product.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle text-light"></i> Add Product
                        </a>
                    </div>

                    <!-- Filters Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Products</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" class="form-control" name="search" 
                                               placeholder="Search by name or description..." 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-tag"></i>
                                        </span>
                                        <select class="form-select" name="category" id="categoryFilter">
                                            <option value="">All Categories</option>
                                            <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" 
                                                        <?php echo (isset($_GET['category']) && $_GET['category'] == $category['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-tag-fill"></i>
                                        </span>
                                        <select class="form-select" name="subcategory" id="subcategoryFilter">
                                            <option value="">All Subcategories</option>
                                            <?php foreach($subcategories as $subcategory): ?>
                                                <option value="<?php echo $subcategory['id']; ?>" 
                                                        data-category="<?php echo $subcategory['category_id']; ?>"
                                                        <?php echo (isset($_GET['subcategory']) && $_GET['subcategory'] == $subcategory['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($subcategory['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-toggle-on"></i>
                                        </span>
                                        <select class="form-select" name="status">
                                            <option value="">All Status</option>
                                            <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-funnel-fill text-light"></i> Filter
                                        </button>
                                        <a href="product_management.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Clear
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Products Table -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Subcategory</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($products)): ?>
                                            <tr>
                                                <td colspan="8" class="text-center">No products found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($products as $product): ?>
                                                <tr>
                                                    <td>
                                                        <?php if (!empty($product['image'])): ?>
                                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($product['image']); ?>" 
                                                                 class="product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                                                        <?php else: ?>
                                                            <img src="../assets/img/no-image.png" class="product-image" alt="No Image">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                                    <td><?php echo htmlspecialchars($product['subcategory_name'] ?? 'N/A'); ?></td>
                                                    <td>₱<?php echo number_format($product['price'], 2); ?></td>
                                                    <td><?php echo $product['quantity']; ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $product['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                            <?php echo ucfirst($product['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="bi bi-pencil text-light"></i> Edit
                                                        </a>
                                                        <form action="../includes/product_actions.php" method="POST" style="display: inline;">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                                    onclick="return confirm('Are you sure you want to delete this product?');">
                                                                <i class="bi bi-trash text-light"></i> Delete
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <!-- Previous button -->
                                    <li class="page-item <?php echo $current_page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page - 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory=' . $_GET['subcategory'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>

                                    <!-- Page numbers -->
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?php echo $current_page == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory=' . $_GET['subcategory'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <!-- Next button -->
                                    <li class="page-item <?php echo $current_page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $current_page + 1; ?><?php echo isset($_GET['category']) ? '&category=' . $_GET['category'] : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory=' . $_GET['subcategory'] : ''; ?><?php echo isset($_GET['status']) ? '&status=' . $_GET['status'] : ''; ?><?php echo isset($_GET['search']) ? '&search=' . $_GET['search'] : ''; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
            <!-- Start of Footer -->
            <?php require_once("../includes/footer.php"); ?>
            <!-- End of Footer -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Core theme JS-->
    <script src="../js/scripts.js"></script>
    <script>
        // Dynamic subcategory filtering
        document.addEventListener('DOMContentLoaded', function() {
            const categoryFilter = document.getElementById('categoryFilter');
            const subcategoryFilter = document.getElementById('subcategoryFilter');
            const subcategories = <?php echo $js_subcategories; ?>;
            
            function updateSubcategories() {
                const selectedCategory = categoryFilter.value;
                const options = subcategoryFilter.options;
                
                // Hide all options first
                for (let i = 0; i < options.length; i++) {
                    options[i].style.display = 'none';
                }
                
                // Show only relevant subcategories
                for (let i = 0; i < options.length; i++) {
                    const option = options[i];
                    if (!selectedCategory || option.value === '' || option.dataset.category === selectedCategory) {
                        option.style.display = '';
                    }
                }
                
                // Reset subcategory selection if the current selection is not valid
                if (selectedCategory && subcategoryFilter.value) {
                    const selectedOption = subcategoryFilter.options[subcategoryFilter.selectedIndex];
                    if (selectedOption.dataset.category !== selectedCategory) {
                        subcategoryFilter.value = '';
                    }
                }
            }
            
            categoryFilter.addEventListener('change', updateSubcategories);
            updateSubcategories(); // Initial update
        });
    </script>
</body>
</html>