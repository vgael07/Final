<?php
session_start();
include('../includes/connect.php');

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

// Handle image upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'];
    
    // Handle image upload
    if (isset($_FILES['user_image']) && $_FILES['user_image']['error'] === UPLOAD_ERR_OK) {
        $image = file_get_contents($_FILES['user_image']['tmp_name']);
        
        // Update the image in the database
        $query = "UPDATE users SET image = :image WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':image', $image);
        $stmt->bindParam(':user_id', $userId);
        
        if ($stmt->execute()) {
            $selectStmt = $conn->prepare("SELECT image FROM users WHERE id = :user_id");
            $selectStmt->bindParam(':user_id', $userId);
            $selectStmt->execute();
            $updatedUser = $selectStmt->fetch(PDO::FETCH_ASSOC);
        
            if ($updatedUser && !empty($updatedUser['image'])) {
                $_SESSION['user_image'] = $updatedUser['image'];
            }
        
            header("Location: profile.php?success=Profile picture updated successfully");
        } else {
            header("Location: profile.php?error=Failed to update profile picture");
        }        
    } else {
        header("Location: profile.php?error=Image upload failed");
    }
}
?>