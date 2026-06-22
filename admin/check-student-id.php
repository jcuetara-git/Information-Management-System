<?php
include("../config/db.php");

// Set header to return JSON data
header('Content-Type: application/json');

if(isset($_GET['id'])) {
    $student_no = trim($_GET['id']);

    // 1. Updated: Check if registered AND fetch first_name / last_name
    $user_query = "SELECT student_no, first_name, last_name FROM users WHERE student_no = ? AND role = 'student'";
    $stmt1 = $conn->prepare($user_query);
    $stmt1->bind_param("s", $student_no);
    $stmt1->execute();
    $user_result = $stmt1->get_result();

    if($user_result->num_rows > 0) {
        $user_data = $user_result->fetch_assoc();
        
        // 2. User is registered! Now check if they ALREADY have a profile
        $profile_query = "SELECT student_no FROM student_profile WHERE student_no = ?";
        $stmt2 = $conn->prepare($profile_query);
        $stmt2->bind_param("s", $student_no);
        $stmt2->execute();
        $profile_result = $stmt2->get_result();

        if($profile_result->num_rows > 0) {
            echo json_encode(['registered' => true, 'has_profile' => true]);
        } else {
            // Updated: Added names to the successful payload response
            echo json_encode([
                'registered' => true, 
                'has_profile' => false,
                'first_name' => $user_data['first_name'],
                'last_name' => $user_data['last_name']
            ]);
        }
        $stmt2->close();

    } else {
        echo json_encode(['registered' => false]);
    }
    
    $stmt1->close();
    $conn->close();
} else {
    echo json_encode(['error' => 'No ID provided']);
}
?>