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
    
    // 3. Retrieve and sanitize POST data
    $student_no        = trim($_POST['student_no']);
    $first_name        = trim($_POST['first_name']);
    $middle_name       = trim($_POST['middle_name'] ?? '');
    $last_name         = trim($_POST['last_name']);
    $dob               = $_POST['dob'];
    $age               = intval($_POST['age']);
    $gender            = $_POST['gender'];
    $civil_status      = trim($_POST['civil_status']);
    $religion          = trim($_POST['religion'] ?? '');
    $permanent_address = trim($_POST['permanent_address']);
    $city_address      = trim($_POST['city_address']);
    $housing_type      = $_POST['housing_type'];
    $contact_number    = trim($_POST['contact_number']);
    $emergency_person  = trim($_POST['emergency_person']);
    $emergency_number  = trim($_POST['emergency_number']);
    $father_name       = trim($_POST['father_name']);
    $father_occupation = trim($_POST['father_occupation']);
    $mother_name       = trim($_POST['mother_name']);
    $mother_occupation = trim($_POST['mother_occupation']);
    $activities        = trim($_POST['activities'] ?? '');
    $previous_gpa      = trim($_POST['previous_gpa']);

    // 4. Determine if it's an UPDATE or an INSERT operation
    if (isset($_POST['edit_mode'])) {
        // --- UPDATE EXISTING PROFILE ---
        $query = "UPDATE student_profile SET 
                    first_name=?, middle_name=?, last_name=?, dob=?, age=?, gender=?, 
                    civil_status=?, religion=?, permanent_address=?, city_address=?, housing_type=?, 
                    contact_number=?, emergency_person=?, emergency_number=?, father_name=?, 
                    father_occupation=?, mother_name=?, mother_occupation=?, activities=?, previous_gpa=? 
                  WHERE student_no=?";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("ssssissssssssssssssss", 
                $first_name, $middle_name, $last_name, $dob, $age, $gender, 
                $civil_status, $religion, $permanent_address, $city_address, $housing_type, 
                $contact_number, $emergency_person, $emergency_number, $father_name, 
                $father_occupation, $mother_name, $mother_occupation, $activities, $previous_gpa, 
                $student_no
            );
            
            if ($stmt->execute()) {
                header("Location: manage-students.php?success=" . urlencode("Student profile updated successfully."));
            } else {
                header("Location: manage-students.php?error=" . urlencode("Failed to update profile: " . $stmt->error));
            }
            $stmt->close();
        }
    } else {
        // --- INSERT NEW PROFILE ---
        $query = "INSERT INTO student_profile (
                    student_no, first_name, middle_name, last_name, dob, age, gender, 
                    civil_status, religion, permanent_address, city_address, housing_type, 
                    contact_number, emergency_person, emergency_number, father_name, 
                    father_occupation, mother_name, mother_occupation, activities, previous_gpa
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        if ($stmt = $conn->prepare($query)) {
            $stmt->bind_param("sssssisssssssssssssss", 
                $student_no, $first_name, $middle_name, $last_name, $dob, $age, $gender, 
                $civil_status, $religion, $permanent_address, $city_address, $housing_type, 
                $contact_number, $emergency_person, $emergency_number, $father_name, 
                $father_occupation, $mother_name, $mother_occupation, $activities, $previous_gpa
            );
            
            if ($stmt->execute()) {
                header("Location: manage-students.php?success=" . urlencode("Student profile saved successfully."));
            } else {
                header("Location: manage-students.php?error=" . urlencode("Failed to save profile: " . $stmt->error));
            }
            $stmt->close();
        }
    }
    
    $conn->close();
    exit();

} else {
    header("Location: manage-students.php");
    exit();
}
?>