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
    <meta name="description" content="Admin Dashboard - College of Criminal Justice Alumni">
    <meta name="theme-color" content="#f4b42c">
    <title>admin-alumni</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-students.css">
    
    <style>
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
                <h2>Add Alumni</h2>
                <p>Add an alumni and manage their account.</p>
            </div>
        </section>

        <section class="card add-info-card" id="openModalBtn" role="button" tabindex="0" aria-label="Add Alumni">
            <div class="plus-icon">
                <i class="fa-solid fa-plus"></i>
            </div>
            <p>Add Alumni</p>
        </section>
         
        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='manage-alumni.php'" aria-label="Go to Manage Alumni page">
                <i class="fa-solid fa-list"></i> Manage Alumni
            </button>
        </div>
    </main>

</div>

<div id="alumniModal" class="modal" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">

    <div class="modal-content">

        <div class="modal-header">
            <h2 id="modalTitle">Alumni Personal Information</h2>
            <button class="close" aria-label="Close Modal" title="Close">&times;</button>
        </div>

        <form class="personal-form" method="POST" action="admin-save-alumni.php" novalidate>

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
                    <input type="text" id="middle_name" name="middle_name">
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name <span aria-label="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" required>
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
                    <label for="contact_number">Contact Number <span aria-label="required">*</span></label>
                    <input type="tel" id="contact_number" name="contact_number" required>
                </div>

                <div class="form-group">
                    <label for="email_address">Email Address <span aria-label="required">*</span></label>
                    <input type="email" id="email_address" name="email_address" required>
                </div>

                <div class="form-group">
                    <label for="year_graduated">Year Graduated <span aria-label="required">*</span></label>
                    <input type="number" id="year_graduated" name="year_graduated" min="1900" max="2100" placeholder="e.g. 2024" required>
                </div>

                <div class="form-group">
                    <label for="date_of_licensure_exam">Date of Licensure Exam</label>
                    <input type="date" id="date_of_licensure_exam" name="date_of_licensure_exam">
                </div>

                <div class="form-group">
                    <label for="prc_board_rating">PRC Board Rating (%)</label>
                    <input type="text" id="prc_board_rating" name="prc_board_rating" placeholder="e.g. 85.50">
                </div>

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="current_job">Current Job / Employment Details</label>
                    <input type="text" id="current_job" name="current_job" placeholder="e.g. Police Officer I / Security Consultant">
                </div>

            </div>  

            <div class="modal-buttons">
                <button type="button" class="cancel-btn" aria-label="Cancel Form">Cancel</button>
                <button type="submit" class="save-btn" id="saveBtn" aria-label="Save Alumni Information">Save Alumni</button>
            </div>

        </form>

    </div>
</div>

<script>
    const studentNoInput = document.getElementById("student_no");
    const idMessage = document.getElementById("id_message");
    const saveBtn = document.getElementById("saveBtn");
    const form = document.querySelector(".personal-form");
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

    studentNoInput.addEventListener("blur", async function() {
        const studentId = this.value.trim();

        if (studentId === "") {
            lockForm();
            idMessage.style.display = "none";
            return;
        }

        idMessage.style.display = "block";
        idMessage.style.color = "#007bff"; 
        idMessage.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking ID...';

        try {
            const response = await fetch(`check-alumni-id.php?id=${studentId}`);
            const data = await response.json();

            if (!data.registered) {
                lockForm();
                idMessage.style.color = "#dc3545"; 
                idMessage.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Error: Student must be registered first!';
            } else if (data.has_profile) {
                lockForm();
                idMessage.style.color = "#ffc107"; 
                idMessage.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> Alumni profile already exists for this ID.';
            } else {
                unlockForm();
                idMessage.style.color = "#28a745"; 
                idMessage.innerHTML = '<i class="fa-solid fa-circle-check"></i> Student found! Proceeding...';
                
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

    form.addEventListener("submit", function(event) {
        let isValid = true;
        const requiredFields = form.querySelectorAll("[required]");

        requiredFields.forEach(field => {
            if (!field.value.trim()) isValid = false;
        });

        if (!isValid) {
            event.preventDefault(); 
            alert("Please completely fill out all required (*) fields before saving the alumni information.");
        }
    });

    const modal = document.getElementById("alumniModal");
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
    closeBtn.addEventListener("click", closeModal);
    cancelBtn.addEventListener("click", closeModal);
    window.addEventListener("click", (e) => { if (e.target === modal) closeModal(); });

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