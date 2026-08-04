    <link rel="stylesheet" href="../assets/css/student-view-record.css">

<?php
// Fetch student profile row for viewing in the modal
$student_profile_row = null;
$student_age = '';

if (isset($conn) && !empty($student_no)) {
    $profile_stmt = $conn->prepare("SELECT * FROM student_profile WHERE student_no = ?");
    if ($profile_stmt) {
        $profile_stmt->bind_param("s", $student_no);
        $profile_stmt->execute();
        $profile_res = $profile_stmt->get_result();
        if ($profile_res && $profile_res->num_rows > 0) {
            $student_profile_row = $profile_res->fetch_assoc();
            
            // Calculate age if DOB exists
            if (!empty($student_profile_row['dob'])) {
                $dob_date = new DateTime($student_profile_row['dob']);
                $today = new DateTime('today');
                $student_age = $dob_date->diff($today)->y;
            }
        }
        $profile_stmt->close();
    }
}
?>

<!-- ================= VIEW RECORD MODAL ================= -->
<div class="modal-overlay" id="recordModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeRecordModal()">&times;</span>
        <div class="container" style="width: 100%; padding: 10px 0;">
            <h2 style="margin-bottom: 20px; color: #1e293b;">My Student Record</h2>
            <?php if($student_profile_row): ?>

            <!-- PROFILE CARD -->
            <div class="profile-card">
                <div class="profile-left">
                    <form action="student-upload-photo.php" method="POST" enctype="multipart/form-data">
                        <input type="file" name="photo" id="photoInput" hidden onchange="this.form.submit()">
                    </form>
                    <div class="student-pic-container" onclick="document.getElementById('photoInput').click();" title="Click to change photo">
                        <img 
                        src="<?= !empty($student_profile_row['profile_pic']) ? '../uploads/'.$student_profile_row['profile_pic'] : '../assets/student.jpg'; ?>" 
                        class="student-pic"
                        alt="Student Photo"
                        >
                    </div>
                    <div>
                        <h3><?= htmlspecialchars($student_profile_row['first_name'] . " " . $student_profile_row['last_name']); ?></h3>
                        <p>ID Number: <?= htmlspecialchars($student_profile_row['student_no']); ?></p>
                    </div>
                </div>
            </div>

            <!-- ACCORDION -->
            <div class="accordion">
                <button type="button" class="accordion-btn">Personal Information</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <div class="field">
                            <label>First Name</label>
                            <p><?= htmlspecialchars($student_profile_row['first_name']); ?></p>
                        </div>
                        <div class="field">
                            <label>Middle Name</label>
                            <p><?= htmlspecialchars($student_profile_row['middle_name']); ?></p>
                        </div>
                        <div class="field">
                            <label>Last Name</label>
                            <p><?= htmlspecialchars($student_profile_row['last_name']); ?></p>
                        </div>
                        <div class="field">
                            <label>Date of Birth</label>
                            <p><?= htmlspecialchars($student_profile_row['dob']); ?></p>
                        </div>
                        <div class="field">
                            <label>Age</label>
                            <p><?= $student_age; ?></p>
                        </div>
                        <div class="field">
                            <label>Gender</label>
                            <p><?= htmlspecialchars($student_profile_row['gender']); ?></p>
                        </div>
                        <div class="field">
                            <label>Civil Status</label>
                            <p><?= htmlspecialchars($student_profile_row['civil_status']); ?></p>
                        </div>
                        <div class="field">
                            <label>Contact Number</label>
                            <p><?= htmlspecialchars($student_profile_row['contact_number']); ?></p>
                        </div>
                        <div class="field">
                            <label>Activities</label>
                            <p><?= htmlspecialchars($student_profile_row['activities']); ?></p>
                        </div>
                        <div class="field">
                            <label>Previous GPA</label>
                            <p><?= htmlspecialchars($student_profile_row['previous_gpa']); ?></p>
                        </div>
                    </div>
                </div>

                <button type="button" class="accordion-btn">Residential Information</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <div class="field full-width">
                            <label>Permanent Address</label>
                            <p><?= htmlspecialchars($student_profile_row['permanent_address']); ?></p>
                        </div>
                        <div class="field">
                            <label>City Address</label>
                            <p><?= htmlspecialchars($student_profile_row['city_address']); ?></p>
                        </div>
                        <div class="field">
                            <label>Housing Type</label>
                            <p><?= htmlspecialchars($student_profile_row['housing_type']); ?></p>
                        </div>
                    </div>
                </div>

                <button type="button" class="accordion-btn">Family Information</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <div class="field">
                            <label>Father</label>
                            <p><?= htmlspecialchars($student_profile_row['father_name']); ?></p>
                        </div>
                        <div class="field">
                            <label>Father's Occupation</label>
                            <p><?= htmlspecialchars($student_profile_row['father_occupation']); ?></p>
                        </div>
                        <div class="field">
                            <label>Mother</label>
                            <p><?= htmlspecialchars($student_profile_row['mother_name']); ?></p>
                        </div>
                        <div class="field">
                            <label>Mother's Occupation</label>
                            <p><?= htmlspecialchars($student_profile_row['mother_occupation']); ?></p>
                        </div>
                    </div>
                </div>

                <button type="button" class="accordion-btn">Emergency Contact</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <div class="field">
                            <label>Contact Person</label>
                            <p><?= htmlspecialchars($student_profile_row['emergency_person']); ?></p>
                        </div>
                        <div class="field">
                            <label>Contact Number</label>
                            <p><?= htmlspecialchars($student_profile_row['emergency_number']); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: ?>
                <div class="card" style="text-align: center; padding: 40px 20px;">
                    <p style="color: #64748b; margin-bottom: 15px;">No record found. <br> Please add your personal information first.</p>
                    <a href="student-personal-info.php" style="display: inline-block; background: #f4b42c; color: black; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px;">Add Personal Information</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const accButtons = document.querySelectorAll(".accordion-btn");
        accButtons.forEach(button => {
            button.addEventListener("click", function () {
                this.classList.toggle("active");
                const content = this.nextElementSibling;
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                }
            });
        });
    });
</script>