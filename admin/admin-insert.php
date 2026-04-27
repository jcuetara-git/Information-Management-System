<?php

include("../config/db.php"); 

$student_no = "022026";
$first_name = "ucmain";
$last_name  = "ccj";
$email      = "ucmain_ccj@uc.edu.ph";
$year_level = "admin";
$role       = "admin";
$password   = "crim020126"; 

$hash = password_hash($password, PASSWORD_DEFAULT);


$check = $conn->prepare("SELECT id FROM users WHERE student_no = ?");
$check->bind_param("s", $student_no);
$check->execute();
$check->store_result();

if($check->num_rows > 0){
    echo "Admin account already exists.";
} else {

    $stmt = $conn->prepare("INSERT INTO users (student_no, first_name, last_name, email, year_level, password, role) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param("sssssss",
        $student_no,
        $first_name,
        $last_name,
        $email,
        $year_level,
        $hash,
        $role
    );

    if($stmt->execute()){
        echo "Admin account created successfully!";
    } else {
        echo "Failed to create admin account: " . $stmt->error;
    }
}
?>