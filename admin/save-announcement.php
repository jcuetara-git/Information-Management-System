<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $message = trim($_POST['message']);
    $audience = $_POST['audience']; // 'all', 'students', 'faculty', 'alumni', or 'specific_user'
    $status = $_POST['status'];
    
    // Handle specific user ID
    $target_user_id = ($audience === 'specific_user') ? trim($_POST['target_user_id']) : NULL;

    $stmt = $conn->prepare("INSERT INTO announcements (title, message, target_audience, status, target_user_id) VALUES (?, ?, ?, ?, ?)");
    
    if ($stmt) {
        $stmt->bind_param("sssss", $title, $message, $audience, $status, $target_user_id);
        
        if ($stmt->execute()) {
            header("Location: admin-announcement.php?success=Announcement posted successfully!");
            exit();
        } else {
            header("Location: admin-announcement.php?error=Failed to post announcement.");
            exit();
        }
        $stmt->close();
    } else {
        header("Location: admin-announcement.php?error=Database error.");
        exit();
    }
} else {
    header("Location: admin-announcement.php");
    exit();
}
?>