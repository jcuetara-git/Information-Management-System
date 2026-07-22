<?php
session_start();
include("../config/db.php");

// Ensure the student is logged in
if(!isset($_SESSION['student_no'])){
    header("Location: ../auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $student_no = $_SESSION['student_no'];
    $file = $_FILES['photo'];

    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));

    $allowed = array('jpg', 'jpeg', 'png');

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 5000000) { 
                $fileNameNew = "profile_" . $student_no . "_" . uniqid('', true) . "." . $fileActualExt;
                $fileDestination = '../uploads/' . $fileNameNew;

                if (!is_dir('../uploads/')) {
                    mkdir('../uploads/', 0755, true); // Changed to 0755 for security
                }

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    $stmt = $conn->prepare("UPDATE student_profile SET profile_pic = ? WHERE student_no = ?");
                    
                    if ($stmt) {
                        $stmt->bind_param("ss", $fileNameNew, $student_no);
                        if ($stmt->execute()) {
                            header("Location: student-dashboard.php?upload=success&modal=open");
                        } else {
                            header("Location: student-dashboard.php?upload=db_error&msg=" . urlencode($stmt->error) . "&modal=open");
                        }
                        $stmt->close();
                    } else {
                        header("Location: student-dashboard.php?upload=db_error&msg=" . urlencode($conn->error) . "&modal=open");
                    }
                } else {
                    header("Location: student-dashboard.php?upload=move_error&modal=open");
                }
            } else {
                header("Location: student-dashboard.php?upload=too_big&modal=open");
            }
        } else {
            header("Location: student-dashboard.php?upload=error&modal=open");
        }
    } else {
        header("Location: student-dashboard.php?upload=invalid_type&modal=open");
    }
} else {
    header("Location: student-dashboard.php");
}
?>