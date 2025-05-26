<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('../includes/connect.php');

// Function to update user profile
function updateUserProfile($userId, $fullName, $email, $image = null) {
    global $conn;
    
    if ($image) {
        $query = "UPDATE users SET full_name = :full_name, email = :email, image = :image WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':image', $image, PDO::PARAM_LOB);
    } else {
        $query = "UPDATE users SET full_name = :full_name, email = :email WHERE id = :user_id";
        $stmt = $conn->prepare($query);
    }
    
    $stmt->bindParam(':full_name', $fullName);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    
    return $stmt->execute();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit();
    }

    $userId = $_SESSION['user_id'];

    try {
        // Update profile
        if (isset($_POST['fullname']) && isset($_POST['email'])) {
            $fullName = trim($_POST['fullname']);
            $email = trim($_POST['email']);
            
            // Validate email
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                header("Location: profile.php?error=Invalid email format");
                exit();
            }

            $image = null;
            
            // Handle image upload if present
            if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
                // Validate image
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $maxFileSize = 2 * 1024 * 1024; // 2MB
                
                if (!in_array($_FILES['user_image']['type'], $allowedTypes)) {
                    header("Location: profile.php?error=Invalid image type. Only JPG, PNG, and GIF are allowed");
                    exit();
                }
                
                if ($_FILES['user_image']['size'] > $maxFileSize) {
                    header("Location: profile.php?error=Image size too large. Max 2MB allowed");
                    exit();
                }
                
                $image = file_get_contents($_FILES['user_image']['tmp_name']);
            }

            if (updateUserProfile($userId, $fullName, $email, $image)) {
                header("Location: profile.php?success=Profile updated successfully");
            } else {
                header("Location: profile.php?error=Failed to update profile");
            }
            exit();
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        header("Location: profile.php?error=Database error occurred");
        exit();
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
        header("Location: profile.php?error=An error occurred");
        exit();
    }
}