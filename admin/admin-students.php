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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Admin Dashboard - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>admin-students</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin-students.css">
</head>

<body>

<div class="main-container">

    <!-- ================= SIDEBAR ================= -->
    <nav class="sidebar" id="sidebar" role="navigation" aria-label="Main Navigation">
        <div class="sidebar-header">
            <button id="toggleSidebar" class="hamburger-btn" aria-label="Toggle Sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="sidebar-title">UC-MAIN CCJ</span>
        </div>

        <ul>
            <li role="menuitem" onclick="window.location.href='admin-dashboard.php'">
                <i class="fa-solid fa-chart-line"></i> 
                <span>Dashboard</span>
            </li>
            <li class="active" role="menuitem" onclick="window.location.href='admin-students.php'">
                <i class="fa-solid fa-users"></i> 
                <span>Students</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-user-tie"></i> 
                <span>Faculty</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-briefcase"></i> 
                <span>Internship</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-folder"></i> 
                <span>Organizations</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-house"></i> 
                <span>Community Extension</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-file"></i> 
                <span>Indiana Jones</span>
            </li>
        </ul>

        <div class="profile-menu">
            <a href="../auth/logout.php" role="menuitem">
                <i class="fa-solid fa-sign-out-alt"></i> 
                <span>Logout</span>
            </a>
        </div>
    </nav>

    <!-- ================= MAIN CONTENT ================= -->
    <main class="dashboard-container" id="mainContent" role="main">

        <!-- Welcome Section -->
        <section class="card welcome-card" aria-label="Welcome Section">
            <h2>Manage Students</h2>
            <p>View list of students and manage their account.</p>
        </section>

        <!-- Add Student Card -->
        <section class="card add-info-card" id="openModalBtn" role="button" tabindex="0" aria-label="Add Student">
            <div class="plus-icon">
                <i class="fa-solid fa-plus"></i>
            </div>
            <p>Add Student</p>
        </section>
         
        <!-- Action Buttons -->
        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='manage-students.php'" aria-label="Go to Manage Students page">
                <i class="fa-solid fa-list"></i> Manage Students
            </button>
        </div>

    </main>

</div>

