<?php
session_start();
include('../includes/connect.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Build the query with filters
$where_conditions = [];
$params = [];

if (isset($_GET['role']) && !empty($_GET['role'])) {
    $where_conditions[] = "role = :role";
    $params[':role'] = $_GET['role'];
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $where_conditions[] = "(full_name LIKE :search OR email LIKE :search)";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Fetch users with filters
$query = "SELECT * FROM users $where_clause ORDER BY id DESC";
$stmt = $conn->prepare($query);
foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>User Management</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="../assets/favicon.ico" />
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
        .user-card {
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            background: #fff;
            margin-bottom: 24px;
        }
        .user-card .card-body {
            padding: 1.5rem;
        }
        .user-actions .btn {
            margin-right: 8px;
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            object-fit: cover;
            border-radius: 50%;
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
                        <li class="breadcrumb-item active">User Management</li>
                    </ol>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                    <?php endif; ?>
                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
                    <?php endif; ?>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title mb-0">User Management</h2>
                        <a href="add_user.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle text-light"></i> Add User
                        </a>
                    </div>

                    <!-- Filters Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="bi bi-funnel"></i> Filter Users</h5>
                        </div>
                        <div class="card-body">
                            <form method="GET" class="row g-3">
                                <div class="col-md-6">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input type="text" class="form-control" name="search" 
                                               placeholder="Search by name or email..." 
                                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light">
                                            <i class="bi bi-person-badge"></i>
                                        </span>
                                        <select class="form-select" name="role">
                                            <option value="">All Roles</option>
                                            <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
                                            <option value="user" <?php echo (isset($_GET['role']) && $_GET['role'] == 'user') ? 'selected' : ''; ?>>User</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-funnel-fill text-light"></i> Apply Filters
                                        </button>
                                        <a href="user_management.php" class="btn btn-outline-secondary">
                                            <i class="bi bi-x-circle"></i> Clear Filters
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Users Table -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Avatar</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr class="<?php echo ($user['id'] == $_SESSION['user_id']) ? 'table-primary' : ''; ?>">
                                                <td>
                                                    <?php if (!empty($user['image'])): ?>
                                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($user['image']); ?>"
                                                             alt="User Avatar" class="user-avatar">
                                                    <?php else: ?>
                                                        <img src="../assets/no-profile.png" alt="Default Avatar" class="user-avatar">
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php echo htmlspecialchars($user['full_name']); ?>
                                                    <?php if ($user['id'] == $_SESSION['user_id']): ?>
                                                        <span class="badge bg-info ms-2">You</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                                        <?php echo ucfirst($user['role']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="edit_user.php?id=<?php echo md5($user['id']); ?>" 
                                                           class="btn btn-warning btn-sm">
                                                            <i class="bi bi-pencil text-light"></i> Edit
                                                        </a>
                                                    <?php endif; ?>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="../includes/user_actions.php?action=delete&id=<?php echo md5($user['id']); ?>" 
                                                           class="btn btn-danger btn-sm"
                                                           onclick="return confirm('Are you sure you want to delete this user?')">
                                                            <i class="bi bi-trash text-light"></i> Delete
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
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
</body>
</html> 