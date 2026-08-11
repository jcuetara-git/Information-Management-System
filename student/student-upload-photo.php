<?php
session_start();
include("../config/db.php");

// Set header to return JSON for our background AJAX request
header('Content-Type: application/json');

// Ensure the student is logged in
if(!isset($_SESSION['student_no'])){
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
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
            if ($fileSize < 5000000) { // Limit is currently 5MB
                $fileNameNew = "profile_" . $student_no . "_" . uniqid('', true) . "." . $fileActualExt;
                $fileDestination = '../uploads/' . $fileNameNew;

                if (!is_dir('../uploads/')) {
                    mkdir('../uploads/', 0755, true); 
                }

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    $stmt = $conn->prepare("UPDATE student_profile SET profile_pic = ? WHERE student_no = ?");
                    
                    if ($stmt) {
                        $stmt->bind_param("ss", $fileNameNew, $student_no);
                        if ($stmt->execute()) {
                            // Success! Send back the path to the new image
                            echo json_encode(['status' => 'success', 'new_image' => $fileDestination]);
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $stmt->error]);
                        }
                        $stmt->close();
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $conn->error]);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to move file.']);
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File too large.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Upload error.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file received.']);
}
?>