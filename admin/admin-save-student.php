<?php
session_start();
include("../config/db.php");

// 1. SECURITY: Ensure only admin can access
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // Get form data
    $student_no = trim($_POST['student_no']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $dob = $_POST['dob'];
    $age = $_POST['age'];
    $gender = $_POST['gender'] ?? '';
    $civil_status = $_POST['civil_status'];
    $religion = $_POST['religion'];
    $permanent_address = $_POST['permanent_address'];
    $city_address = $_POST['city_address'];
    $housing_type = $_POST['housing_type'] ?? '';
    $contact_number = $_POST['contact_number'];
    $emergency_person = $_POST['emergency_person'];
    $emergency_number = $_POST['emergency_number'];
    $father_name = $_POST['father_name'];
    $father_occupation = $_POST['father_occupation'];
    $mother_name = $_POST['mother_name'];
    $mother_occupation = $_POST['mother_occupation'];
    $activities = $_POST['activities'];
    $previous_gpa = $_POST['previous_gpa'];

    // 2. STRICT CHECK: Does the student account exist in the 'users' table?
    $check_user = $conn->prepare("SELECT id FROM users WHERE student_no = ?");
    $check_user->bind_param("s", $student_no);
    $check_user->execute();
    $user_result = $check_user->get_result();

    if($user_result->num_rows == 0){
        // ACCOUNT DOES NOT EXIST - STOP HERE
        header("Location: admin-dashboard.php?error=Account not found! The student must create an account first.");
        exit();
    }

    // 3. CHECK FOR EXISTING PROFILE: Should we Update or Insert?
    $check_profile = $conn->prepare("SELECT id FROM student_profile WHERE student_no = ?");
    $check_profile->bind_param("s", $student_no);
    $check_profile->execute();
    $profile_result = $check_profile->get_result();

    if($profile_result->num_rows > 0){
        // UPDATE EXISTING
        $stmt = $conn->prepare("UPDATE student_profile SET 
            first_name=?, middle_name=?, last_name=?, dob=?, age=?, gender=?, 
            civil_status=?, religion=?, permanent_address=?, city_address=?, 
            housing_type=?, contact_number=?, emergency_person=?, emergency_number=?, 
            father_name=?, father_occupation=?, mother_name=?, mother_occupation=?, 
            activities=?, previous_gpa=? WHERE student_no=?");
        
        $stmt->bind_param("ssssissssssssssssssss", 
            $first_name, $middle_name, $last_name, $dob, $age, $gender, 
            $civil_status, $religion, $permanent_address, $city_address, 
            $housing_type, $contact_number, $emergency_person, $emergency_number, 
            $father_name, $father_occupation, $mother_name, $mother_occupation, 
            $activities, $previous_gpa, $student_no);
    } else {
        // INSERT NEW
        $stmt = $conn->prepare("INSERT INTO student_profile (
            student_no, first_name, middle_name, last_name, dob, age, gender, 
            civil_status, religion, permanent_address, city_address, 
            housing_type, contact_number, emergency_person, emergency_number, 
            father_name, father_occupation, mother_name, mother_occupation, 
            activities, previous_gpa) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        
        $stmt->bind_param("sssssisssssssssssssss", 
            $student_no, $first_name, $middle_name, $last_name, $dob, $age, $gender, 
            $civil_status, $religion, $permanent_address, $city_address, 
            $housing_type, $contact_number, $emergency_person, $emergency_number, 
            $father_name, $father_occupation, $mother_name, $mother_occupation, 
            $activities, $previous_gpa);
    }

    // 4. EXECUTE AND REDIRECT
    if($stmt->execute()){
        header("Location: admin-dashboard.php?success=Student information saved successfully!");
    } else {
        header("Location: admin-dashboard.php?error=Database error: " . $conn->error);
    }
    exit();
}
?>
