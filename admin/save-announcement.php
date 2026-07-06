<?php
// 1. Include your database configuration
include("../config/db.php");
include("../config/auth.php");

// 2. Ensure only admins can post
if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// 3. Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 4. Sanitize and collect inputs
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $audience = $_POST['audience']; // 'all', 'students', 'faculty', 'alumni', or 'specific_user'
    $status = $_POST['status'];
    
    // Handle the specific user ID if applicable
    $target_user_id = ($audience === 'specific_user') ? trim($_POST['target_user_id']) : NULL;

    // 5. Prepare and execute the SQL statement
    // Note: Ensure your table columns match exactly (title, message, target_audience, status, target_user_id)
    $stmt = $conn->prepare("INSERT INTO announcements (title, message, target_audience, status, target_user_id) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("sssss", $title, $message, $audience, $status, $target_user_id);
        
        if ($stmt->execute()) {
            // Success: Redirect back to the announcement page with a success message
            header("Location: admin-announcement.php?success=Announcement posted successfully!");
            exit();
        } else {
            // Error: Redirect with an error message
            header("Location: admin-announcement.php?error=Failed to post announcement.");
            exit();
        }
        $stmt->close();
    } else {
        // Database preparation error
        header("Location: admin-announcement.php?error=Database error.");
        exit();
    }
} else {
    // If someone tries to access this file directly without POST
    header("Location: admin-announcement.php");
    exit();
}
?>