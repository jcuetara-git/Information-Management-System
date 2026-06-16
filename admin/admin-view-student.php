<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// Get student ID from URL
$student_no = $_GET['id'] ?? null;

if(!$student_no){
    header("Location: manage-students.php?error=Invalid student ID");
    exit();
}

// Fetch student data
$query = "SELECT u.*, p.* FROM users u 
          LEFT JOIN student_profile p ON u.student_no = p.student_no 
          WHERE u.student_no = ? AND u.role = 'student'";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_no);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

if(!$student){
    header("Location: manage-students.php?error=Student not found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="View Student Details - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>View Student</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-view-student.css">
</head>
<body>

<div class="view-container">
    <div class="view-card">
        <div class="view-header">
            <h2><?= htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) ?></h2>
            <a href="manage-students.php" class="close-btn"><i class="fa-solid fa-times"></i></a>
        </div>
        
        <div class="view-body">
            <div class="view-section">
                <h3><i class="fa-solid fa-user"></i> Personal Information</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>ID Number:</label>
                        <p><?= htmlspecialchars($student['student_no']) ?></p>
                    </div>
                    <div class="view-item">
                        <label>First Name:</label>
                        <p><?= htmlspecialchars($student['first_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Middle Name:</label>
                        <p><?= htmlspecialchars($student['middle_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Last Name:</label>
                        <p><?= htmlspecialchars($student['last_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Date of Birth:</label>
                        <p><?= htmlspecialchars($student['dob'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Age:</label>
                        <p><?= htmlspecialchars($student['age'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Gender:</label>
                        <p><?= htmlspecialchars($student['gender'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Civil Status:</label>
                        <p><?= htmlspecialchars($student['civil_status'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <div class="view-section">
                <h3><i class="fa-solid fa-phone"></i> Contact Information</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>Email:</label>
                        <p><?= htmlspecialchars($student['email'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Contact Number:</label>
                        <p><?= htmlspecialchars($student['contact_number'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Emergency Person:</label>
                        <p><?= htmlspecialchars($student['emergency_person'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Emergency Number:</label>
                        <p><?= htmlspecialchars($student['emergency_number'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <div class="view-section">
                <h3><i class="fa-solid fa-home"></i> Residential Information</h3>
                <div class="view-grid">
                    <div class="view-item full-width">
                        <label>Permanent Address:</label>
                        <p><?= htmlspecialchars($student['permanent_address'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item full-width">
                        <label>Provincial/City Address:</label>
                        <p><?= htmlspecialchars($student['city_address'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Housing Type:</label>
                        <p><?= htmlspecialchars($student['housing_type'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Religion:</label>
                        <p><?= htmlspecialchars($student['religion'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <div class="view-section">
                <h3><i class="fa-solid fa-people-roof"></i> Family Information</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>Father's Name:</label>
                        <p><?= htmlspecialchars($student['father_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Father's Occupation:</label>
                        <p><?= htmlspecialchars($student['father_occupation'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Mother's Name:</label>
                        <p><?= htmlspecialchars($student['mother_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Mother's Occupation:</label>
                        <p><?= htmlspecialchars($student['mother_occupation'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <div class="view-section">
                <h3><i class="fa-solid fa-graduation-cap"></i> Academic Information</h3>
                <div class="view-grid">
                    <div class="view-item full-width">
                        <label>Extracurricular Activities:</label>
                        <p><?= htmlspecialchars($student['activities'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Previous GPA:</label>
                        <p><?= htmlspecialchars($student['previous_gpa'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Year Level:</label>
                        <p><?= htmlspecialchars($student['year_level'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="view-footer">
            <a href="admin-edit-student.php?id=<?= urlencode($student['student_no']) ?>" class="edit-btn"><i class="fa-solid fa-edit"></i> Edit</a>
            <a href="manage-students.php" class="close-btn-full"><i class="fa-solid fa-arrow-left"></i> Back</a>
        </div>
    </div>
</div>

</body>
</html>
