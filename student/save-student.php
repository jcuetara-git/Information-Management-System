<?php
session_start();
include("../config/db.php");

// Ensure only students can access
if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: ../auth/login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // Get student_no from session for security
    $student_no = $_SESSION['student_no'];
    
    // Get form data
    $first_name = $_SESSION['first_name']; // Use session data for readonly fields
    $last_name = $_SESSION['last_name'];   // Use session data for readonly fields
    $middle_name = trim($_POST['middle_name']);
    $dob = $_POST['dob'];
    $age = $_POST['age'];
    $gender = $_POST['gender'];
    $civil_status = $_POST['civil_status'];
    $religion = $_POST['religion'];
    $permanent_address = $_POST['permanent_address'];
    $city_address = $_POST['city_address'];
    $housing_type = $_POST['housing_type'];
    $contact_number = $_POST['contact_number'];
    $emergency_person = $_POST['emergency_person'];
    $emergency_number = $_POST['emergency_number'];
    $father_name = $_POST['father_name'];
    $father_occupation = $_POST['father_occupation'];
    $mother_name = $_POST['mother_name'];
    $mother_occupation = $_POST['mother_occupation'];
    $activities = $_POST['activities'];
    $previous_gpa = $_POST['previous_gpa'];

    // Check if the student profile already exists
    $check_profile = $conn->prepare("SELECT id FROM student_profile WHERE student_no = ?");
    $check_profile->bind_param("s", $student_no);
    $check_profile->execute();
    $profile_result = $check_profile->get_result();

    if($profile_result->num_rows > 0){
        // Update existing profile
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
        // Insert new profile
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

    if($stmt->execute()){
        header("Location: student-dashboard.php?success=Student+Information+saved+successfully");
    } else {
        echo "Error: " . $conn->error;
    }
    exit();
}
?>