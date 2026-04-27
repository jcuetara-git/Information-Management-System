<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>student-add-info</title>
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
                <div class="profile-icon" onclick="toggleMenu()">
                    <span>👤</span>
                </div>

                <div class="profile-dropdown" id="profileDropdown">
                 <a href="../auth/logout.php">Logout</a>
                </div>
            </div>

        </div>

    <!-- ================= MODAL ================= -->
    <div class="modal-overlay">
        <div class="personal-modal">

            <div class="close-btn">✖</div>

            <form class="personal-form" method="POST" action="save-student.php" onsubmit="return confirmSave()">

                <h3 class="form-title">Student Personal Information</h3>

                <div class="form-grid">

                    <!-- ================= COLUMN 1 ================= -->
                    <div class="form-column">

                        <div class="form-group">
                            <label for="id_number">ID Number</label> 
                            <input type="text" name="id_number" value="<?= $_SESSION['student_no'] ?? ''; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" name="first_name" value="<?=$_SESSION['first_name'] ?? ''; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="middle_name">Middle Name</label>
                            <input type="text" id="middle_name" name="middle_name">
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" name="first_name" value="<?=$_SESSION['last_name'] ?? ''; ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="birthdate">Date of Birth</label>
                            <input type="date" id="dob" name="dob" max="<?= date('Y-m-d'); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="age">Age</label>
                            <input type="number" id="age" name="age" min="1" required>
                        </div>

                        <div class="form-group">
                            <label>Gender</label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="gender" value="Male" required> Male
                                </label>
                                <label>
                                    <input type="radio" name="gender" value="Female"> Female
                                </label>
                            </div>
                        </div>

                    </div>


                    <!-- ================= COLUMN 2 ================= -->
                    <div class="form-column">

                        <div class="form-group">
                            <label for="civil_status">Civil Status</label>
                            <input type="text" id="civil_status" name="civil_status">
                        </div>

                        <div class="form-group">
                            <label for="religion">Religion</label>
                            <input type="text" id="religion" name="religion">
                        </div>

                        <div class="form-group">
                            <label for="permanent_address">Permanent Address</label>
                            <textarea id="permanent_address" name="permanent_address" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="city_address">Provincial/City Address</label>
                            <textarea id="city_address" name="city_address" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="housing_type">Housing Type (Refer to Provincial/City Address)</label>
                            <select id="housing_type" name="housing_type">
                                <option value="" disabled selected>Select</option>
                                <option value="Owned">Owned</option>
                                <option value="Rented">Rented</option>
                                <option value="Free">Staying for Free</option>
                            </select>
                        </div>

                    </div>


                    <!-- ================= COLUMN 3 ================= -->
                    <div class="form-column">

                        <div class="form-group">
                            <label for="contact_number">Contact Number</label>
                            <input type="text" id="contact_number" name="contact_number" required>
                        </div>

                        <div class="form-group">
                            <label for="emergency_person">Emergency Contact Person</label>
                            <input type="text" id="emergency_person" name="emergency_person" required>
                        </div>

                        <div class="form-group">
                            <label for="emergency_number">Emergency Contact No.</label>
                            <input type="text" id="emergency_number" name="emergency_number" required>
                        </div>

                        <div class="form-group">
                            <label for="father_name">Father's Name</label>
                            <input type="text" id="father_name" name="father_name">
                        </div>

                        <div class="form-group">
                            <label for="father_occupation">Father's Occupation</label>
                            <input type="text" id="father_occupation" name="father_occupation">
                        </div>

                        <div class="form-group">
                            <label for="mother_name">Mother's Name</label>
                            <input type="text" id="mother_name" name="mother_name">
                        </div>

                        <div class="form-group">
                            <label for="mother_occupation">Mother's Occupation</label>
                            <input type="text" id="mother_occupation" name="mother_occupation">
                        </div>

                    </div>


                    <!-- ================= COLUMN 4 ================= -->
                    <div class="form-column">

                        <div class="form-group">
                            <label for="activities">Extracurricular Activities</label>
                            <textarea id="activities" name="activities" rows="4"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="previous_gpa">Previous GPA</label>
                            <input type="text" id="previous_gpa" name="previous_gpa">
                        </div>

                    </div>

                </div>

                <!--BUTTONS  -->
                <div class="modal-buttons">
                    <button type="button" class="cancel-btn">Cancel</button>
                    <button type="submit" class="save-btn">Save</button>
                </div>

            </form>

        </div>
    </div>
    <!-- ================= END MODAL ================= -->
</div>
 
<script src="../assets/js/script.js"></script>

</body>
</html>