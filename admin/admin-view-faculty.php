<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// Get faculty ID from URL
$faculty_no = $_GET['id'] ?? null;

if(!$faculty_no){
    header("Location: manage-faculty.php?error=Invalid faculty ID");
    exit();
}

// Fixed query: Removed u.middle_name since it doesn't exist in the users table
$query = "SELECT u.student_no AS faculty_no, u.first_name, u.last_name, u.email, 
                 p.contact_no, p.status
          FROM users u 
          LEFT JOIN faculty_profile p ON u.student_no = p.faculty_no 
          WHERE u.student_no = ? AND u.role = 'faculty'";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("s", $faculty_no);
$stmt->execute();
$result = $stmt->get_result();
$faculty = $result->fetch_assoc();

if(!$faculty){
    header("Location: manage-faculty.php?error=Faculty record not found");
    exit();
}
?>
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="View Faculty Details - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>View Faculty</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <!-- Reusing your clean viewing stylesheets -->
    <link rel="stylesheet" href="../assets/css/admin-view-student.css">
</head>
<body>

<div class="view-container">
    <div class="view-card">
        <div class="view-header">
            <a href="manage-faculty.php" class="close-btn"><i class="fa-solid fa-times"></i></a>
        </div>
        
        <div class="view-body">
            <div class="view-section">
                <h3><i class="fa-solid fa-user"></i> Faculty Information</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>ID Number:</label>
                        <p><?= htmlspecialchars($faculty['faculty_no']) ?></p>
                    </div>
                    <div class="view-item">
                        <label>First Name:</label>
                        <p><?= htmlspecialchars($faculty['first_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Last Name:</label>
                        <p><?= htmlspecialchars($faculty['last_name'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <div class="view-section">
                <h3><i class="fa-solid fa-briefcase"></i> Employment & Contact</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>Employment Designation:</label>
                        <p><strong><?= htmlspecialchars($faculty['status'] ?? 'N/A') ?></strong></p>
                    </div>
                    <div class="view-item">
                        <label>Email Address:</label>
                        <p><?= htmlspecialchars($faculty['email'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Contact Number:</label>
                        <p><?= htmlspecialchars($faculty['contact_no'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="view-footer">
            <a href="manage-faculty.php" class="close-btn-full"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <a href="admin-edit-faculty.php?id=<?= urlencode($faculty['faculty_no']) ?>" class="edit-btn"><i class="fa-solid fa-edit"></i> Edit</a>
        </div>
    </div>
</div>

</body>
</html>