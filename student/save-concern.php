<?php
session_start();
include("../config/db.php");

// Ensure logged in as student
if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_no = $_SESSION['student_no'] ?? '';
    $first_name = $_POST['first_name'] ?? '';
    $last_name  = $_POST['last_name'] ?? '';
    $year_level = $_POST['year_level'] ?? '';
    $status     = 'Pending';

    // Handle File Upload
    if (isset($_FILES['concern_file']) && $_FILES['concern_file']['error'] === UPLOAD_ERR_OK) {
        $file_name = $_FILES['concern_file']['name'];
        $file_tmp  = $_FILES['concern_file']['tmp_name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $new_file_name = time() . '_' . $student_no . '_concern.' . $file_ext;
        $upload_dir = '../uploads/concerns/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            // Updated to match your exact database columns: first_name, last_name, file_path
            $query = "INSERT INTO student_concerns 
                      (student_no, first_name, last_name, year_level, file_path, status) 
                      VALUES (?, ?, ?, ?, ?, ?)";
                      
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("ssssss", $student_no, $first_name, $last_name, $year_level, $new_file_name, $status);
                
                if ($stmt->execute()) {
                    header("Location: submit-concern.php?success=Concern submitted successfully!");
                } else {
                    $error_msg = urlencode("Execution failed: " . $stmt->error);
                    header("Location: submit-concern.php?error=" . $error_msg);
                }
                $stmt->close();
            } else {
                $error_msg = urlencode("Database preparation failed: " . $conn->error);
                header("Location: submit-concern.php?error=" . $error_msg);
            }
        } else {
            header("Location: submit-concern.php?error=Failed to upload file to the server.");
        }
    } else {
        header("Location: submit-concern.php?error=Please select a valid document or image file.");
    }
} else {
    header("Location: submit-concern.php");
    exit();
}
?>