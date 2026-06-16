<?php
session_start();
include("../config/db.php");

// 1. SECURITY: Ensure only admin can access
if(!isset($_SESSION['role']) || $_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    
    // ===== VALIDATION FUNCTION =====
    function validate_form($data) {
        $errors = [];
        
        // Check required fields
        $required_fields = [
            'student_no' => 'ID Number',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'dob' => 'Date of Birth',
            'age' => 'Age',
            'gender' => 'Gender',
            'civil_status' => 'Civil Status',
            'permanent_address' => 'Permanent Address',
            'city_address' => 'City Address',
            'housing_type' => 'Housing Type',
            'contact_number' => 'Contact Number',
            'emergency_person' => 'Emergency Contact Person',
            'emergency_number' => 'Emergency Contact Number',
            'father_name' => "Father's Name",
            'father_occupation' => "Father's Occupation",
            'mother_name' => "Mother's Name",
            'mother_occupation' => "Mother's Occupation",
            'previous_gpa' => 'Previous GPA'
        ];

        foreach($required_fields as $field => $label) {
            if(empty($data[$field])) {
                $errors[] = "$label is required.";
            }
        }

        // Validate age
        if(!empty($data['age']) && (!is_numeric($data['age']) || $data['age'] < 1 || $data['age'] > 150)) {
            $errors[] = "Age must be between 1 and 150.";
        }

        // Validate contact number (basic check)
        if(!empty($data['contact_number']) && !preg_match('/^[0-9\-\+\(\)\s]+$/', $data['contact_number'])) {
            $errors[] = "Contact number format is invalid.";
        }

        // Validate GPA format
        if(!empty($data['previous_gpa']) && !is_numeric($data['previous_gpa'])) {
            $errors[] = "GPA must be a number.";
        }

        return $errors;
    }

    // Get form data
    $student_no = trim($_POST['student_no'] ?? '');
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $dob = trim($_POST['dob'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $civil_status = trim($_POST['civil_status'] ?? '');
    $religion = trim($_POST['religion'] ?? '');
    $permanent_address = trim($_POST['permanent_address'] ?? '');
    $city_address = trim($_POST['city_address'] ?? '');
    $housing_type = trim($_POST['housing_type'] ?? '');
    $contact_number = trim($_POST['contact_number'] ?? '');
    $emergency_person = trim($_POST['emergency_person'] ?? '');
    $emergency_number = trim($_POST['emergency_number'] ?? '');
    $father_name = trim($_POST['father_name'] ?? '');
    $father_occupation = trim($_POST['father_occupation'] ?? '');
    $mother_name = trim($_POST['mother_name'] ?? '');
    $mother_occupation = trim($_POST['mother_occupation'] ?? '');
    $activities = trim($_POST['activities'] ?? '');
    $previous_gpa = trim($_POST['previous_gpa'] ?? '');
    $edit_mode = isset($_POST['edit_mode']) && $_POST['edit_mode'] === 'true';

    // 2. VALIDATE FORM
    $validation_errors = validate_form($_POST);
    if(!empty($validation_errors)) {
        $error_message = implode(' | ', $validation_errors);
        if($edit_mode) {
            header("Location: admin-edit-student.php?id=$student_no&error=" . urlencode($error_message));
        } else {
            header("Location: admin-students.php?error=" . urlencode($error_message));
        }
        exit();
    }

    // 3. CHECK: Does the student account exist in the 'users' table?
    $check_user = $conn->prepare("SELECT id, email FROM users WHERE student_no = ? AND role = 'student'");
    $check_user->bind_param("s", $student_no);
    $check_user->execute();
    $user_result = $check_user->get_result();

    if($user_result->num_rows == 0){
        // ACCOUNT DOES NOT EXIST
        $error = "Account not found! Student ID '$student_no' does not exist. The student must create an account first.";
        if($edit_mode) {
            header("Location: admin-edit-student.php?id=$student_no&error=" . urlencode($error));
        } else {
            header("Location: admin-students.php?error=" . urlencode($error));
        }
        exit();
    }

    $user_data = $user_result->fetch_assoc();

    // 4. BEGIN TRANSACTION
    $conn->begin_transaction();

    try {
        // 5. CHECK FOR EXISTING PROFILE: Should we Update or Insert?
        $check_profile = $conn->prepare("SELECT id FROM student_profile WHERE student_no = ?");
        $check_profile->bind_param("s", $student_no);
        $check_profile->execute();
        $profile_result = $check_profile->get_result();

        $is_update = $profile_result->num_rows > 0;
        $action = $is_update ? "updated" : "created";

        if($is_update){
            // UPDATE EXISTING PROFILE
            $update_stmt = $conn->prepare("UPDATE student_profile SET 
                first_name=?, middle_name=?, last_name=?, dob=?, age=?, gender=?, 
                civil_status=?, religion=?, permanent_address=?, city_address=?, 
                housing_type=?, contact_number=?, emergency_person=?, emergency_number=?, 
                father_name=?, father_occupation=?, mother_name=?, mother_occupation=?, 
                activities=?, previous_gpa=?, updated_at=NOW() WHERE student_no=?");
            
            $update_stmt->bind_param("ssssissssssssssssssss", 
                $first_name, $middle_name, $last_name, $dob, $age, $gender, 
                $civil_status, $religion, $permanent_address, $city_address, 
                $housing_type, $contact_number, $emergency_person, $emergency_number, 
                $father_name, $father_occupation, $mother_name, $mother_occupation, 
                $activities, $previous_gpa, $student_no);
            
            if(!$update_stmt->execute()){
                throw new Exception("Failed to update profile: " . $update_stmt->error);
            }
        } else {
            // INSERT NEW PROFILE
            $insert_stmt = $conn->prepare("INSERT INTO student_profile (
                student_no, first_name, middle_name, last_name, dob, age, gender, 
                civil_status, religion, permanent_address, city_address, 
                housing_type, contact_number, emergency_person, emergency_number, 
                father_name, father_occupation, mother_name, mother_occupation, 
                activities, previous_gpa, created_at) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, NOW())");
            
            $insert_stmt->bind_param("sssssisssssssssssssss", 
                $student_no, $first_name, $middle_name, $last_name, $dob, $age, $gender, 
                $civil_status, $religion, $permanent_address, $city_address, 
                $housing_type, $contact_number, $emergency_person, $emergency_number, 
                $father_name, $father_occupation, $mother_name, $mother_occupation, 
                $activities, $previous_gpa);
            
            if(!$insert_stmt->execute()){
                throw new Exception("Failed to insert profile: " . $insert_stmt->error);
            }
        }

        // 6. COMMIT TRANSACTION
        $conn->commit();

        // 7. REDIRECT WITH SUCCESS MESSAGE
        $success_msg = "Student information has been $action successfully!";
        if($edit_mode) {
            header("Location: admin-view-student.php?id=$student_no&success=" . urlencode($success_msg));
        } else {
            header("Location: manage-students.php?success=" . urlencode($success_msg));
        }
        exit();

    } catch(Exception $e) {
        // ROLLBACK ON ERROR
        $conn->rollback();
        $error_msg = "Error saving student: " . $e->getMessage();
        if($edit_mode) {
            header("Location: admin-edit-student.php?id=$student_no&error=" . urlencode($error_msg));
        } else {
            header("Location: admin-students.php?error=" . urlencode($error_msg));
        }
        exit();
    }
}
?>
