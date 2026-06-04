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
    <title>Admin Dashboard - College of Criminal Justice</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>

<body>

<div class="main-container">

    <!-- ================= SIDEBAR ================= -->
    <nav class="sidebar" role="navigation" aria-label="Main Navigation">
        <ul>
            <li class="active" role="menuitem">
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
    <main class="dashboard-container" role="main">

        <!-- HEADER -->
        <header class="logo-section">
            <div class="logo-left">
                <div class="logo-circle">
                    <img src="../assets/logo.png" alt="College of Criminal Justice Logo">
                </div>

                <div class="logo-text">
                    <h1>College of Criminal Justice</h1>
                    <p>Center of Development in Criminology</p>
                </div>
            </div>
        </header>

        <!-- Welcome Section -->
        <section class="card welcome-card" aria-label="Welcome Section">
            <h2>Welcome, Admin!</h2>
            <p>View students and manage their account.</p>
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
        <form class="personal-form" method="POST" action="save-student.php" novalidate>

            <div class="form-grid">

                <!-- PERSONAL INFORMATION -->
                <div class="form-group">
                    <label for="student_no">ID Number <span aria-label="required">*</span></label>
                    <input type="text" id="student_no" name="student_no" placeholder="Enter ID Number" required>
                </div>

                <div class="form-group">
                    <label for="first_name">First Name <span aria-label="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" placeholder="First Name" required>
                </div>

                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" id="middle_name" name="middle_name" placeholder="Middle Name">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name <span aria-label="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" placeholder="Last Name" required>
                </div>

                <div class="form-group">
                    <label for="dob">Date of Birth <span aria-label="required">*</span></label>
                    <input type="date" id="dob" name="dob" required>
                </div>

                <div class="form-group">
                    <label for="age">Age <span aria-label="required">*</span></label>
                    <input type="number" id="age" name="age" placeholder="Age" min="1" max="100" required>
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
                    <input type="text" id="civil_status" name="civil_status" placeholder="Civil Status" required>
                </div>

                <!-- RELIGIOUS & RESIDENTIAL INFORMATION -->
                <div class="form-group">
                    <label for="religion">Religion</label>
                    <input type="text" id="religion" name="religion" placeholder="Religion">
                </div>

                <div class="form-group">
                    <label for="permanent_address">Permanent Address <span aria-label="required">*</span></label>
                    <textarea id="permanent_address" name="permanent_address" placeholder="Enter Permanent Address" required></textarea>
                </div>

                <div class="form-group">
                    <label for="city_address">Provincial/City Address <span aria-label="required">*</span></label>
                    <textarea id="city_address" name="city_address" placeholder="Enter Provincial/City Address" required></textarea>
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
                    <input type="tel" id="contact_number" name="contact_number" placeholder="Contact Number" required>
                </div>

                <div class="form-group">
                    <label for="emergency_person">Emergency Contact Person <span aria-label="required">*</span></label>
                    <input type="text" id="emergency_person" name="emergency_person" placeholder="Name of Emergency Contact" required>
                </div>

                <div class="form-group">
                    <label for="emergency_number">Emergency Contact No. <span aria-label="required">*</span></label>
                    <input type="tel" id="emergency_number" name="emergency_number" placeholder="Emergency Contact No." required>
                </div>

                <!-- FAMILY INFORMATION -->
                <div class="form-group">
                    <label for="father_name">Father's Name <span aria-label="required">*</span></label>
                    <input type="text" id="father_name" name="father_name" placeholder="Father's Full Name" required>
                </div>

                <div class="form-group">
                    <label for="father_occupation">Father's Occupation <span aria-label="required">*</span></label>
                    <input type="text" id="father_occupation" name="father_occupation" placeholder="Father's Occupation" required>
                </div>

                <div class="form-group">
                    <label for="mother_name">Mother's Name <span aria-label="required">*</span></label>
                    <input type="text" id="mother_name" name="mother_name" placeholder="Mother's Full Name" required>
                </div>

                <div class="form-group">
                    <label for="mother_occupation">Mother's Occupation <span aria-label="required">*</span></label>
                    <input type="text" id="mother_occupation" name="mother_occupation" placeholder="Mother's Occupation" required>
                </div>

                <!-- ACADEMIC INFORMATION -->
                <div class="form-group">
                    <label for="activities">Extracurricular Activities</label>
                    <textarea id="activities" name="activities" placeholder="List activities..."></textarea>
                </div>

                <div class="form-group">
                    <label for="previous_gpa">Previous GPA <span aria-label="required">*</span></label>
                    <input type="text" id="previous_gpa" name="previous_gpa" placeholder="Enter GPA" required>
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

<!-- Modal and Form Script -->
<script>
    // Get modal elements
    const modal = document.getElementById("studentModal");
    const openBtn = document.getElementById("openModalBtn");
    const closeBtn = document.querySelector(".close");
    const cancelBtn = document.querySelector(".cancel-btn");
    const form = document.querySelector(".personal-form");

    // Function to open modal
    function openModal() {
        modal.style.display = "block";
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
    }

    // Function to close modal
    function closeModal() {
        modal.style.display = "none";
        modal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "auto";
        form.reset();
    }

    // Open modal on button click
    openBtn.addEventListener("click", openModal);
    openBtn.addEventListener("keypress", function(event) {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            openModal();
        }
    });

    // Close modal on close button click
    closeBtn.addEventListener("click", closeModal);

    // Close modal on cancel button click
    cancelBtn.addEventListener("click", closeModal);

    // Close modal when clicking outside of it
    window.addEventListener("click", function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener("keydown", function(event) {
        if (event.key === "Escape" && modal.style.display === "block") {
            closeModal();
        }
    });

    // Auto-calculate age from date of birth
    const dobInput = document.getElementById("dob");
    const ageInput = document.getElementById("age");

    if (dobInput && ageInput) {
        dobInput.addEventListener("change", function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();

            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                age--;
            }

            if (age > 0 && age < 120) {
                ageInput.value = age;
            }
        });
    }
</script>

</body>
</html>
