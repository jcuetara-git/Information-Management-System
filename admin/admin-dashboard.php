<?php
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <title>admin-dashboard</title>

    <!-- LINK CSS -->
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>

<body>

<div class="main-container">

    <!-- ================= SIDEBAR ================= -->
    <div class="sidebar">
        <ul>
            <li class="active"><i class="fa-solid fa-users"></i> Students</li>
            <li><i class="fa-solid fa-user-tie"></i> Faculty</li>
            <li><i class="fa-solid fa-briefcase"></i> Internship</li>
            <li><i class="fa-solid fa-folder"></i> Organizations</li>
            <li><i class="fa-solid fa-house"></i> Community Extension</li>
            <li><i class="fa-solid fa-file"></i> Indiana Jones</li>

        </ul>

        <div class="profile-icon">
            <i class="fa-solid fa-user"></i>
        </div>
    </div>

    <!-- ================= MAIN CONTENT ================= -->
    <div class="dashboard-container">

        <!-- HEADER -->
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

           
        </div>

        <!-- Welcome -->
        <div class="card welcome-card">
            <h1>Welcome, Admin!</h1>
            <p>View students and manage their account.</p>
        </div>

        <!-- Add Student -->
        <div class="card add-info-card" id="openModalBtn">
            <div class="plus-icon">+</div>
            <p>Add Student</p>
        </div>
         
        <!-- Button -->
        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='manage-students.php'">
                Manage Students
            </button>
        </div>

    </div>

</div>

<!-- ================= ADD STUDENT MODAL ================= -->
    <div id="studentModal" class="modal">

    <div class="modal-content">

        <!-- HEADER -->
        <div class="modal-header">
        <h2>Student Personal Information</h2>
        <span class="close">&times;</span>
        </div>

        <!-- FORM -->
        <form class="personal-form" method="POST" action="admin-save-student.php">

        <div class="form-grid">

            <!-- COLUMN 1 -->
            <div class="form-group">
            <label>ID Number</label>
            <input type="text" name="student_no" required>
            </div>

            <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" required>
            </div>

            <div class="form-group">
            <label>Middle Name</label>
            <input type="text" name="middle_name">
            </div>

            <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" required>
            </div>

            <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" id="dob" name="dob" required>
            </div>

            <div class="form-group">
            <label>Age</label>
            <input type="number" id="age" name="age" required>
            </div>

            <div class="form-group">
            <label>Gender</label>
            <div class="radio-group">
                <label><input type="radio" name="gender" value="Male"> Male</label>
                <label><input type="radio" name="gender" value="Female"> Female</label>
            </div>
            </div>

            <!-- COLUMN 2 -->
            <div class="form-group">
            <label>Civil Status</label>
            <input type="text" name="civil_status" required>
            </div>

            <div class="form-group">
            <label>Religion</label>
            <input type="text" name="religion">
            </div>

            <div class="form-group">
            <label>Permanent Address</label>
            <textarea name="permanent_address" required></textarea>
            </div>

            <div class="form-group">
            <label>Provincial/City Address</label>
            <textarea name="city_address" required></textarea>
            </div>

            <div class="form-group">
            <label>Housing Type (Kindly refer to Provincial/City Address)</label>
            <select name="housing_type">
                <option disabled selected>Select</option>
                <option>Owned</option>
                <option>Rented</option>
                <option>Free</option>
            </select>
            </div>

            <!-- COLUMN 3 -->
            <div class="form-group">
            <label>Contact Number</label>
            <input type="text" name="contact_number" required>
            </div>

            <div class="form-group">
            <label>Emergency Contact Person</label>
            <input type="text" name="emergency_person" required>
            </div>

            <div class="form-group">
            <label>Emergency Contact No.</label>
            <input type="text" name="emergency_number" required>
            </div>

            <div class="form-group">
            <label>Father's Name</label>
            <input type="text" name="father_name" required>
            </div>

            <div class="form-group">
            <label>Father's Occupation</label>
            <input type="text" name="father_occupation" required>
            </div>

            <div class="form-group">
            <label>Mother's Name</label>
            <input type="text" name="mother_name" required>
            </div>

            <div class="form-group">
            <label>Mother's Occupation</label>
            <input type="text" name="mother_occupation" required>
            </div>

            <!-- COLUMN 4 -->
            <div class="form-group">
            <label>Extracurricular Activities</label>
            <textarea name="activities"></textarea>
            </div>

            <div class="form-group">
            <label>Previous GPA</label>
            <input type="text" name="previous_gpa" required>
            </div>

        </div>

        <!-- BUTTONS -->
        <div class="modal-buttons">
            <button type="button" class="cancel-btn">Cancel</button>
            <button type="submit" class="save-btn">Save</button>
        </div>

        </form>

    </div>
    </div>

</div>

<script src="../assets/js/script.js"></script>

</body>
</html>