<!-- ================= ADD STUDENT MODAL ================= -->
<div id="studentModal" class="modal" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">

    <div class="modal-content">

        <!-- MODAL HEADER -->
        <div class="modal-header">
            <h2 id="modalTitle">Student Personal Information</h2>
            <button class="close" aria-label="Close Modal" title="Close">&times;</button>
        </div>

        <!-- FORM -->
        <form class="personal-form" method="POST" action="admin-save-student.php" novalidate>

            <div class="form-grid">

                <!-- PERSONAL INFORMATION -->
                <div class="form-group">
                    <label for="student_no">ID Number <span aria-label="required">*</span></label>
                    <input type="text" id="student_no" name="student_no" required>
                </div>

                <div class="form-group">
                    <label for="first_name">First Name <span aria-label="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" >
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name <span aria-label="required">*</span></label>
                    <input type="text" id="last_name" name="last_name"  required>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth <span aria-label="required">*</span></label>
                    <input type="date" id="dob" name="dob" required>
                </div>

                <div class="form-group">
                    <label for="age">Age <span aria-label="required">*</span></label>
                    <input type="number" id="age" name="age" min="1" max="100" required>
                </div>

                <div class="form-group">
                    <label>Gender <span aria-label="required">*</span></label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="gender" value="Male" required> Male
                        </label>
                        <label>
                            <input type="radio" name="gender" value="Female" required> Female
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label for="civil_status">Civil Status <span aria-label="required">*</span></label>
                    <input type="text" id="civil_status" name="civil_status" required>
                </div>

                <!-- RELIGIOUS & RESIDENTIAL INFORMATION -->
                <div class="form-group">
                    <label for="religion">Religion</label>
                    <input type="text" id="religion" name="religion">
                </div>

                <div class="form-group">
                    <label for="permanent_address">Permanent Address <span aria-label="required">*</span></label>
                    <textarea id="permanent_address" name="permanent_address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="city_address">Provincial/City Address <span aria-label="required">*</span></label>
                    <textarea id="city_address" name="city_address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="housing_type">Housing Type <span aria-label="required">*</span></label>
                    <select id="housing_type" name="housing_type" required>
                        <option disabled selected>Select Housing Type</option>
                        <option>Owned</option>
                        <option>Rented</option>
                        <option>Free</option>
                    </select>
                </div>

                <!-- CONTACT INFORMATION -->
                <div class="form-group">
                    <label for="contact_number">Contact Number <span aria-label="required">*</span></label>
                    <input type="tel" id="contact_number" name="contact_number" required>
                </div>

                <div class="form-group">
                    <label for="emergency_person">Emergency Contact Person <span aria-label="required">*</span></label>
                    <input type="text" id="emergency_person" name="emergency_person" required>
                </div>

                <div class="form-group">
                    <label for="emergency_number">Emergency Contact No. <span aria-label="required">*</span></label>
                    <input type="tel" id="emergency_number" name="emergency_number" required>
                </div>

                <!-- FAMILY INFORMATION -->
                <div class="form-group">
                    <label for="father_name">Father's Name <span aria-label="required">*</span></label>
                    <input type="text" id="father_name" name="father_name" required>
                </div>

                <div class="form-group">
                    <label for="father_occupation">Father's Occupation <span aria-label="required">*</span></label>
                    <input type="text" id="father_occupation" name="father_occupation" required>
                </div>

                <div class="form-group">
                    <label for="mother_name">Mother's Name <span aria-label="required">*</span></label>
                    <input type="text" id="mother_name" name="mother_name" required>
                </div>

                <div class="form-group">
                    <label for="mother_occupation">Mother's Occupation <span aria-label="required">*</span></label>
                    <input type="text" id="mother_occupation" name="mother_occupation" required>
                </div>

                <!-- ACADEMIC INFORMATION -->
                <div class="form-group">
                    <label for="activities">Extracurricular Activities</label>
                    <textarea id="activities" name="activities"></textarea>
                </div>

                <div class="form-group">
                    <label for="previous_gpa">Previous GPA <span aria-label="required">*</span></label>
                    <input type="text" id="previous_gpa" name="previous_gpa" required>
                </div>

            </div>  

            <!-- MODAL BUTTONS -->
            <div class="modal-buttons">
                <button type="button" class="cancel-btn" aria-label="Cancel Form">Cancel</button>
                <button type="submit" class="save-btn" aria-label="Save Student Information">Save Student</button>
            </div>

        </form>

    </div>
</div>

<!-- Scripts -->
<script>
    // Sidebar Toggle Logic
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");
    const mainContent = document.getElementById("mainContent");

    toggleBtn.addEventListener("click", () => {
        sidebar.classList.toggle("collapsed");
        mainContent.classList.toggle("expanded");
    });

    // Modal Logic
    const modal = document.getElementById("studentModal");
    const openBtn = document.getElementById("openModalBtn");
    const closeBtn = document.querySelector(".close");
    const cancelBtn = document.querySelector(".cancel-btn");
    const form = document.querySelector(".personal-form");

    function openModal() {
        modal.style.display = "block";
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
    }

    function closeModal() {
        modal.style.display = "none";
        modal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "auto";
        form.reset();
    }

    openBtn.addEventListener("click", openModal);
    openBtn.addEventListener("keypress", (e) => {
        if (e.key === "Enter" || e.key === " ") {
            e.preventDefault();
            openModal();
        }
    });

    closeBtn.addEventListener("click", closeModal);
    cancelBtn.addEventListener("click", closeModal);
    window.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });
    document.addEventListener("keydown", (e) => { if (e.key === "Escape" && modal.style.display === "block") closeModal(); });

    // Age Calculation Logic
    const dobInput = document.getElementById("dob");
    const ageInput = document.getElementById("age");

    if (dobInput && ageInput) {
        dobInput.addEventListener("change", function() {
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
