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
    <meta name="description" content="Edit Student - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>Edit Student</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin-edit-student.css">
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
                <h2>Edit Student Information</h2>
                <p>Update student profile information.</p>
            </div>
        </section>

        <!-- EDIT FORM -->
        <section class="card edit-form-card">
            <form method="POST" action="admin-save-student.php" class="personal-form">
                <input type="hidden" name="student_no" value="<?= htmlspecialchars($student['student_no']) ?>">
                <input type="hidden" name="edit_mode" value="true">

                <div class="form-grid">

                    <!-- PERSONAL INFORMATION -->
                    <div class="form-group">
                        <label for="first_name">First Name <span aria-label="required">*</span></label>
                        <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($student['first_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="middle_name">Middle Name</label>
                        <input type="text" id="middle_name" name="middle_name" value="<?= htmlspecialchars($student['middle_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name <span aria-label="required">*</span></label>
                        <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($student['last_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="dob">Date of Birth <span aria-label="required">*</span></label>
                        <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($student['dob'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="age">Age <span aria-label="required">*</span></label>
                        <input type="number" id="age" name="age" min="1" max="100" value="<?= htmlspecialchars($student['age'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Gender <span aria-label="required">*</span></label>
                        <div class="radio-group">
                            <label>
                                <input type="radio" name="gender" value="Male" <?= ($student['gender'] ?? '') === 'Male' ? 'checked' : '' ?> required> Male
                            </label>
                            <label>
                                <input type="radio" name="gender" value="Female" <?= ($student['gender'] ?? '') === 'Female' ? 'checked' : '' ?> required> Female
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="civil_status">Civil Status <span aria-label="required">*</span></label>
                        <input type="text" id="civil_status" name="civil_status" value="<?= htmlspecialchars($student['civil_status'] ?? '') ?>" required>
                    </div>

                    <!-- RELIGIOUS & RESIDENTIAL INFORMATION -->
                    <div class="form-group">
                        <label for="religion">Religion</label>
                        <input type="text" id="religion" name="religion" value="<?= htmlspecialchars($student['religion'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="permanent_address">Permanent Address <span aria-label="required">*</span></label>
                        <textarea id="permanent_address" name="permanent_address" required><?= htmlspecialchars($student['permanent_address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="city_address">Provincial/City Address <span aria-label="required">*</span></label>
                        <textarea id="city_address" name="city_address" required><?= htmlspecialchars($student['city_address'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="housing_type">Housing Type <span aria-label="required">*</span></label>
                        <select id="housing_type" name="housing_type" required>
                            <option disabled>Select Housing Type</option>
                            <option value="Owned" <?= ($student['housing_type'] ?? '') === 'Owned' ? 'selected' : '' ?>>Owned</option>
                            <option value="Rented" <?= ($student['housing_type'] ?? '') === 'Rented' ? 'selected' : '' ?>>Rented</option>
                            <option value="Free" <?= ($student['housing_type'] ?? '') === 'Free' ? 'selected' : '' ?>>Free</option>
                        </select>
                    </div>

                    <!-- CONTACT INFORMATION -->
                    <div class="form-group">
                        <label for="contact_number">Contact Number <span aria-label="required">*</span></label>
                        <input type="tel" id="contact_number" name="contact_number" value="<?= htmlspecialchars($student['contact_number'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="emergency_person">Emergency Contact Person <span aria-label="required">*</span></label>
                        <input type="text" id="emergency_person" name="emergency_person" value="<?= htmlspecialchars($student['emergency_person'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="emergency_number">Emergency Contact No. <span aria-label="required">*</span></label>
                        <input type="tel" id="emergency_number" name="emergency_number" value="<?= htmlspecialchars($student['emergency_number'] ?? '') ?>" required>
                    </div>

                    <!-- FAMILY INFORMATION -->
                    <div class="form-group">
                        <label for="father_name">Father's Name <span aria-label="required">*</span></label>
                        <input type="text" id="father_name" name="father_name" value="<?= htmlspecialchars($student['father_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="father_occupation">Father's Occupation <span aria-label="required">*</span></label>
                        <input type="text" id="father_occupation" name="father_occupation" value="<?= htmlspecialchars($student['father_occupation'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="mother_name">Mother's Name <span aria-label="required">*</span></label>
                        <input type="text" id="mother_name" name="mother_name" value="<?= htmlspecialchars($student['mother_name'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="mother_occupation">Mother's Occupation <span aria-label="required">*</span></label>
                        <input type="text" id="mother_occupation" name="mother_occupation" value="<?= htmlspecialchars($student['mother_occupation'] ?? '') ?>" required>
                    </div>

                    <!-- ACADEMIC INFORMATION -->
                    <div class="form-group">
                        <label for="activities">Extracurricular Activities</label>
                        <textarea id="activities" name="activities"><?= htmlspecialchars($student['activities'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="previous_gpa">Previous GPA <span aria-label="required">*</span></label>
                        <input type="text" id="previous_gpa" name="previous_gpa" value="<?= htmlspecialchars($student['previous_gpa'] ?? '') ?>" required>
                    </div>

                </div>  

                <!-- FORM BUTTONS -->
                <div class="modal-buttons">
                    <a href="manage-students.php" class="cancel-btn"><i class="fa-solid fa-arrow-left"></i> Back</a>
                    <button type="submit" class="save-btn"><i class="fa-solid fa-save"></i> Update Student</button>
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
