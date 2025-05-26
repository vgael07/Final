<?php
session_start();
include('../includes/connect.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Get product ID from URL
$id = $_GET['id'] ?? '';
if (empty($id)) {
    header("Location: product_management.php?error=" . urlencode("Product ID is required"));
    exit();
}

// Fetch product details
try {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, s.name as subcategory_name 
                           FROM products p 
                           LEFT JOIN categories c ON p.category_id = c.id 
                           LEFT JOIN subcategories s ON p.subcategory_id = s.id 
                           WHERE p.id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header("Location: product_management.php?error=" . urlencode("Product not found"));
        exit();
    }
} catch (PDOException $e) {
    error_log("Error fetching product: " . $e->getMessage());
    header("Location: product_management.php?error=" . urlencode("Failed to fetch product details"));
    exit();
}

// Fetch categories
$cat_query = "SELECT * FROM categories ORDER BY name";
$cat_stmt = $conn->prepare($cat_query);
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch subcategories
$subcat_query = "SELECT s.*, c.name as main_category_name 
                 FROM subcategories s 
                 JOIN categories c ON s.category_id = c.id 
                 ORDER BY c.name, s.name";
$subcat_stmt = $conn->prepare($subcat_query);
$subcat_stmt->execute();
$subcategories = $subcat_stmt->fetchAll(PDO::FETCH_ASSOC);

// Convert subcategories to JSON for JavaScript
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
    <title>Edit Product</title>
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/style.min.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <link href="../css/styles.css" rel="stylesheet" />
    <style>
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            margin-top: 10px;
        }
        .subcategory-select {
            display: none;
        }
    </style>
</head>
<body class="sb-nav-fixed">
    <?php require_once("../includes/header.php"); ?>

    <div id="layoutSidenav">
        <?php require_once("../includes/menu.php"); ?>
         
        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4 mt-5">
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="product_management.php">Product Management</a></li>
                        <li class="breadcrumb-item active">Edit Product</li>
                    </ol>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo htmlspecialchars($_GET['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Edit Product</h5>
                        </div>
                        <div class="card-body">
                            <form action="../includes/product_actions.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Product Name</label>
                                        <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Category</label>
                                        <select class="form-select" name="category_id" id="categorySelect" required>
                                            <option value="">Select Category</option>
                                            <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($category['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Subcategory</label>
                                        <select class="form-select" name="subcategory_id" id="subcategorySelect">
                                            <option value="">Select Subcategory (Optional)</option>
                                            <?php foreach($subcategories as $subcategory): ?>
                                                <option value="<?php echo $subcategory['id']; ?>" 
                                                        data-category="<?php echo $subcategory['category_id']; ?>"
                                                        <?php echo $subcategory['id'] == $product['subcategory_id'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($subcategory['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="quantity" min="0" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status" required>
                                            <option value="active" <?php echo $product['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo $product['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Product Image</label>
                                    <?php if ($product['image']): ?>
                                        <div class="mb-2">
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($product['image']); ?>" 
                                                 class="preview-image" alt="Current product image">
                                        </div>
                                    <?php endif; ?>
                                    <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this)">
                                    <img id="imagePreview" class="preview-image mt-2" src="#" alt="Preview" style="display: none;">
                                    <small class="text-muted">Leave empty to keep the current image</small>
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="product_management.php" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Update Product</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
            <?php require_once("../includes/footer.php"); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../js/scripts.js"></script>
    <script>
        // Image preview
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        }

        // Dynamic subcategory filtering
        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('categorySelect');
            const subcategorySelect = document.getElementById('subcategorySelect');
            const subcategories = <?php echo $js_subcategories; ?>;
            
            function updateSubcategories() {
                const selectedCategory = categorySelect.value;
                const options = subcategorySelect.options;
                
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
                if (selectedCategory && subcategorySelect.value) {
                    const selectedOption = subcategorySelect.options[subcategorySelect.selectedIndex];
                    if (selectedOption.dataset.category !== selectedCategory) {
                        subcategorySelect.value = '';
                    }
                }
            }
            
            categorySelect.addEventListener('change', updateSubcategories);
            updateSubcategories(); // Initial update
        });
    </script>
</body>
</html>