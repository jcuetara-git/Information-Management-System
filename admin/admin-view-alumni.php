<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// Get alumni ID from URL
$student_no = $_GET['id'] ?? null;

if(!$student_no){
    header("Location: manage-alumni.php?error=Invalid alumni ID");
    exit();
}

// Fetch alumni data mapping to your exact database schema fields
$query = "SELECT u.*, p.* FROM users u 
          LEFT JOIN alumni_profile p ON u.student_no = p.student_no 
          WHERE u.student_no = ? AND u.role = 'alumni'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_no);
$stmt->execute();
$result = $stmt->get_result();
$alumni = $result->fetch_assoc();

if(!$alumni){
    header("Location: manage-alumni.php?error=Alumni not found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="View Alumni Details - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>View Alumni</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS matching student record layout design -->
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-view-student.css">
</head>
<body>

<div class="view-container">
    <div class="view-card">
        <div class="view-header">
            <a href="manage-alumni.php" class="close-btn"><i class="fa-solid fa-times"></i></a>
        </div>
        
        <div class="view-body">
            <div class="view-section">
                <h3><i class="fa-solid fa-user"></i> Personal Information</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>ID Number:</label>
                        <p><?= htmlspecialchars($alumni['student_no']) ?></p>
                    </div>
                    <div class="view-item">
                        <label>First Name:</label>
                        <p><?= htmlspecialchars($alumni['first_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Middle Name:</label>
                        <p><?= htmlspecialchars($alumni['middle_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Last Name:</label>
                        <p><?= htmlspecialchars($alumni['last_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Date of Birth:</label>
                        <p><?= htmlspecialchars($alumni['dob'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Age:</label>
                        <p><?= htmlspecialchars($alumni['age'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <div class="view-section">
                <h3><i class="fa-solid fa-graduation-cap"></i> Graduation & Professional Information</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>Year Graduated:</label>
                        <p><?= htmlspecialchars($alumni['year_graduated'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Date of Licensure Exam:</label>
                        <p><?= htmlspecialchars($alumni['date_of_licensure_exam'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>PRC Board Rating (%):</label>
                        <p><?= !empty($alumni['prc_board_rating']) ? htmlspecialchars($alumni['prc_board_rating'] . '%') : 'N/A' ?></p>
                    </div>
                    <div class="view-item full-width">
                        <label>Current Job / Employment Details:</label>
                        <p><?= htmlspecialchars($alumni['current_job'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <div class="view-section">
                <h3><i class="fa-solid fa-phone"></i> Contact Information</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>Email Address:</label>
                        <p><?= htmlspecialchars($alumni['email_address'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Contact Number:</label>
                        <p><?= htmlspecialchars($alumni['contact_number'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
            
        </div>

        <div class="view-footer">
            <a href="manage-alumni.php" class="close-btn-full"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <a href="admin-edit-alumni.php?id=<?= urlencode($alumni['student_no']) ?>" class="edit-btn"><i class="fa-solid fa-edit"></i> Edit</a>
        </div>
    </div>
</div>

</body>
</html>