<?php
session_start();

// Include correct db configuration matching alumni-dashboard
require_once('../config/db.php'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Retrieve inputs with explicit fallbacks
    $student_no              = trim($_POST['student_no'] ?? $_SESSION['student_no'] ?? ''); 
    $first_name              = trim($_POST['first_name'] ?? $_SESSION['first_name'] ?? '');
    $middle_name             = trim($_POST['middle_name'] ?? '');
    $last_name               = trim($_POST['last_name'] ?? $_SESSION['last_name'] ?? '');
    $dob                     = $_POST['dob'] ?? null;
    $age                     = intval($_POST['age'] ?? 0);
    $contact_number          = trim($_POST['contact_number'] ?? '');
    $email_address           = trim($_POST['email_address'] ?? '');
    $year_graduated          = intval($_POST['year_graduated'] ?? 0);
    $date_of_licensure_exam  = !empty($_POST['date_of_licensure_exam']) ? $_POST['date_of_licensure_exam'] : null;
    $prc_board_rating   = !empty($_POST['prc_board_rating']) ? floatval($_POST['prc_board_rating']) : null;
    $current_job             = trim($_POST['current_job'] ?? '');

    // Form Server-side Validation
    if (empty($student_no) || empty($first_name) || empty($last_name) || empty($dob) || empty($contact_number) || empty($email_address) || empty($year_graduated) || empty($current_job)) {
        $_SESSION['error_message'] = "Please fill up all required fields completely.";
        header("Location: alumni-add-portfolio.php");
        exit();
    }

    $query = "INSERT INTO alumni_profile (
                student_no,
                first_name, 
                middle_name, 
                last_name, 
                dob, 
                age, 
                contact_number, 
                email_address, 
                year_graduated, 
                date_of_licensure_exam, 
                prc_board_rating, 
                current_job
              ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($query)) {
        
        // FIXED: The string contains exactly 12 type definition identifiers matching the 12 columns:
        // s = student_no, s = first_name, s = middle_name, s = last_name, s = dob, i = age, 
        // s = contact_number, s = email_address, i = year_graduated, s = date_of_licensure_exam, d = prc_board_rating, s = current_job
        $stmt->bind_param(
            "sssssissisds", 
            $student_no,
            $first_name, 
            $middle_name, 
            $last_name, 
            $dob, 
            $age, 
            $contact_number, 
            $email_address, 
            $year_graduated, 
            $date_of_licensure_exam, 
            $prc_board_rating, 
            $current_job
        );

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Alumni portfolio information saved successfully!";
            header("Location: alumni-dashboard.php?success=1");
            exit();
        } else {
            $_SESSION['error_message'] = "Database insertion failed: " . $stmt->error;
            header("Location: alumni-add-portfolio.php");
            exit();
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "SQL Prepare failed: " . $conn->error;
        header("Location: alumni-add-portfolio.php");
        exit();
    }
} else {
    header("Location: alumni-add-portfolio.php");
    exit();
}
?>