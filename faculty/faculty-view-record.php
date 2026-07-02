<?php
session_start();
include("../config/db.php");

// Protect page access - ensure user is logged in and is a faculty member
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}

$faculty_id = $_SESSION['student_no'];

// Safely pull first and last names directly from the logged-in session variables
$first_name = $_SESSION['first_name'] ?? 'Faculty';
$last_name = $_SESSION['last_name'] ?? '';
$full_name = trim($first_name . " " . $last_name);

// Handle Profile Picture Upload via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $targetDir = "../uploads/profiles/";
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileExt = strtolower(pathinfo($_FILES['profile_pic']['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];
    
    if (in_array($fileExt, $allowedExts)) {
        $newFileName = $faculty_id . "_" . time() . "." . $fileExt;
        $targetFilePath = $targetDir . $newFileName;
        
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $targetFilePath)) {
            // Save the relative path into the database column 'profile_pic'
            $dbPath = "uploads/profiles/" . $newFileName;
            $updateStmt = $conn->prepare("UPDATE faculty_profile SET profile_pic = ? WHERE faculty_no = ?");
            if ($updateStmt) {
                $updateStmt->bind_param("ss", $dbPath, $faculty_id);
                $updateStmt->execute();
                $updateStmt->close();
            }
            
            header("Location: faculty-view-record.php?upload=success");
            exit();
        }
    }
}

// Initialize profile data variables
$email = "";
$contact_no = "";
$status = "";
$profile_pic = "";

// Initialize portfolio document paths
$portfolio = [
    'cv' => null, 'tor' => null, 'diploma' => null, 'prc_license' => null,
    'certificates_membership' => null, 'seminars_regional' => null, 
    'seminars_national' => null, 'seminars_international' => null,
    'research_cert' => null, 'research_presenter' => null, 
    'community_extension' => null, 'test_questionnaires' => null, 
    'syllabi' => null, 'tos' => null
];

// Fetch faculty profile details
$query = "SELECT email, contact_no, status, profile_pic, cv, tor, diploma, prc_license, certificates_membership, seminars_regional, seminars_national, seminars_international, research_cert, research_presenter, community_extension, test_questionnaires, syllabi, tos FROM faculty_profile WHERE faculty_no = ? LIMIT 1";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("<h3>Database Query Error!</h3><p><strong>MySQL Error:</strong> " . htmlspecialchars($conn->error) . "</p>");
}

$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $email = $row['email'];
    $contact_no = $row['contact_no'];
    $status = $row['status'];
    $profile_pic = $row['profile_pic'] ?? '';
    
    // Map paths from database
    foreach ($portfolio as $key => $val) {
        $portfolio[$key] = $row[$key] ?? null;
    }
}
$stmt->close();

/**
 * Robust helper function to output attached documents.
 */
