<?php
session_start();
include("../config/db.php");

// Ensure logged in as student
if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_no = $_SESSION['student_no'];
    
    // Sanitize input data
    $first_name = $conn->real_escape_string($_POST['first_name'] ?? '');
    $last_name = $conn->real_escape_string($_POST['last_name'] ?? '');
    $year_level = $conn->real_escape_string($_POST['year_level'] ?? '');

    // Validate that a file was uploaded without errors
    if (isset($_FILES['concern_file']) && $_FILES['concern_file']['error'] == 0) {
        
        // Define the upload directory
        $upload_dir = '../uploads/concerns/';
        
        // Create the directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_name = $_FILES['concern_file']['name'];
        $file_tmp = $_FILES['concern_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Define allowed file extensions for security
        $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];

        if (in_array($file_ext, $allowed_extensions)) {
            
            // Create a unique file name to prevent overwriting
            $new_file_name = 'concern_' . $student_no . '_' . time() . '.' . $file_ext;
            $dest_path = $upload_dir . $new_file_name;

            // Move the file to the uploads folder
            if (move_uploaded_file($file_tmp, $dest_path)) {
                
                // Insert the record into the database (Removed middle_name here)
                $query = "INSERT INTO student_concerns (student_no, first_name, last_name, year_level, file_path) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                
                if ($stmt) {
                    // Changed binding string from "ssssss" to "sssss"
                    $stmt->bind_param("sssss", $student_no, $first_name, $last_name, $year_level, $new_file_name);
                    
                    if ($stmt->execute()) {
                        header("Location: student-dashboard.php?success=Concern submitted successfully!");
                        exit();
                    } else {
                        $error = "Database error: Could not save your concern.";
                    }
                    $stmt->close();
                } else {
                    $error = "Database preparation error.";
                }
            } else {
                $error = "Error saving the uploaded file to the server.";
            }
        } else {
            $error = "Invalid file type. Please upload PDF, DOCX, JPG, or PNG files only.";
        }
    } else {
        $error = "Please upload a file detailing your concern.";
    }

    if (isset($error)) {
        header("Location: student-dashboard.php?error=" . urlencode($error));
        exit();
    }
} else {
    header("Location: student-dashboard.php");
    exit();
}
?>