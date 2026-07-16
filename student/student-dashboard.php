<?php
include("../config/auth.php");
include("../config/db.php"); 
include 'retention-policy.php';

// Ensure logged in as student
if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';
$last_name  = $_SESSION['last_name'] ?? '';

// Check if student has already filled up personal information
$info_filled = false;
if (!empty($student_no)) {
    $query = "SELECT id FROM student_profile WHERE student_no = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt) {
        $stmt->bind_param("s", $student_no); 
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $info_filled = true;
        }
        $stmt->close();
    }
}

// Fetch announcements for dropdown
$announcements = [];
$conn->query("SET time_zone = '+08:00'");
$query = "SELECT title, message, created_at, 
          (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
          FROM announcements 
          WHERE status = 'published' 
          AND (target_audience = 'all' OR target_audience = 'students' OR (target_audience = 'specific_user' AND target_user_id = ?))
          ORDER BY created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_SESSION['student_no']);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
$stmt->close();

// Query the student_profile table for the logged-in student's record (for View Record Modal)
$stmt_profile = $conn->prepare("SELECT * FROM student_profile WHERE student_no = ?");
$stmt_profile->bind_param("s", $student_no);
$stmt_profile->execute();
$result_profile = $stmt_profile->get_result();
$student_profile_row = $result_profile->fetch_assoc();

// Calculate age if date of birth exists
$student_age = "";
if($student_profile_row && !empty($student_profile_row['dob'])){
    $birthDate = new DateTime($student_profile_row['dob']);
    $today = new DateTime();
    $student_age = $today->diff($birthDate)->y;
}
$stmt_profile->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>student-dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    <link rel="stylesheet" href="../assets/css/student-view-record.css">
</head>
<body>

<?php if(isset($_GET['success'])): ?>
    <div class="success-overlay">
        <div class="success-box">
            <p>Student information saved successfully!</p>
            <button onclick="this.parentElement.parentElement.style.display='none'">OK</button>
        </div>
    </div>
<?php endif; ?>

