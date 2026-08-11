<?php
// Turn off standard HTML error display so it doesn't break JSON output
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
header('Content-Type: application/json');

// Safely check and include db configuration
$db_path = '../config/db.php';
if (!file_exists($db_path)) {
    echo json_encode(['status' => 'error', 'message' => 'Database configuration file not found at path: ' . $db_path]);
    exit();
}
require_once($db_path);

// Ensure only logged-in alumni can upload
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'alumni') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access. Please log in as an alumni.']);
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';

if (empty($student_no)) {
    echo json_encode(['status' => 'error', 'message' => 'Session student number missing.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'File upload error code: ' . $file['error']]);
        exit();
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
    
    // Fallback mime type detection
    $file_tmp = $file['tmp_name'];
    $file_mime = function_exists('mime_content_type') ? mime_content_type($file_tmp) : '';
    
    if (empty($file_mime)) {
        $check = getimagesize($file_tmp);
        $file_mime = $check ? $check['mime'] : '';
    }

    if (!in_array($file_mime, $allowed_types)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Please upload a JPG, PNG, or WEBP image.']);
        exit();
    }

    $max_size = 5 * 1024 * 1024; // 5MB
    if ($file['size'] > $max_size) {
        echo json_encode(['status' => 'error', 'message' => 'File is too large. Maximum allowed size is 5MB.']);
        exit();
    }

    // Ensure uploads folder exists in the parent root directory
    $upload_dir = '../uploads/';
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create uploads directory. Check server permissions.']);
            exit();
        }
    }

    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'alumni_' . $student_no . '_' . time() . '.' . $file_ext;
    $destination = $upload_dir . $new_filename;

    if (move_uploaded_file($file_tmp, $destination)) {
        
        // Remove old profile picture if exists
        $old_pic_query = "SELECT profile_pic FROM alumni_profile WHERE student_no = ?";
        if ($stmt_old = $conn->prepare($old_pic_query)) {
            $stmt_old->bind_param("s", $student_no);
            $stmt_old->execute();
            $res_old = $stmt_old->get_result();
            if ($row_old = $res_old->fetch_assoc()) {
                if (!empty($row_old['profile_pic']) && file_exists($upload_dir . $row_old['profile_pic'])) {
                    @unlink($upload_dir . $row_old['profile_pic']);
                }
            }
            $stmt_old->close();
        }

        // Update database record
        $update_query = "UPDATE alumni_profile SET profile_pic = ? WHERE student_no = ?";
        if ($stmt = $conn->prepare($update_query)) {
            $stmt->bind_param("ss", $new_filename, $student_no);
            
            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Profile picture updated successfully.',
                    'new_image' => '../uploads/' . $new_filename
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Database update failed: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Database prepare error: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to move uploaded file. Check destination permissions.']);
    }

    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request or no file selected.']);
}
?>