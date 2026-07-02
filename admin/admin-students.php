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
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-students.css">
    
    <style>
        /* Small style addition to make disabled fields look locked */
        .locked-field {
            opacity: 0.5;
            cursor: not-allowed !important;
            background-color: #e9ecef;
        }
    </style>
</head>

<body>

<div class="main-container">

    <?php 
    include("../includes/sidebar.php");
    ?>
    
    <main class="dashboard-container" id="mainContent" role="main">
        <section class="card welcome-card" aria-label="Welcome Section">
            <div class="welcome-content">
                <h2>Add Students</h2>
                <p>Add students and manage their account.</p>
            </div>
        </section>

        <section class="card add-info-card" id="openModalBtn" role="button" tabindex="0" aria-label="Add Student">
            <div class="plus-icon">
                <i class="fa-solid fa-plus"></i>
            </div>
            <p>Add Student</p>
        </section>
         
        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='manage-students.php'" aria-label="Go to Manage Students page">
                <i class="fa-solid fa-list"></i> Manage Students
            </button>
        </div>

    </main>

</div>

<div id="studentModal" class="modal" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">

    <div class="modal-content">

        <div class="modal-header">
            <h2 id="modalTitle">Student Personal Information</h2>
            <button class="close" aria-label="Close Modal" title="Close">&times;</button>
        </div>

        <form class="personal-form" method="POST" action="admin-save-student.php" novalidate>

            <div class="form-grid">

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="student_no">ID Number <span aria-label="required">*</span></label>
                    <input type="text" id="student_no" name="student_no" required autocomplete="off" placeholder="Enter ID to unlock form">
                    
                    <div id="id_message" style="margin-top: 8px; font-size: 14px; font-weight: bold; display: none;"></div>
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

                <div class="form-group">
                    <label for="activities">Extracurricular Activities</label>
                    <textarea id="activities" name="activities"></textarea>
                </div>

                <div class="form-group">
                    <label for="previous_gpa">Previous GPA <span aria-label="required">*</span></label>
                    <input type="text" id="previous_gpa" name="previous_gpa" required>
                </div>

            </div>  

            <div class="modal-buttons">
                <button type="button" class="cancel-btn" aria-label="Cancel Form">Cancel</button>
                <button type="submit" class="save-btn" id="saveBtn" aria-label="Save Student Information">Save Student</button>
            </div>

        </form>

    </div>
</div>

<script>
    // ==========================================
    // GLOBAL ELEMENT DECLARATIONS
    // ==========================================
    const studentNoInput = document.getElementById("student_no");
    const idMessage = document.getElementById("id_message");
    const saveBtn = document.getElementById("saveBtn");
    const form = document.querySelector(".personal-form");

    // Grab all inputs/selects/textareas EXCEPT the student_no
    const formFields = document.querySelectorAll(".personal-form input:not(#student_no), .personal-form select, .personal-form textarea");

    function lockForm() {
        formFields.forEach(field => {
            field.disabled = true;
            field.classList.add("locked-field");
        });
        saveBtn.disabled = true;
        saveBtn.classList.add("locked-field");
    }

    function unlockForm() {
        formFields.forEach(field => {
            field.disabled = false;
            field.classList.remove("locked-field");
        });
        saveBtn.disabled = false;
        saveBtn.classList.remove("locked-field");
    }

    // Check Database on 'blur' (when user clicks out of the ID input)
    studentNoInput.addEventListener("blur", async function() {
        const studentId = this.value.trim();

        if (studentId === "") {
            lockForm();
            idMessage.style.display = "none";
            return;
        }

        // Show loading state
        idMessage.style.display = "block";
        idMessage.style.color = "#007bff"; 
        idMessage.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking ID...';

        try {
            const response = await fetch(`check-student-id.php?id=${studentId}`);
            const data = await response.json();

            if (!data.registered) {
                lockForm();
                idMessage.style.color = "#dc3545"; 
                idMessage.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Error: Student must be registered first!';
            } else if (data.has_profile) {
                lockForm();
                idMessage.style.color = "#ffc107"; 
                idMessage.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Profile already exists for this ID.';
            } else {
                // Success! Unlock the form
                unlockForm();
                idMessage.style.color = "#28a745"; 
                idMessage.innerHTML = '<i class="fa-solid fa-circle-check"></i> Student found! Proceeding...';
                
                // Auto-fill names matching your input element IDs
                document.getElementById("first_name").value = data.first_name || "";
                document.getElementById("last_name").value = data.last_name || "";
            }
        } catch (error) {
            lockForm();
            idMessage.style.color = "#dc3545";
            idMessage.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Error checking database.';
            console.error(error);
        }
    });


    // SUBMIT BUTTON / REQUIRED FIELD VALIDATION
    form.addEventListener("submit", function(event) {
        let isValid = true;
        const requiredFields = form.querySelectorAll("[required]");

        requiredFields.forEach(field => {
            if (field.type === "radio") {
                const radioGroup = form.querySelectorAll(`input[name="${field.name}"]:checked`);
                if (radioGroup.length === 0) isValid = false;
            } else if (field.tagName === "SELECT") {
                if (field.selectedIndex === 0 || field.value === "") isValid = false;
            } else {
                if (!field.value.trim()) isValid = false;
            }
        });

        if (!isValid) {
            event.preventDefault(); 
            alert("Please completely fill out all required (*) fields before saving the student information.");
        }
    });

   
    // MODAL LOGIC
    const modal = document.getElementById("studentModal");
    const openBtn = document.getElementById("openModalBtn");
    const closeBtn = document.querySelector(".close");
    const cancelBtn = document.querySelector(".cancel-btn");

    function openModal() {
        modal.style.display = "block";
        modal.setAttribute("aria-hidden", "false");
        document.body.style.overflow = "hidden";
        
        form.reset();
        idMessage.style.display = "none";
        lockForm(); 
    }

    function closeModal() {
        modal.style.display = "none";
        modal.setAttribute("aria-hidden", "true");
        document.body.style.overflow = "auto";
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

    // ==========================================
    // AGE CALCULATION LOGIC
    // ==========================================
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