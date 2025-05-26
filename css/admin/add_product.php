<?php 
session_start();
include('../includes/connect.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
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
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
    <title>Add Product</title>
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
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            object-fit: contain;
            margin-top: 10px;
            display: none;
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
                        <li class="breadcrumb-item"><a href="product_management.php">Product Management</a></li>
                        <li class="breadcrumb-item active">Add Product</li>
                    </ol>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>

                    <div class="card mb-4">
                        <div class="card-header">
                            <h2 class="section-title mb-0">Add New Product</h2>
                        </div>
                        <div class="card-body">
                            <form action="../includes/product_actions.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="action" value="add">
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Product Name</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" class="form-control" name="price" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Category</label>
                                        <select class="form-select" name="category_id" id="categorySelect" required>
                                            <option value="">Select Category</option>
                                            <?php foreach($categories as $category): ?>
                                                <option value="<?php echo $category['id']; ?>">
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
                                                        data-category="<?php echo $subcategory['category_id']; ?>">
                                                    <?php echo htmlspecialchars($subcategory['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="quantity" min="0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Status</label>
                                        <select class="form-select" name="status" required>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" required></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Product Image</label>
                                    <input type="file" class="form-control" name="image" accept="image/*" onchange="previewImage(this)">
                                    <img id="imagePreview" class="preview-image mt-2" src="#" alt="Preview">
                                </div>

                                <div class="d-flex justify-content-end gap-2">
                                    <a href="product_management.php" class="btn btn-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-primary">Add Product</button>
                                </div>
                            </form>
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