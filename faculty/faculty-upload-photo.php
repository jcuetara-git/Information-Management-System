<?php
session_start();
include("../config/db.php");

// Set header to return JSON for our background AJAX request
header('Content-Type: application/json');

// Check all possible session keys used for faculty authentication
$faculty_no = $_SESSION['faculty_no'] ?? $_SESSION['student_no'] ?? $_SESSION['faculty_id'] ?? '';

if (empty($faculty_no)) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized: Session expired or missing faculty ID.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];

    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];
    $fileExt = explode('.', $fileName);
    $fileActualExt = strtolower(end($fileExt));

    $allowed = array('jpg', 'jpeg', 'png', 'gif');

    if (in_array($fileActualExt, $allowed)) {
        if ($fileError === 0) {
            if ($fileSize < 1073741824) { // Allows up to 1GB
                $fileNameNew = "faculty_" . $faculty_no . "_" . uniqid('', true) . "." . $fileActualExt;
                $uploadDir = '../uploads/';

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true); 
                }

                $fileDestination = $uploadDir . $fileNameNew;

                if (move_uploaded_file($fileTmpName, $fileDestination)) {
                    // Try updating using faculty_no first
                    $stmt = $conn->prepare("UPDATE faculty_profile SET profile_pic = ? WHERE faculty_no = ?");
                    
                    if ($stmt) {
                        $stmt->bind_param("ss", $fileNameNew, $faculty_no);
                        $stmt->execute();
                        
                        if ($stmt->affected_rows === 0) {
                            // Fallback: try updating using faculty_id column if faculty_no doesn't match
                            $stmt->close();
                            $stmt = $conn->prepare("UPDATE faculty_profile SET profile_pic = ? WHERE faculty_id = ?");
                            if ($stmt) {
                                $stmt->bind_param("ss", $fileNameNew, $faculty_no);
                                $stmt->execute();
                            }
                        }
                        
                        if ($stmt) {
                            $stmt->close();
                        }

                        // Success! Send back the path for the frontend
                        echo json_encode(['status' => 'success', 'new_image' => $fileDestination]);
                        exit();
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'DB Error: ' . $conn->error]);
                        exit();
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file.']);
                    exit();
                }
            } else {
                echo json_encode(['status' => 'error', 'message' => 'File too large.']);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Upload error code: ' . $fileError]);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, JPEG, PNG, GIF allowed.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No file received.']);
    exit();
}
?>