<div class="dashboard-container">

    <!-- HEADER -->
    <div class="logo-section">
        <div class="logo-left">
            <div class="logo-circle">
                <img src="../assets/logo.png">
            </div>

            <div class="logo-text">
                <h2>College of Criminal Justice</h2>
                <p>Center of Development in Criminology</p>
            </div>
        </div>

        <div class="header-right">
            <!-- NOTIFICATION DROPDOWN -->
            <div class="notification-container">
                <button class="notification-btn" id="notifBtn">
                    <i class="fa-solid fa-bell"></i><span class="notif-badge"><?= count($announcements); ?></span>
                </button>
                <div class="notification-dropdown" id="notifDropdown">
                    <div class="notification-header">Recent Notifications</div>
                    <?php if (count($announcements) > 0): ?>
                        <?php foreach ($announcements as $announce): ?>
                            <div class="notification-item">
                                <div class="notif-title">
                                    <?= htmlspecialchars($announce['title']) ?>
                                    <?php if ($announce['is_new'] == 1): ?>
                                        <span class="notif-new-badge">NEW</span>
                                    <?php endif; ?>
                                </div>
                                <div class="notif-time"><?= date('M d, Y h:i A', strtotime($announce['created_at'])) ?></div>
                                <div class="notif-msg"><?= htmlspecialchars($announce['message']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div style="padding: 20px; text-align: center; color: #94a3b8; font-size: 13px;">
                            No announcements
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- PROFILE DROPDOWN -->
            <div class="profile-container">
                <div class="profile-menu" id="profileBtn">
                    <div class="profile-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>
                    <span class="profile-name"><?= htmlspecialchars($first_name); ?></span>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-dropdown-header">
                        <div class="profile-avatar"><?= strtoupper(substr($first_name, 0, 1)); ?></div>
                        <div class="profile-info">
                            <h4><?= htmlspecialchars($first_name . ' ' . $last_name); ?></h4>
                            <p><?= htmlspecialchars($student_no); ?></p>
                        </div>
                    </div>
                    <div class="profile-logout" onclick="window.location.href='../auth/logout.php'">
                        <i class="fa-solid fa-sign-out-alt"></i> Logout
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WELCOME -->
    <div class="card welcome-card">
        <h1>Hi, <?= htmlspecialchars($first_name); ?>!👋</h1>
        <p>Add your personal information and view your record.</p>
    </div>

    <!-- PROGRAM CARDS GRID -->
    <div class="program-grid">
        
        <!-- Personal Information -->
        <div class="program-card">
            <div class="card-header">
                <div class="icon-circle">
                    <i class="fa-solid fa-user"></i>
                </div>
                <div class="card-title-area">
                    <h3>Personal Information</h3>
                    <p class="card-desc">Add your student personal information.</p>
                </div>
            </div>
            <div class="btn-container">
                <a href="javascript:void(0)" class="program-btn" onclick="openInfoModal()">
                    <span><i class="fa-solid fa-user-gear"></i> Add Information</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <!-- Student Record -->
        <div class="program-card">
            <div class="card-header">
                <div class="icon-circle">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <div class="card-title-area">
                    <h3>My Student Record</h3>
                    <p class="card-desc">View your student information.</p>
                </div>
            </div>
            <div class="btn-container">
                <a href="javascript:void(0)" class="program-btn" onclick="openViewRecordModal()">
                    <span><i class="fa-solid fa-file-lines"></i> View Record</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
        </div>
        

        <!-- Retention Policy -->
        <div class="program-card">
            <div>
                <div class="card-header">
                    <div class="icon-circle">
                        <i class="fa-solid fa-graduation-cap"></i>
                    </div>
                    <div class="card-title-area">
                        <h3>Retention Policy</h3>
                        <p class="card-desc">For students with three (3) or more failed professional subjects.</p>
                    </div>
                </div>
                <div class="card-info-box">
                    <i class="fa-solid fa-circle-info info-icon"></i>
                    <p class="info-text">You are required to submit a Letter of Undertaking and commit to improve your academic performance.</p>
                </div>
            </div>
            <div class="btn-container">
                <!-- Your trigger button -->
                <button class="program-btn" onclick="document.getElementById('retentionModal').style.display='block'">
                    <span><i class="fa-solid fa-file-signature"></i> View Details / Submit LOU</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- Indiana Jones Program -->
        <div class="program-card">
            <div>
                <div class="card-header">
                    <div class="icon-circle">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div class="card-title-area">
                        <h3>Indiana Jones Program</h3>
                        <p class="card-desc">For students with three (3) consecutive absences.</p>
                    </div>
                </div>
                <div class="card-info-box">
                    <i class="fa-solid fa-circle-info info-icon"></i>
                    <p class="info-text">You are required to submit a Letter of Undertaking and commit to maintain regular attendance.</p>
                </div>
            </div>
            <div class="btn-container">
                <button class="program-btn">
                    <span><i class="fa-solid fa-file-signature"></i> View Details / Submit LOU</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>

    </div>

    <div style="text-align: center; padding: 30px; color: #999; font-size: 13px;">
        ©2026 College of Criminal Justice | Version 1.1
    </div>

</div>

<!-- ================= ADD INFO MODAL ================= -->
<div class="modal-overlay" id="infoModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeModal()">&times;</span>
        <form class="personal-form" method="POST" action="save-student.php" onsubmit="return confirmSave()">
            <h3 class="form-title">Student Personal Information</h3>
            <div class="form-grid">
                <!-- COLUMN 1 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>ID Number</label> 
                        <input type="text" name="id_number" value="<?= htmlspecialchars($student_no); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name" value="<?= htmlspecialchars($first_name); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middle_name">
                    </div>
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name" value="<?= htmlspecialchars($last_name); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" id="dob" name="dob" required>
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" id="age" name="age" required readonly>
                    </div>
                    <div class="form-group">
                        <label>Gender</label>
                        <div class="radio-group">
                            <label><input type="radio" name="gender" value="Male" required> Male</label>
                            <label><input type="radio" name="gender" value="Female"> Female</label>
                        </div>
                    </div>
                </div>
                <!-- COLUMN 2 -->
                <div class="form-column">
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
                        <textarea name="permanent_address" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Provincial/City Address</label>
                        <textarea name="city_address" rows="2" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Housing Type</label>
                        <select name="housing_type">
                            <option value="" disabled selected>Select</option>
                            <option value="Owned">Owned</option>
                            <option value="Rented">Rented</option>
                            <option value="Free">Staying for Free</option>
                        </select>
                    </div>
                </div>
                <!-- COLUMN 3 -->
                <div class="form-column">
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
                        <input type="text" name="father_name">
                    </div>
                    <div class="form-group">
                        <label>Father's Occupation</label>
                        <input type="text" name="father_occupation">
                    </div>
                    <div class="form-group">
                        <label>Mother's Name</label>
                        <input type="text" name="mother_name">
                    </div>
                    <div class="form-group">
                        <label>Mother's Occupation</label>
                        <input type="text" name="mother_occupation">
                    </div>
                </div>
                <!-- COLUMN 4 -->
                <div class="form-column">
                    <div class="form-group">
                        <label>Extracurricular Activities</label>
                        <textarea name="activities" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label>Previous GPA</label>
                        <input type="text" name="previous_gpa" required>
                    </div>
                </div>
            </div>
            <div class="modal-buttons">
                <button type="button" class="cancel-btn" onclick="closeModal()">Cancel</button>
                <button type="submit" class="save-btn">Save</button>
            </div>
        </form>
    </div>
</div>

<!-- ================= VIEW RECORD MODAL ================= -->
<div class="modal-overlay" id="viewRecordModal">
    <div class="personal-modal">
        <span class="close-btn" onclick="closeViewRecordModal()">&times;</span>
        <div class="container" style="width: 100%; padding: 10px, 0;">
            <h2 style="margin-bottom: 20px; color: #1e293b;">My Student Record</h2>
            <?php if($student_profile_row): ?>

            <!-- PROFILE -->
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
                <button class="accordion-btn">Personal Information</button>
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

                <button class="accordion-btn">Residential Information</button>
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

                <button class="accordion-btn">Family Information</button>
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

                <button class="accordion-btn">Emergency Contact</button>
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
                    <a href="javascript:void(0)" onclick="closeViewRecordModal(); openInfoModal();" style="display: inline-block; background: #1e293b; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px;">Add Personal Information</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="../assets/js/script.js"></script>
<script>
    // Add Info Modal
    function openInfoModal() {
        document.getElementById('infoModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('infoModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // View Record Modal
    function openViewRecordModal() {
        document.getElementById('viewRecordModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeViewRecordModal() {
        document.getElementById('viewRecordModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function confirmSave() {
        return confirm("Are you sure you want to save this information?");
    }

    document.addEventListener('DOMContentLoaded', function() {
        const notifBtn = document.getElementById('notifBtn');
        const notifDropdown = document.getElementById('notifDropdown');
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        const infoModal = document.getElementById('infoModal');
        const viewRecordModal = document.getElementById('viewRecordModal');

        if (notifBtn) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notifDropdown.classList.toggle('active');
                profileDropdown.classList.remove('active');
            });
        }

        if (profileBtn) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileDropdown.classList.toggle('active');
                notifDropdown.classList.remove('active');
            });
        }

        document.addEventListener('click', function(event) {
            if (notifDropdown && !notifBtn.contains(event.target) && !notifDropdown.contains(event.target)) {
                notifDropdown.classList.remove('active');
            }
            if (profileDropdown && !profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove('active');
            }
            if (event.target === infoModal) {
                closeModal();
            }
            if (event.target === viewRecordModal) {
                closeViewRecordModal();
            }
        });

        const dobInput = document.getElementById('dob');
        const ageInput = document.getElementById('age');
        if (dobInput && ageInput) {
            dobInput.addEventListener('change', function() {
                const dob = new Date(this.value);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) { age--; }
                ageInput.value = age;
            });
        }

        // Accordion Script for View Record Modal
        document.querySelectorAll(".accordion-btn").forEach(btn => {
            btn.addEventListener("click", function(){
                this.classList.toggle("active");
                let panel = this.nextElementSibling;
                if (panel.style.maxHeight) {
                    panel.style.maxHeight = null;
                } else {
                    panel.style.maxHeight = panel.scrollHeight + "px";
                }
            });
        });
    });
</script>
</body>
</html>
