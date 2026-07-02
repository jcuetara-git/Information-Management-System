<?php
session_start();
include('../config/db.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>alumni-add-info</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student-add-info.css">
</head>

<body>
<div class="dashboard-container">

    <!-- ================= HEADER ================= -->
    <div class="logo-section">
        <div class="logo-left">
            <div class="logo-circle">
                <img src="../assets/logo.png" alt="College Logo">
            </div>

            <div class="logo-text">
                <h2>College of Criminal Justice</h2>
                <p>Center of Development in Criminology</p>
            </div>
        </div>
        <div class="profile-menu">
            <a href="../auth/logout.php">
                <i class="fa-solid fa-sign-out-alt"></i> 
                <span class="logout-text">Logout</span>
            </a>
        </div>
    </div>

    <!-- ================= MODAL ================= -->
    <div class="modal-overlay">
        <div class="personal-modal">

            <!-- Close Button with redirect -->
            <div class="close-btn" onclick="goBackToDashboard()">✖</div>

            <form class="personal-form" method="POST" action="save-alumni.php" onsubmit="return confirmSave()">

                <h3 class="form-title">Alumni Personal & Professional Information</h3>

                <!-- Alert Messages Container -->
                <?php if (isset($_SESSION['error_message'])): ?>
                    <div style="color: #721c24; background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 15px; border-radius: 4px;">
                        <?= $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <!-- FIXED: Added hidden field to pass the required student/alumni identification number -->
                <input type="hidden" name="student_no" value="<?= htmlspecialchars($_SESSION['student_no'] ?? ''); ?>">

                <div class="form-grid">

                    <!-- ================= COLUMN 1 ================= -->
                    <div class="form-column">

                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" value="<?= htmlspecialchars($_SESSION['first_name'] ?? ''); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name">
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="last_name" value="<?= htmlspecialchars($_SESSION['last_name'] ?? ''); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label>Date of Birth</label>
                            <input type="date" id="dob" name="dob" required>
                        </div>

                        <div class="form-group">
                            <label>Age</label>
                            <input type="number" id="age" name="age" required readonly>
                        </div>

                    </div>

                    <!-- ================= COLUMN 2 ================= -->
                    <div class="form-column">

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" required>
                        </div>

                        <div class="form-group">
                            <label for="email_address">Email Address</label>
                            <input type="email" id="email_address" name="email_address" required>
                        </div>

                        <div class="form-group">
                            <label for="year_graduated">Year Graduated</label>
                            <input type="number" id="year_graduated" name="year_graduated" placeholder="YYYY" required>
                        </div>

                    </div>

                    <!-- ================= COLUMN 3 ================= -->
                    <div class="form-column">

                        <div class="form-group">
                            <label for="date_of_licensure_exam">Date of Licensure Exam</label>
                            <input type="date" id="date_of_licensure_exam" name="date_of_licensure_exam">
                        </div>

                        <div class="form-group">
                            <label for="prc_board_exam_rating">PRC Board Exam Rating (%)</label>
                            <input type="number" step="0.01" id="prc_board_exam_rating" name="prc_board_exam_rating" placeholder="e.g., 85.50">
                        </div>

                    </div>

                    <!-- ================= COLUMN 4 ================= -->
                    <div class="form-column">

                        <div class="form-group">
                            <label for="current_job">Current Job / Occupation</label>
                            <textarea id="current_job" name="current_job" rows="4" placeholder="e.g., Police Officer I, Criminal Investigator, Security Consultant..." required></textarea>
                        </div>

                    </div>

                </div>

                <!-- BUTTONS -->
                <div class="modal-buttons">
                    <button type="button" class="cancel-btn" onclick="goBackToDashboard()">Cancel</button>
                    <button type="submit" class="save-btn">Save Information</button>
                </div>

            </form>

        </div>
    </div>
    <!-- ================= END MODAL ================= -->
</div>
 
<script>
// Function to redirect back to the dashboard
function goBackToDashboard() {
    window.location.href = 'alumni-dashboard.php';
}

// Function to confirm save
function confirmSave() {
    return confirm("Are you sure you want to save this information?");
}

// Function to calculate age from DOB
document.getElementById('dob').addEventListener('change', function() {
    const dob = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - dob.getFullYear();
    const m = today.getMonth() - dob.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
        age--;
    }
    document.getElementById('age').value = age;
});
</script>

<script src="../assets/js/script.js"></script>

</body>
</html>