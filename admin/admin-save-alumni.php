<?php
session_start();
include("../config/db.php");
include("../config/auth.php");

// 1. Authenticate Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != "admin") {
    header("Location: ../auth/login.php");
    exit();
}

// 2. Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Retrieve and sanitize form data matching your admin-alumni input attributes
    $student_no              = trim($_POST['student_no']);
    $first_name              = trim($_POST['first_name']);
    $middle_name             = trim($_POST['middle_name'] ?? '');
    $last_name               = trim($_POST['last_name']);
    $dob                     = $_POST['dob'];
    $age                     = intval($_POST['age']);
    $contact_number          = trim($_POST['contact_number']);
    $email_address           = trim($_POST['email_address']);
    $year_graduated          = intval($_POST['year_graduated']);
    
    // Handle optional fields safely
    $date_of_licensure_exam  = !empty($_POST['date_of_licensure_exam']) ? $_POST['date_of_licensure_exam'] : null;
    $prc_board_rating        = !empty($_POST['prc_board_rating']) ? trim($_POST['prc_board_rating']) : null;
    $current_job             = !empty($_POST['current_job']) ? trim($_POST['current_job']) : null;

    // 4. Determine if it's an UPDATE or an INSERT operation
    if (isset($_POST['edit_mode'])) {
        // --- UPDATE EXISTING PROFILE ---
        $query = "UPDATE alumni_profile SET 
                    first_name=?, middle_name=?, last_name=?, dob=?, age=?, 
                    contact_number=?, email_address=?, year_graduated=?, 
                    date_of_licensure_exam=?, prc_board_rating=?, current_job=? 
                  WHERE student_no=?";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ssssississss", 
                $first_name, $middle_name, $last_name, $dob, $age, 
                $contact_number, $email_address, $year_graduated, 
                $date_of_licensure_exam, $prc_board_rating, $current_job, 
                $student_no
            );
            
            if ($stmt->execute()) {
                header("Location: manage-alumni.php?success=" . urlencode("Alumni profile updated successfully."));
            } else {
                header("Location: manage-alumni.php?error=" . urlencode("Failed to update profile: " . $stmt->error));
            }
            $stmt->close();
        }
    } else {
        // --- INSERT NEW PROFILE ---
        $query = "INSERT INTO alumni_profile (
                    student_no, first_name, middle_name, last_name, dob, age, 
                    contact_number, email_address, year_graduated, 
                    date_of_licensure_exam, prc_board_rating, current_job
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ssssississss", 
                $student_no, $first_name, $middle_name, $last_name, $dob, $age, 
                $contact_number, $email_address, $year_graduated, 
                $date_of_licensure_exam, $prc_board_rating, $current_job
            );
            
            if ($stmt->execute()) {
                header("Location: manage-alumni.php?success=" . urlencode("Alumni profile saved successfully."));
            } else {
                header("Location: manage-alumni.php?error=" . urlencode("Failed to save profile: " . $stmt->error));
            }
            $stmt->close();
        }
    }
    
    $conn->close();
    exit();

} else {
    header("Location: manage-alumni.php");
    exit();
}
?>