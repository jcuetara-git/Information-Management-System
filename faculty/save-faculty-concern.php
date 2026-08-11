<?php
session_start();
include("../config/db.php");

// Ensure logged in as faculty
if (!isset($_SESSION['role']) || $_SESSION['role'] != "faculty") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $faculty_id = $_SESSION['faculty_no'] ?? $_SESSION['faculty_id'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name  = $_POST['last_name'] ?? '';
    $status     = 'Pending';

    // Handle File Upload
    if (isset($_FILES['concern_file']) && $_FILES['concern_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['concern_file']['name'];
        $file_tmp  = $_FILES['concern_file']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $new_file_name = time() . '_' . $faculty_id . '_faculty_concern.' . $file_ext;
        $upload_dir = '../uploads/concerns/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            $query = "INSERT INTO faculty_concerns 
                      (faculty_no, first_name, last_name, file_path, status) 
                      VALUES (?, ?, ?, ?, ?)";
                      
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("sssss", $faculty_id, $first_name, $last_name, $new_file_name, $status);
                
                if ($stmt->execute()) {
                    header("Location: faculty-concern.php?success=Concern submitted successfully!");
                } else {
                    $error_msg = urlencode("Execution failed: " . $stmt->error);
                    header("Location: faculty-concern.php?error=" . $error_msg);
                }
                $stmt->close();
            } else {
                $error_msg = urlencode("Database preparation failed: " . $conn->error);
                header("Location: faculty-concern.php?error=" . $error_msg);
            }
        } else {
            header("Location: faculty-concern.php?error=Failed to upload file to the server.");
        }
    } else {
        header("Location: faculty-concern.php?error=Please select a valid document or image file.");
    }
} else {
    header("Location: faculty-concern.php");
    exit();
}
?>