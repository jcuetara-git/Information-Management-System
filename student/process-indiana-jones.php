<?php
session_start();
include("../config/db.php");

// Ensure logged in as student
if (!isset($_SESSION['role']) || $_SESSION['role'] != "student") {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Retrieve inputs securely
    $student_no         = $_SESSION['student_no']; 
    $firstname          = $_POST['first_name']; 
    $lastname           = $_POST['last_name'];  
    $middlename         = ""; 
    $year_level         = $_POST['year_level'];
    $number_of_absences = (int)$_POST['number_of_absences'];
    $date_recorded      = $_POST['date_recorded'];
    $status             = 'Pending';

    // Handle File Upload
    if (isset($_FILES['undertaking_file']) && $_FILES['undertaking_file']['error'] === UPLOAD_ERR_OK) {
        
        $file_name = $_FILES['undertaking_file']['name'];
        $file_tmp  = $_FILES['undertaking_file']['tmp_name'];
        
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if ($file_ext !== 'pdf') {
            header("Location: student-dashboard.php?error=Invalid file format. Please upload a PDF.");
            exit();
        }

        $new_file_name = time() . '_' . $student_no . '_Jones.' . $file_ext;
        $upload_dir = '../uploads/jones/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        if (move_uploaded_file($file_tmp, $upload_dir . $new_file_name)) {
            
            $query = "INSERT INTO indiana_jones_records
                      (student_no, firstname, lastname, middlename, year_level, number_of_absences, date_recorded, undertaking_file_path, status) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                      
            $stmt = $conn->prepare($query);
            
            if ($stmt) {
                $stmt->bind_param("sssssisss", $student_no, $firstname, $lastname, $middlename, $year_level, $number_of_absences, $date_recorded, $new_file_name, $status);
                
                if ($stmt->execute()) {
                    header("Location: student-dashboard.php?success=Indiana Jones Submission uploaded successfully! Awaiting Admin review.");
                } else {
                    header("Location: student-dashboard.php?error=Database error. Failed to save submission.");
                }
                $stmt->close();
            } else {
                die("Database preparation failed: " . $conn->error); 
            }
        } else {
            header("Location: student-dashboard.php?error=Failed to upload the file to the server.");
        }
    } else {
        header("Location: student-dashboard.php?error=Please select a valid PDF file to upload.");
    }
} else {
    header("Location: student-dashboard.php");
    exit();
}
?>