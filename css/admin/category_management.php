<?php
session_start();
include('../includes/connect.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Add image column if it doesn't exist
try {
    $check_column = "SHOW COLUMNS FROM categories LIKE 'image'";
    $result = $conn->query($check_column);
    if ($result->rowCount() == 0) {
        $add_column = "ALTER TABLE categories ADD COLUMN image LONGBLOB NULL AFTER name";
        $conn->exec($add_column);
    }
} catch (PDOException $e) {
    // Column might already exist, continue with the page
}

// Pagination settings
$items_per_page = 10;
$main_page = isset($_GET['main_page']) ? (int)$_GET['main_page'] : 1;
$sub_page = isset($_GET['sub_page']) ? (int)$_GET['sub_page'] : 1;

// Calculate offsets
$main_offset = ($main_page - 1) * $items_per_page;
$sub_offset = ($sub_page - 1) * $items_per_page;

// Get total count of main categories
$main_count_query = "SELECT COUNT(*) as total FROM categories";
$main_count_stmt = $conn->prepare($main_count_query);
$main_count_stmt->execute();
$main_total = $main_count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$main_total_pages = ceil($main_total / $items_per_page);

// Get total count of subcategories
$sub_count_query = "SELECT COUNT(*) as total FROM subcategories";
$sub_count_stmt = $conn->prepare($sub_count_query);
$sub_count_stmt->execute();
$sub_total = $sub_count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
$sub_total_pages = ceil($sub_total / $items_per_page);

// Fetch main categories with pagination
$main_cat_query = "SELECT * FROM categories ORDER BY name LIMIT :limit OFFSET :offset";
$main_cat_stmt = $conn->prepare($main_cat_query);
$main_cat_stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$main_cat_stmt->bindValue(':offset', $main_offset, PDO::PARAM_INT);
$main_cat_stmt->execute();
$main_categories = $main_cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subcategories with their main category names and pagination
$sub_cat_query = "SELECT s.*, c.name as main_category_name 
                 FROM subcategories s 
                 JOIN categories c ON s.category_id = c.id 
                 ORDER BY c.name, s.name 
                 LIMIT :limit OFFSET :offset";
$sub_cat_stmt = $conn->prepare($sub_cat_query);
$sub_cat_stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
$sub_cat_stmt->bindValue(':offset', $sub_offset, PDO::PARAM_INT);
$sub_cat_stmt->execute();
$sub_categories = $sub_cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to generate pagination links
function generatePaginationLinks($current_page, $total_pages, $page_param) {
    $links = '';
    $query_params = $_GET;
    
    if ($total_pages > 1) {
        $links .= '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        $prev_disabled = $current_page <= 1 ? 'disabled' : '';
        $query_params[$page_param] = $current_page - 1;
        $prev_link = '?' . http_build_query($query_params);
        $links .= '<li class="page-item ' . $prev_disabled . '"><a class="page-link" href="' . $prev_link . '">Previous</a></li>';
        
        // Page numbers
        for ($i = 1; $i <= $total_pages; $i++) {
            $active = $current_page == $i ? 'active' : '';
            $query_params[$page_param] = $i;
            $page_link = '?' . http_build_query($query_params);
            $links .= '<li class="page-item ' . $active . '"><a class="page-link" href="' . $page_link . '">' . $i . '</a></li>';
        }
        
        // Next button
        $next_disabled = $current_page >= $total_pages ? 'disabled' : '';
        $query_params[$page_param] = $current_page + 1;
        $next_link = '?' . http_build_query($query_params);
        $links .= '<li class="page-item ' . $next_disabled . '"><a class="page-link" href="' . $next_link . '">Next</a></li>';
        
        $links .= '</ul></nav>';
    }
    
    return $links;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Category Management</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <style>
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
        /* Styles for the table image and actions */
        .product-image { /* Reusing this class from product_management, consider renaming to .item-image if also used for categories with images */
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
        .product-actions .btn { /* Reusing this class, consider renaming to .item-actions */
            margin-right: 8px;
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
                        <li class="breadcrumb-item active">Category Management</li>
                    </ol>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_GET['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title mb-0">Category Management</h2>
                        <div>
                            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#addMainCategoryModal">
                                <i class="bi bi-plus-circle text-light"></i> Add Main Category
                            </button>
                            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addSubCategoryModal">
                                <i class="bi bi-plus-circle text-light"></i> Add Subcategory
                            </button>
                        </div>
                    </div>

                    <!-- Main Categories Table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Main Categories</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($main_categories)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No categories found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($main_categories as $category): ?>
                                                <tr>
                                                    <td><?php echo $category['id']; ?></td>
                                                    <td>
                                                        <?php if (!empty($category['image'])): ?>
                                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($category['image']); ?>" 
                                                                 alt="<?php echo htmlspecialchars($category['name']); ?>" 
                                                                 class="product-image">
                                                        <?php else: ?>
                                                            <img src="../assets/placeholder.png" 
                                                                 alt="No image" 
                                                                 class="product-image">
                                                        <?php endif; ?>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $category['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                            <?php echo ucfirst($category['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editMainCategoryModal<?php echo $category['id']; ?>">
                                                            <i class="bi bi-pencil text-light"></i> Edit
                                                        </button>
                                                        <a href="../includes/category_actions.php?action=delete&id=<?php echo $category['id']; ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Are you sure you want to delete this category? This will also delete all its subcategories.');">
                                                            <i class="bi bi-trash text-light"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>

                                                <!-- Edit Main Category Modal -->
                                                <div class="modal fade" id="editMainCategoryModal<?php echo $category['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Main Category</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="../includes/category_actions.php" method="POST" enctype="multipart/form-data">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="action" value="update_main">
                                                                    <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Category Name</label>
                                                                        <input type="text" class="form-control" name="name" 
                                                                               value="<?php echo htmlspecialchars($category['name']); ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Category Image</label>
                                                                        <?php if (!empty($category['image'])): ?>
                                                                            <div class="mb-2">
                                                                                <img src="data:image/jpeg;base64,<?php echo base64_encode($category['image']); ?>" 
                                                                                     alt="Current category image" 
                                                                                     class="img-thumbnail" 
                                                                                     style="max-height: 200px;">
                                                                            </div>
                                                                        <?php endif; ?>
                                                                        <input type="file" class="form-control" name="image" accept="image/*">
                                                                        <small class="text-muted">Recommended size: 800x600 pixels. Max file size: 2MB</small>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Status</label>
                                                                        <select class="form-select" name="status" required>
                                                                            <option value="active" <?php echo $category['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                            <option value="inactive" <?php echo $category['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Update Category</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php echo generatePaginationLinks($main_page, $main_total_pages, 'main_page'); ?>
                        </div>
                    </div>

                    <!-- Subcategories Table -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Subcategories</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Main Category</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($sub_categories)): ?>
                                            <tr>
                                                <td colspan="5" class="text-center">No subcategories found.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($sub_categories as $subcategory): ?>
                                                <tr>
                                                    <td><?php echo $subcategory['id']; ?></td>
                                                    <td><?php echo htmlspecialchars($subcategory['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($subcategory['main_category_name']); ?></td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $subcategory['status'] == 'active' ? 'success' : 'danger'; ?>">
                                                            <?php echo ucfirst($subcategory['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-warning btn-sm" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#editSubCategoryModal<?php echo $subcategory['id']; ?>">
                                                            <i class="bi bi-pencil text-light"></i> Edit
                                                        </button>
                                                        <a href="../includes/category_actions.php?action=delete_sub&id=<?php echo $subcategory['id']; ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Are you sure you want to delete this subcategory?');">
                                                            <i class="bi bi-trash text-light"></i> Delete
                                                        </a>
                                                    </td>
                                                </tr>

                                                <!-- Edit Subcategory Modal -->
                                                <div class="modal fade" id="editSubCategoryModal<?php echo $subcategory['id']; ?>" tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">Edit Subcategory</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <form action="../includes/category_actions.php" method="POST">
                                                                <div class="modal-body">
                                                                    <input type="hidden" name="action" value="update_sub">
                                                                    <input type="hidden" name="id" value="<?php echo $subcategory['id']; ?>">
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Main Category</label>
                                                                        <select class="form-select" name="category_id" required>
                                                                            <?php foreach ($main_categories as $category): ?>
                                                                                <option value="<?php echo $category['id']; ?>"
                                                                                        <?php echo $subcategory['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Subcategory Name</label>
                                                                        <input type="text" class="form-control" name="name" 
                                                                               value="<?php echo htmlspecialchars($subcategory['name']); ?>" required>
                                                                    </div>
                                                                    
                                                                    <div class="mb-3">
                                                                        <label class="form-label">Status</label>
                                                                        <select class="form-select" name="status" required>
                                                                            <option value="active" <?php echo $subcategory['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                                                            <option value="inactive" <?php echo $subcategory['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                    <button type="submit" class="btn btn-primary">Update Subcategory</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php echo generatePaginationLinks($sub_page, $sub_total_pages, 'sub_page'); ?>
                        </div>
                    </div>
                </div>
            </main>
            <!-- Start of Footer -->
            <?php require_once("../includes/footer.php"); ?>
            <!-- End of Footer -->
        </div>
    </div>

    <!-- Add Main Category Modal -->
    <div class="modal fade" id="addMainCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Main Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../includes/category_actions.php" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_main">
                        
                        <div class="mb-3">
                            <label class="form-label">Category Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Category Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            <small class="text-muted">Recommended size: 800x600 pixels. Max file size: 2MB</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Category</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Subcategory Modal -->
    <div class="modal fade" id="addSubCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Subcategory</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="../includes/category_actions.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_sub">
                        
                        <div class="mb-3">
                            <label class="form-label">Main Category</label>
                            <select class="form-select" name="category_id" required>
                                <option value="">Select Main Category</option>
                                <?php foreach ($main_categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>">
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subcategory Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Subcategory</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/scripts.js"></script>
</body>
</html>