function renderDocumentStatus($filePath) {
    $filePath = trim((string)$filePath);

    if (!empty($filePath) && $filePath !== 'NULL' && $filePath !== '[]') {
        $filePath = str_replace(['[', ']', '"', "'"], '', $filePath);
        $filePath = str_replace('\\/', '/', $filePath);
        $filePath = str_replace('\\', '/', $filePath);
        $filePath = str_replace('../', '', $filePath);
        $filePath = ltrim($filePath, '/');

        $viewerUrl = 'view-file.php?file=' . urlencode($filePath);

        return '<p><a href="' . htmlspecialchars($viewerUrl) . '" target="_blank" style="color: #10b981; font-weight: 600; text-decoration: none;">
                    <i class="fa-solid fa-circle-check"></i> View Document
                </a></p>';
    }
    
    return '<p style="color: #ef4444; font-weight: 500;">
                <i class="fa-solid fa-circle-xmark"></i> Not Attached
            </p>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>faculty record</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Using the exact same styling template base as student-view-record -->
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
    <h2>My Faculty Record</h2>

    <?php if(!empty($email) || !empty($contact_no)): ?>

    <!-- PROFILE LAYOUT CARD MATCHING STUDENT VIEW STYLE -->
    <div class="profile-card">
        <div class="profile-left">

            <form id="avatarForm" action="faculty-view-record.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="profile_pic" id="photoInput" hidden onchange="document.getElementById('avatarForm').submit()">
            </form>

            <div class="student-pic-container" onclick="document.getElementById('photoInput').click();" title="Click to change photo">
                <img 
                src="<?= (!empty($profile_pic) && file_exists('../' . $profile_pic)) ? '../' . htmlspecialchars($profile_pic) : '../assets/student.jpg'; ?>" 
                class="student-pic"
                alt="Faculty Photo"
                >
            </div>

            <div>
                <h3><?= htmlspecialchars($full_name); ?></h3>
                <p>ID Number: <?= htmlspecialchars($faculty_id); ?></p>
            </div>
        </div>
    </div>

    <!-- ACCORDION COMPONENT -->
    <div class="accordion">

        <!-- PANEL 1: ACCOUNT DETAILS -->
        <button class="accordion-btn">Profile Information</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>Employment Status</label>
                    <p><?= !empty($status) ? htmlspecialchars($status) : '---' ?></p>
                </div>
                <div class="field">
                    <label>Email Address</label>
                    <p><?= !empty($email) ? htmlspecialchars($email) : '---' ?></p>
                </div>
                <div class="field">
                    <label>Contact Number</label>
                    <p><?= !empty($contact_no) ? htmlspecialchars($contact_no) : '---' ?></p>
                </div>
            </div>
        </div>

        <!-- PANEL 2: PERSONAL & ACADEMIC DOCUMENTS -->
        <button class="accordion-btn">Personal & Academic Credentials</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>Curriculum Vitae (CV)</label>
                    <?= renderDocumentStatus($portfolio['cv']) ?>
                </div>
                <div class="field">
                    <label>Updated PRC License</label>
                    <?= renderDocumentStatus($portfolio['prc_license']) ?>
                </div>
                <div class="field">
                    <label>Transcript of Records (TOR)</label>
                    <?= renderDocumentStatus($portfolio['tor']) ?>
                </div>
                <div class="field">
                    <label>Diploma</label>
                    <?= renderDocumentStatus($portfolio['diploma']) ?>
                </div>
            </div>
        </div>

        <!-- PANEL 3: TRAININGS & PROFESSIONAL MEMBERSHIPS -->
        <button class="accordion-btn">Professional Associations & Trainings</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>Certificate of Professional Membership</label>
                    <?= renderDocumentStatus($portfolio['certificates_membership']) ?>
                </div>
                <div class="field">
                    <label>Seminars Attended (Regional)</label>
                    <?= renderDocumentStatus($portfolio['seminars_regional']) ?>
                </div>
                <div class="field">
                    <label>Seminars Attended (National)</label>
                    <?= renderDocumentStatus($portfolio['seminars_national']) ?>
                </div>
                <div class="field">
                    <label>Seminars Attended (International)</label>
                    <?= renderDocumentStatus($portfolio['seminars_international']) ?>
                </div>
            </div>
        </div>

        <!-- PANEL 4: RESEARCH & INSTRUCTIONAL MATERIALS -->
        <button class="accordion-btn">Research Works & Instructional Materials</button>
        <div class="accordion-content">
            <div class="info-grid">
                <div class="field">
                    <label>Certificate of Researchers</label>
                    <?= renderDocumentStatus($portfolio['research_cert']) ?>
                </div>
                <div class="field">
                    <label>Certificate as Research Presenter</label>
                    <?= renderDocumentStatus($portfolio['research_presenter']) ?>
                </div>
                <div class="field">
                    <label>Community Extension Documentation</label>
                    <?= renderDocumentStatus($portfolio['community_extension']) ?>
                </div>
                <div class="field">
                    <label>Syllabi</label>
                    <?= renderDocumentStatus($portfolio['syllabi']) ?>
                </div>
                <div class="field">
                    <label>Test Questionnaires</label>
                    <?= renderDocumentStatus($portfolio['test_questionnaires']) ?>
                </div>
                <div class="field">
                    <label>Table of Specifications (TOS)</label>
                    <?= renderDocumentStatus($portfolio['tos']) ?>
                </div>
            </div>
        </div>

    </div>

    <?php else: ?>
        <div class="card" style="text-align: center; padding: 40px; background: white; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px;">
            <p style="color: #6b7280; font-size: 1.1rem; margin-bottom: 20px;">No record found. <br> Please add your faculty portfolio records information first.</p>
            <a href="faculty-add-portfolio.php">Add FacultyPortfolio Record</a>
        </div>
    <?php endif; ?>

    <!-- BACK BUTTON AT BOTTOM -->
    <div class="back-container">
        <a href="faculty-dashboard.php" class="back-btn">
            <i class="fa-solid fa-arrow-left"></i> Back 
        </a>
    </div>

</div>

<!-- ACCORDION INTERACTION CONTROL SCRIPT -->
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