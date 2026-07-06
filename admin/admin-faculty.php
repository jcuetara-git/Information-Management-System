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
    <meta name="description" content="Admin Dashboard - Faculty Management - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>admin-faculty</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-students.css">
</head>

<body>

<div class="main-container">

    <?php 
    include("../includes/sidebar.php");
    ?>
    
    <main class="dashboard-container" id="mainContent" role="main">
        <section class="card welcome-card" aria-label="Welcome Section">
            <div class="welcome-content">
                <h2>Add Faculty Members</h2>
                <p>Add faculty records and manage their professional portfolios.</p>
            </div>
        </section>

        <section class="card add-info-card" id="openModalBtn" role="button" tabindex="0" aria-label="Add Faculty Profile">
            <div class="plus-icon">
                <i class="fa-solid fa-plus"></i>
            </div>
            <p>Add Faculty Member</p>
        </section>
         
        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='manage-faculty.php'" aria-label="Go to Manage Faculty page">
                <i class="fa-solid fa-list"></i> Manage Faculty Records
            </button>
        </div>

    </main>

</div>

<!-- Faculty Account Registration & Metadata Modal Dialog -->
<div id="facultyModal" class="modal" role="dialog" aria-labelledby="modalTitle" aria-hidden="true">

    <div class="modal-content">

        <div class="modal-header">
            <h2 id="modalTitle">Faculty Personal Information</h2>
            <button class="close" aria-label="Close Modal" title="Close">&times;</button>
        </div>

        <form class="personal-form" method="POST" action="admin-save-faculty.php" novalidate>

            <div class="form-grid">

                <div class="form-group" style="grid-column: 1 / -1;">
                    <label for="faculty_no">ID Number <span aria-label="required">*</span></label>
                    <input type="text" id="faculty_no" name="faculty_no" required autocomplete="off" placeholder="Enter ID to unlock profile form">
                    
                    <div id="id_message" style="margin-top: 8px; font-size: 14px; font-weight: bold; display: none;"></div>
                </div>

                <div class="form-group">
                    <label for="first_name">First Name <span aria-label="required">*</span></label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>

                <div class="form-group">
                    <label for="last_name">Last Name <span aria-label="required">*</span></label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span aria-label="required">*</span></label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="contact_no">Contact Number <span aria-label="required">*</span></label>
                    <input type="tel" id="contact_no" name="contact_no" required>
                </div>

                <div class="form-group">
                    <label for="status">Employment Status <span aria-label="required">*</span></label>
                    <select id="status" name="status" required>
                        <option disabled selected value="">Select Employment Status</option>
                        <option value="Full-Time Regular">Full-Time Regular</option>
                        <option value="Full-Time Probationary">Full-Time Probationary</option>
                        <option value="Part-Time Lawyer">Part-Time Lawyer</option>
                        <option value="Part-Time Instructor">Part-Time Instructor</option>
                    </select>
                </div>

            </div>  

            <div class="modal-buttons">
                <button type="button" class="cancel-btn" aria-label="Cancel Form">Cancel</button>
                <button type="submit" class="save-btn" id="saveBtn" aria-label="Save Faculty Information">Save Profile</button>
            </div>

        </form>

    </div>
</div>

<script>
    // ==========================================
    // GLOBAL ELEMENT DECLARATIONS
    // ==========================================
    const facultyNoInput = document.getElementById("faculty_no");
    const idMessage = document.getElementById("id_message");
    const saveBtn = document.getElementById("saveBtn");
    const form = document.querySelector(".personal-form");

    const formFields = document.querySelectorAll(".personal-form input:not(#faculty_no), .personal-form select, .personal-form textarea");

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

    // Check Database on 'blur' when checking account references
    facultyNoInput.addEventListener("blur", async function() {
        const facultyId = this.value.trim();

        if (facultyId === "") {
            lockForm();
            idMessage.style.display = "none";
            return;
        }

        // Show loading state
        idMessage.style.display = "block";
        idMessage.style.color = "#007bff"; 
        idMessage.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Checking ID...';

        try {
            // Reusing your API validation architecture patterns mapped for faculty verification checks
            const response = await fetch(`check-faculty-id.php?id=${facultyId}`);
            const data = await response.json();

            if (!data.registered) {
                lockForm();
                idMessage.style.color = "#dc3545"; 
                idMessage.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Error: Faculty account must be registered first!';
            } else if (data.has_profile) {
                lockForm();
                idMessage.style.color = "#ffc107"; 
                idMessage.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i> A profile already exists for this ID number.';
            } else {
                unlockForm();
                idMessage.style.color = "#28a745"; 
                idMessage.innerHTML = '<i class="fa-solid fa-circle-check"></i> Faculty found! Proceeding...';
                
                document.getElementById("first_name").value = data.first_name || "";
                document.getElementById("last_name").value = data.last_name || "";
            }
        } catch (error) {
            lockForm();
            idMessage.style.color = "#dc3545";
            idMessage.innerHTML = '<i class="fa-solid fa-circle-xmark"></i> Error checking database accounts profile status.';
            console.error(error);
        }
    });

    // ==========================================
    // SUBMIT FORM REQUIRED VALIDATION CHECKER
    // ==========================================
    form.addEventListener("submit", function(event) {
        let isValid = true;
        const requiredFields = form.querySelectorAll("[required]");

        requiredFields.forEach(field => {
            if (field.tagName === "SELECT") {
                if (field.selectedIndex === 0 || field.value === "") isValid = false;
            } else {
                if (!field.value.trim()) isValid = false;
            }
        });

        if (!isValid) {
            event.preventDefault(); 
            alert("Please fill out all required (*) profile fields before updating faculty systems.");
        }
    });

    // ==========================================
    // MODAL WINDOW CONTROL ACTIONS
    // ==========================================
    const modal = document.getElementById("facultyModal");
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
</script>

</body>
</html>