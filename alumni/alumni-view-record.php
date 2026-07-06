<?php
session_start();
include("../config/db.php");

// Ensure the alumni is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'alumni') {
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';

// ================= PROFILE PIC UPLOAD HANDLING =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['photo'])) {
    $targetDir = "../uploads/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileExt = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($fileExt, $allowedExts)) {
        // Create unique name using student number and timestamp
        $newFileName = "alumni_" . $student_no . "_" . time() . "." . $fileExt;
        $targetFilePath = $targetDir . $newFileName;
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
            // Update profile_pic column in your alumni_profile table
            $updateStmt = $conn->prepare("UPDATE alumni_profile SET profile_pic = ? WHERE student_no = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("ss", $newFileName, $student_no);
                $updateStmt->execute();
                $updateStmt->close();
            }
            header("Location: alumni-view-record.php?upload=success");
            exit();
        }
    }
}
// Query the latest record for the logged-in alumni
$stmt = $conn->prepare("SELECT * FROM alumni_profile WHERE student_no = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param("s", $student_no);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>alumni record</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student-view-record.css">
</head>

<body>

<div class="logo-section">
    <div class="logo-left">
        <div class="logo-circle">
            <img src="../assets/logo.png" alt="Logo">
        </div>
        <div class="logo-text">
            <h2>College of Criminal Justice</h2>
            <p>Center of Development in Criminology</p>
        </div>
    </div>
    <div class="profile-menu">
        <a href="../auth/logout.php">
            <i class="fa-solid fa-sign-out-alt"></i> 
            <span class="logout-text">Logout</span>
        </a>
    </div>
</div>

<div class="container">
    <h2>My Alumni Record</h2>
    <?php if($row): ?>

    <!-- PROFILE WITH WORKING UPLOAD FEATURE -->
    <div class="profile-card">
        <div class="profile-left">

            <!-- Hidden File Input and Auto-Submit Form -->
            <form action="alumni-view-record.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="photo" id="photoInput" hidden onchange="this.form.submit()">
            </form>

            <!-- Clickable Profile Picture Area -->
            <div class="student-pic-container" onclick="document.getElementById('photoInput').click();" title="Click to change photo" style="cursor: pointer;">
                <img 
                src="<?= !empty($row['profile_pic']) ? '../uploads/'.$row['profile_pic'] : '../assets/student.jpg'; ?>" 
                class="student-pic"
                alt="Alumni Photo"
                >
            </div>

            <div>
                <h3><?= htmlspecialchars($row['first_name'] . " " . (!empty($row['middle_name']) ? $row['middle_name'] . " " : "") . $row['last_name']); ?></h3>
                <p>Alumni ID: <?= htmlspecialchars($row['student_no']); ?></p>
            </div>
        </div>
    </div>

    <!-- ACCORDION -->
    <div class="accordion">

        <!-- SECTION 1: PERSONAL DETAILS -->
        <button class="accordion-btn">Personal Information</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>First Name</label>
                    <p><?= htmlspecialchars($row['first_name']); ?></p>
                </div>
                <div class="field">
                    <label>Middle Name</label>
                    <p><?= htmlspecialchars($row['middle_name'] ?: 'N/A'); ?></p>
                </div>
                <div class="field">
                    <label>Last Name</label>
                    <p><?= htmlspecialchars($row['last_name']); ?></p>
                </div>
                <div class="field">
                    <label>Date of Birth</label>
                    <p><?= htmlspecialchars($row['dob']); ?></p>
                </div>
                <div class="field">
                    <label>Age</label>
                    <p><?= htmlspecialchars($row['age']); ?></p>
                </div>
            </div>
        </div>

        <!-- SECTION 2: CONTACT DETAILS -->
        <button class="accordion-btn">Contact Information</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>Contact Number</label>
                    <p><?= htmlspecialchars($row['contact_number']); ?></p>
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <p><?= htmlspecialchars($row['email_address']); ?></p>
                </div>
            </div>
        </div>

        <!-- SECTION 3: ACADEMIC & LICENSURE HISTORY -->
        <button class="accordion-btn">Academic & Licensure Information</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>Year Graduated</label>
                    <p><?= htmlspecialchars($row['year_graduated']); ?></p>
                </div>
                <div class="field">
                    <label>Date of Licensure Exam</label>
                    <p><?= htmlspecialchars($row['date_of_licensure_exam'] ?: 'N/A'); ?></p>
                </div>
                <div class="field">
                    <label>PRC Board Exam Rating (%)</label>
                    <p><?= htmlspecialchars($row['prc_board_rating'] ? $row['prc_board_rating'] . '%' : 'N/A'); ?></p>
                </div>
            </div>
        </div>

        <!-- SECTION 4: PROFESSIONAL CAREER -->
        <button class="accordion-btn">Professional Employment Status</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field full-width">
                    <label>Current Job / Occupation</label>
                    <p style="white-space: pre-wrap;"><?= htmlspecialchars($row['current_job']); ?></p>
                </div>
            </div>
        </div>

    </div>

    <?php else: ?>
        <div class="card" style="text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <p style="color: #6b7280; font-size: 1.1rem; margin-bottom: 20px;">No records found. <br> Please complete your alumni profile information first.</p>
            <a href="alumni-add-portfolio.php">Add Alumni Profile Information</a>
        </div>
    <?php endif; ?>

    <!-- BACK BUTTON AT BOTTOM -->
    <div class="back-container">
        <a href="alumni-dashboard.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Back 
        </a>
    </div>

</div>

<!-- ACCORDION INTERACTION SCRIPT -->
<script>
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
</script>

</body>
</html>