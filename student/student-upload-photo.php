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

    // File properties
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileType = $file['type'];

    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));

    $allowed = array('jpg', 'jpeg', 'png');

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 5000000) { // 5MB limit
                $fileNameNew = "profile_" . $student_no . "_" . uniqid('', true) . "." . $fileActualExt;
                $fileDestination = '../uploads/' . $fileNameNew;

                // Create directory if not exists
                if (!is_dir('../uploads/')) {
                    mkdir('../uploads/', 0777, true);
                }

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Update database
                    $stmt = $conn->prepare("UPDATE student_profile SET profile_pic = ? WHERE student_no = ?");
                    
                    if ($stmt) {
                        $stmt->bind_param("ss", $fileNameNew, $student_no);
                        if ($stmt->execute()) {
                            header("Location: student-view-record.php?upload=success");
                        } else {
                            header("Location: student-view-record.php?upload=db_error&msg=" . urlencode($stmt->error));
                        }
                        $stmt->close();
                    } else {
                        // This is where the error occurred: the column likely doesn't exist
                        header("Location: student-view-record.php?upload=db_error&msg=" . urlencode($conn->error));
                    }
                } else {
                    header("Location: student-view-record.php?upload=move_error");
                }
            } else {
                header("Location: student-view-record.php?upload=too_big");
            }
        } else {
            header("Location: student-view-record.php?upload=error");
        }
    } else {
        header("Location: student-view-record.php?upload=invalid_type");
    }
} else {
    header("Location: student-view-record.php");
}
?>
