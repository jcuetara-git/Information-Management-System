<?php
session_start();
include("../config/db.php");

// 1. Ensure student is logged in
if (!isset($_SESSION['student_no'])) {
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_no  = $_SESSION['student_no'];
    $firstname   = trim($_POST['firstname']);
    $lastname    = trim($_POST['lastname']);
    $status       = 'Pending';

    // 2. Validate & handle file upload
    if (isset($_FILES['undertaking_file']) && $_FILES['undertaking_file']['error'] === UPLOAD_ERR_OK) {
        $file        = $_FILES['undertaking_file'];
        $fileName    = $file['name'];
        $fileTmpName = $file['tmp_name'];
        $fileExt     = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        // Allowed file formats
        $allowed = array('pdf', 'jpg', 'jpeg', 'png');

        if (in_array($fileExt, $allowed)) {
            $fileNameNew     = "LOU_" . $student_no . "_" . uniqid() . "." . $fileExt;
            $uploadDir       = '../uploads/lou/';
            $fileDestination = $uploadDir . $fileNameNew;

            // Ensure upload directory exists
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (move_uploaded_file($fileTmpName, $fileDestination)) {
                // 3. Insert record into unified program_submissions table
                $stmt = $conn->prepare("INSERT INTO retention_records (student_no, first_name, last_name, file_path, status, submitted_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("ssssss", $student_no, $firstname, $lastname, $fileNameNew, $status);
                
                if ($stmt->execute()) {
                    header("Location: student-dashboard.php?success=LOU_submitted");
                } else {
                    header("Location: student-dashboard.php?error=Database_Error: " . urlencode($stmt->error));
                }
                $stmt->close();
            } else {
                header("Location: student-dashboard.php?error=File_Upload_Failed");
            }
        } else {
            header("Location: student-dashboard.php?error=Invalid_File_Type");
        }
    } else {
        header("Location: student-dashboard.php?error=No_File_Uploaded");
    }
} else {
    header("Location: student-dashboard.php");
}
?>