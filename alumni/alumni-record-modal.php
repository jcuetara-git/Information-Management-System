<link rel="stylesheet" href="../assets/css/student-view-record.css">

<?php
// Fetch alumni profile row for viewing in the modal
$alumni_profile_row = null;
$alumni_age = '';

if (isset($conn) && !empty($student_no)) {
    $profile_stmt = $conn->prepare("SELECT * FROM alumni_profile WHERE student_no = ?");
    if ($profile_stmt) {
        $profile_stmt->bind_param("s", $student_no);
        $profile_stmt->execute();
        $profile_res = $profile_stmt->get_result();
        if ($profile_res && $profile_res->num_rows > 0) {
            $alumni_profile_row = $profile_res->fetch_assoc();
            
            if (!empty($alumni_profile_row['dob'])) {
                $dob_date = new DateTime($alumni_profile_row['dob']);
                $today = new DateTime('today');
                $alumni_age = $dob_date->diff($today)->y;
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
            <h2 style="margin-bottom: 20px; color: #1e293b;">My Alumni Record</h2>
            <?php if($alumni_profile_row): ?>

            <!-- PROFILE CARD -->
            <div class="profile-card">
                <div class="profile-left">
                    <form id="photoUploadForm" enctype="multipart/form-data">
                        <input type="file" name="photo" id="photoInput" hidden onchange="uploadAlumniPhoto()">
                    </form>
                    <div class="student-pic-container" onclick="document.getElementById('photoInput').click();" title="Click to change photo">
                        <img 
                        id="alumniProfileImg"
                        src="<?= !empty($alumni_profile_row['profile_pic']) ? '../uploads/'.$alumni_profile_row['profile_pic'] : '../assets/student.jpg'; ?>" 
                        class="student-pic"
                        alt="Alumni Photo"
                        style="transition: opacity 0.3s;"
                        >
                    </div>
                    <div>
                        <h3><?= htmlspecialchars($alumni_profile_row['first_name'] . " " . $alumni_profile_row['last_name']); ?></h3>
                        <p>ID Number: <?= htmlspecialchars($alumni_profile_row['student_no']); ?></p>
                        <p style="font-size: 13px; color: #64748b; margin-top: 5px;"><i class="fa-solid fa-briefcase"></i> <?= htmlspecialchars($alumni_profile_row['current_job']); ?></p>
                    </div>
                </div>
            </div>

            <!-- ACCORDION -->
            <div class="accordion">
                <!-- PERSONAL INFORMATION -->
                <button type="button" class="accordion-btn">Personal Information</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <!-- Column 1 -->
                        <div class="field">
                            <label>First Name</label>
                            <p><?= htmlspecialchars($alumni_profile_row['first_name']); ?></p>
                        </div>
                        <!-- Column 2 -->
                        <div class="field">
                            <label>Middle Name</label>
                            <p><?= !empty($alumni_profile_row['middle_name']) ? htmlspecialchars($alumni_profile_row['middle_name']) : 'N/A'; ?></p>
                        </div>

                        <div class="field">
                            <label>Last Name</label>
                            <p><?= htmlspecialchars($alumni_profile_row['last_name']); ?></p>
                        </div>
                        <div class="field">
                            <label>Date of Birth</label>
                            <p><?= !empty($alumni_profile_row['dob']) ? htmlspecialchars(date('F j, Y', strtotime($alumni_profile_row['dob']))) : 'N/A'; ?></p>
                        </div>

                        <div class="field">
                            <label>Age</label>
                            <p><?= htmlspecialchars($alumni_age ?: $alumni_profile_row['age']); ?></p>
                        </div>
                        <div class="field">
                            <label>Contact Number</label>
                            <p><?= htmlspecialchars($alumni_profile_row['contact_number']); ?></p>
                        </div>

                        <div class="field full-width">
                            <label>Email Address</label>
                            <p><?= htmlspecialchars($alumni_profile_row['email_address']); ?></p>
                        </div>
                    </div>
                </div>

                <!-- PROFESSIONAL & BOARD EXAM INFO -->
                <button type="button" class="accordion-btn">Professional Information</button>
                <div class="accordion-content">
                    <div class="info-grid">
                        <!-- Column 1 -->
                        <div class="field">
                            <label>Year Graduated</label>
                            <p><?= htmlspecialchars($alumni_profile_row['year_graduated']); ?></p>
                        </div>
                        <!-- Column 2 -->
                        <div class="field">
                            <label>Current Job / Occupation</label>
                            <p><?= htmlspecialchars($alumni_profile_row['current_job']); ?></p>
                        </div>

                        <div class="field">
                            <label>Date of Licensure Exam</label>
                            <p><?= !empty($alumni_profile_row['date_of_licensure_exam']) ? htmlspecialchars(date('F j, Y', strtotime($alumni_profile_row['date_of_licensure_exam']))) : 'N/A'; ?></p>
                        </div>
                        <div class="field">
                            <label>PRC Board Rating</label>
                            <p><?= !empty($alumni_profile_row['prc_board_rating']) ? htmlspecialchars($alumni_profile_row['prc_board_rating']) . '%' : 'N/A'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <?php else: ?>
                <div class="card" style="text-align: center; padding: 40px 20px;">
                    <p style="color: #64748b; margin-bottom: 15px;">No record found. <br> Please add your alumni information first.</p>
                    <button type="button" onclick="window.location.href='alumni-personal-info.php'" style="display: inline-block; background: #f4b42c; color: black; padding: 10px 20px; border: none; border-radius: 6px; cursor: pointer; text-decoration: none; font-size: 14px; font-weight: 600;">
                        Add Alumni Information
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Accordion Logic
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

    // AJAX function to upload photo without reloading the page
    function uploadAlumniPhoto() {
        const fileInput = document.getElementById('photoInput');
        const file = fileInput.files[0];
        
        if (!file) return;

        const formData = new FormData();
        formData.append('photo', file);

        const imgElement = document.getElementById('alumniProfileImg');
        const originalSrc = imgElement.src;
        
        imgElement.style.opacity = '0.5';

        fetch('alumni-upload-photo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                imgElement.src = data.new_image + '?t=' + new Date().getTime();
                imgElement.style.opacity = '1';
            } else {
                alert("Upload failed: " + data.message);
                imgElement.src = originalSrc;
                imgElement.style.opacity = '1';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Check the console.');
            imgElement.src = originalSrc;
            imgElement.style.opacity = '1';
        });
        
        fileInput.value = '';
    }
</script>