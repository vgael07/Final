<?php
session_start();
include('connect.php');

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Function to find original ID from MD5 hash
function findOriginalId($conn, $md5_hash) {
    $stmt = $conn->prepare("SELECT id FROM users WHERE MD5(id) = ?");
    $stmt->execute([$md5_hash]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['id'] : null;
}

// Handle POST requests (add/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add') {
        // Validate required fields
        if (empty($_POST['full_name']) || empty($_POST['email']) || empty($_POST['password'])) {
            header("Location: ../admin/add_user.php?error=All fields are required");
            exit();
        }
        
        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            header("Location: ../admin/add_user.php?error=Invalid email format");
            exit();
        }
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->fetch()) {
            header("Location: ../admin/add_user.php?error=Email already exists");
            exit();
        }
        
        // Handle image upload
        $image_data = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                header("Location: ../admin/add_user.php?error=Invalid file type. Allowed types: " . implode(', ', $allowed_extensions));
                exit();
            }
            
            // Read the image file into binary data
            $image_data = file_get_contents($_FILES['avatar']['tmp_name']);
        }
        
        try {
            // Debug information
            error_log("Attempting to add user with data: " . print_r([
                'full_name' => $_POST['full_name'],
                'email' => $_POST['email'],
                'role' => $_POST['role'],
                'has_image' => !is_null($image_data)
            ], true));

            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, image) VALUES (?, ?, ?, ?, ?)");
            $result = $stmt->execute([
                $_POST['full_name'],
                $_POST['email'],
                password_hash($_POST['password'], PASSWORD_DEFAULT),
                $_POST['role'],
                $image_data
            ]);

            if (!$result) {
                error_log("Database error: " . print_r($stmt->errorInfo(), true));
                throw new PDOException("Failed to insert user");
            }
            
            header("Location: ../admin/user_management.php?success=User added successfully");
            exit();
        } catch(PDOException $e) {
            error_log("Error adding user: " . $e->getMessage());
            header("Location: ../admin/add_user.php?error=Failed to add user: " . $e->getMessage());
            exit();
        }
    }
    
    else if ($action === 'update') {
        // Debug log the incoming data
        error_log("Update user request data: " . print_r($_POST, true));
        error_log("Update user files data: " . print_r($_FILES, true));

        // Validate required fields
        if (empty($_POST['full_name']) || empty($_POST['email'])) {
            header("Location: ../admin/edit_user.php?id=" . $_POST['id'] . "&error=Name and email are required");
            exit();
        }
        
        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            header("Location: ../admin/edit_user.php?id=" . $_POST['id'] . "&error=Invalid email format");
            exit();
        }
        
        // Get original ID from MD5 hash
        $original_id = findOriginalId($conn, $_POST['id']);
        if (!$original_id) {
            error_log("User not found for ID hash: " . $_POST['id']);
            header("Location: ../admin/user_management.php?error=User not found");
            exit();
        }

        // Verify user exists before proceeding
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $check_stmt->execute([$original_id]);
        if (!$check_stmt->fetch()) {
            error_log("User not found in database for ID: " . $original_id);
            header("Location: ../admin/user_management.php?error=User not found");
            exit();
        }
        
        // Check if email already exists for other users
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$_POST['email'], $original_id]);
        if ($stmt->fetch()) {
            header("Location: ../admin/edit_user.php?id=" . $_POST['id'] . "&error=Email already exists");
            exit();
        }
        
        // Handle image upload
        $image_data = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_extension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                header("Location: ../admin/edit_user.php?id=" . $_POST['id'] . "&error=Invalid file type. Allowed types: " . implode(', ', $allowed_extensions));
                exit();
            }
            
            // Read the image file into binary data
            $image_data = file_get_contents($_FILES['image']['tmp_name']);
            if ($image_data === false) {
                error_log("Failed to read image file: " . $_FILES['image']['tmp_name']);
                header("Location: ../admin/edit_user.php?id=" . $_POST['id'] . "&error=Failed to process image");
                exit();
            }
        }
        
        try {
            // Start building the update query
            $update_fields = ["full_name = ?", "email = ?", "role = ?"];
            $params = [$_POST['full_name'], $_POST['email'], $_POST['role']];
            
            // Add password update if provided
            if (!empty($_POST['password'])) {
                if (strlen($_POST['password']) < 6) {
                    header("Location: ../admin/edit_user.php?id=" . $_POST['id'] . "&error=Password must be at least 6 characters long");
                    exit();
                }
                $update_fields[] = "password = ?";
                $params[] = password_hash($_POST['password'], PASSWORD_DEFAULT);
            }
            
            // Add image update if new one uploaded
            if ($image_data) {
                $update_fields[] = "image = ?";
                $params[] = $image_data;
            }
            
            // Add the ID to params
            $params[] = $original_id;
            
            // Debug log the update query and parameters
            error_log("Update query fields: " . implode(", ", $update_fields));
            error_log("Update parameters: " . print_r($params, true));
            
            // Execute the update
            $stmt = $conn->prepare("UPDATE users SET " . implode(", ", $update_fields) . " WHERE id = ?");
            $result = $stmt->execute($params);

            if (!$result) {
                error_log("Database error during update: " . print_r($stmt->errorInfo(), true));
                throw new PDOException("Failed to update user");
            }
            
            header("Location: ../admin/user_management.php?success=User updated successfully");
            exit();
        } catch(PDOException $e) {
            error_log("Error updating user: " . $e->getMessage());
            header("Location: ../admin/edit_user.php?id=" . $_POST['id'] . "&error=Failed to update user: " . $e->getMessage());
            exit();
        }
    }
}

// Handle GET requests (delete)
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'delete') {
        // Get original ID from MD5 hash
        $original_id = findOriginalId($conn, $_GET['id']);
        if (!$original_id) {
            header("Location: ../admin/user_management.php?error=User not found");
            exit();
        }
        
        // Check if this is the last admin
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
        $stmt->execute();
        $admin_count = $stmt->fetchColumn();
        
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$original_id]);
        $user_role = $stmt->fetchColumn();
        
        if ($user_role === 'admin' && $admin_count <= 1) {
            header("Location: ../admin/user_management.php?error=Cannot delete the last admin user");
            exit();
        }
        
        try {
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$original_id]);
            
            header("Location: ../admin/user_management.php?success=User deleted successfully");
            exit();
        } catch(PDOException $e) {
            error_log("Error deleting user: " . $e->getMessage());
            header("Location: ../admin/user_management.php?error=Failed to delete user");
            exit();
        }
    }
}

// If no valid action is provided
header("Location: ../admin/user_management.php");
exit();
?> 