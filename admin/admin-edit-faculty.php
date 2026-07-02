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

// Fixed query: Selecting only verified columns (Removed u.middle_name and extra profile fields)
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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>admin-edit-faculty</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-edit-student.css">
</head>
<body>

<div class="main-container">
    <?php include("../includes/sidebar.php"); ?>

    <main class="dashboard-container" id="mainContent" role="main">
        <section class="card welcome-card">
            <div class="welcome-content">
                <h2>Edit Faculty Information</h2>
                <p>Update faculty configuration and account details.</p>
            </div>
        </section>

        <section class="card edit-form-card">
            <form method="POST" action="admin-save-faculty.php" class="personal-form">
                <input type="hidden" name="faculty_no" value="<?= htmlspecialchars($faculty['faculty_no']) ?>">

                <div class="form-grid">
                    <div class="form-group">
                        <label for="first_name">First Name <span style="color:red;">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($faculty['first_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name <span style="color:red;">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($faculty['last_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_no">Contact Number <span style="color:red;">*</span></label>
                        <input type="tel" id="contact_no" name="contact_no" value="<?= htmlspecialchars($faculty['contact_no'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Employment Status <span style="color:red;">*</span></label>
                        <select id="status" name="status" required>
                            <option value="" disabled>Select Status Designation</option>
                            <option value="Full-Time Regular" <?= ($faculty['status'] ?? '') === 'Full-Time Regular' ? 'selected' : '' ?>>Full-Time Regular</option>
                            <option value="Full-Time Probationary" <?= ($faculty['status'] ?? '') === 'Full-Time Probationary' ? 'selected' : '' ?>>Full-Time Probationary</option>
                            <option value="Part-Time Lawyer" <?= ($faculty['status'] ?? '') === 'Part-Time Lawyer' ? 'selected' : '' ?>>Part-Time Lawyer</option>
                            <option value="Part-Time Instructor" <?= ($faculty['status'] ?? '') === 'Part-Time Instructor' ? 'selected' : '' ?>>Part-Time Instructor</option>
                        </select>
                    </div>
                </div>  

                <div class="modal-buttons" style="margin-top: 20px;">
                    <a href="manage-faculty.php" class="cancel-btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <button type="submit" class="save-btn"><i class="fa-solid fa-save"></i> Update Record</button>
                </div>
            </form>
        </section>
    </main>
</div>

</body>
</html>