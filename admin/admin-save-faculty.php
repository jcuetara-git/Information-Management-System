<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
    $faculty_no = trim($_POST['faculty_no']);
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $status = trim($_POST['status']);
    $contact_no = trim($_POST['contact_no']);

    if(empty($faculty_no) || empty($first_name) || empty($last_name) || empty($status) || empty($contact_no)){
        header("Location: manage-faculty.php?error=All required data fields must be filled out");
        exit();
    }

    // Start database transaction
    $conn->begin_transaction();

    try {
        // 1. Update users table (Removed middle_name column)
        $stmt1 = $conn->prepare("UPDATE users SET first_name = ?, last_name = ? WHERE student_no = ? AND role = 'faculty'");
        $stmt1->bind_param("sss", $first_name, $last_name, $faculty_no);
        $stmt1->execute();

        // 2. Update faculty_profile table (Matches your database schema columns exactly)
        $stmt2 = $conn->prepare("UPDATE faculty_profile SET contact_no = ?, status = ? WHERE faculty_no = ?");
        $stmt2->bind_param("sss", $contact_no, $status, $faculty_no);
        $stmt2->execute();

        $conn->commit();
        header("Location: manage-faculty.php?success=Faculty record updated successfully");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        header("Location: manage-faculty.php?error=System update execution fault: " . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: manage-faculty.php");
    exit();
}