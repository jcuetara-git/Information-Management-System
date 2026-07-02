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
    header("Location: admin-alumni.php?error=Invalid alumni ID");
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
    header("Location: admin-alumni.php?error=Alumni not found");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Edit Alumni - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>Edit Alumni</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<div class="main-container">

    <?php 
    include("../includes/sidebar.php");
    ?>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="dashboard-container" id="mainContent" role="main">
        <section class="card welcome-card">
            <div class="welcome-content">
                <h2>Edit Alumni Information</h2>
                <p>Update alumni profile information.</p>
            </div>
        </section>

        <!-- EDIT FORM -->
        <section class="card edit-form-card">
            <form method="POST" action="admin-save-alumni.php" class="personal-form">
                <input type="hidden" name="student_no" value="<?= htmlspecialchars($alumni['student_no']) ?>">
                <input type="hidden" name="edit_mode" value="true">

                <div class="form-grid">

                    <!-- PERSONAL INFORMATION -->
                    <div class="form-group">
                        <label for="first_name">First Name <span aria-label="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($alumni['first_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="<?= htmlspecialchars($alumni['middle_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name <span aria-label="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($alumni['last_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dob">Date of Birth <span aria-label="required">*</span></label>
                        <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($alumni['dob'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="age">Age <span aria-label="required">*</span></label>
                        <input type="number" id="age" name="age" min="1" max="100" value="<?= htmlspecialchars($alumni['age'] ?? '') ?>" required>
                    </div>

                    <!-- GRADUATION & PROFESSIONAL INFORMATION -->
                    <div class="form-group">
                        <label for="year_graduated">Year Graduated <span aria-label="required">*</span></label>
                        <input type="number" id="year_graduated" name="year_graduated" min="1900" max="2100" placeholder="e.g. 2024" value="<?= htmlspecialchars($alumni['year_graduated'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="date_of_licensure_exam">Date of Licensure Exam</label>
                        <input type="date" id="date_of_licensure_exam" name="date_of_licensure_exam" value="<?= htmlspecialchars($alumni['date_of_licensure_exam'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="prc_board_rating">PRC Board Rating (%)</label>
                        <input type="text" id="prc_board_rating" name="prc_board_rating" placeholder="e.g. 85.50" value="<?= htmlspecialchars($alumni['prc_board_rating'] ?? '') ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="current_job">Current Job / Employment Details</label>
                        <input type="text" id="current_job" name="current_job" placeholder="e.g. Police Officer I / Security Consultant" value="<?= htmlspecialchars($alumni['current_job'] ?? '') ?>">
                    </div>

                    <!-- CONTACT INFORMATION -->
                    <div class="form-group">
                        <label for="email_address">Email Address <span aria-label="required">*</span></label>
                        <input type="email" id="email_address" name="email_address" value="<?= htmlspecialchars($alumni['email_address'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_number">Contact Number <span aria-label="required">*</span></label>
                        <input type="tel" id="contact_number" name="contact_number" value="<?= htmlspecialchars($alumni['contact_number'] ?? '') ?>" required>
                    </div>

                </div>  

                <!-- FORM BUTTONS -->
                <div class="modal-buttons">
                    <a href="admin-alumni.php" class="cancel-btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <button type="submit" class="save-btn"><i class="fa-solid fa-save"></i> Update Alumni</button>
                </div>

            </form>
        </section>

    </main>

</div>

<script>
    // Age Calculation Logic
    const dobInput = document.getElementById('dob');
    const ageInput = document.getElementById('age');

    if (dobInput && ageInput) {
        dobInput.addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) age--;
            if (age > 0 && age < 120) ageInput.value = age;
        });
    }
</script>

</body>
</html>