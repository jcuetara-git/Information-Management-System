<?php
session_start();
include("../config/db.php");

// Protect page access - ensure user is logged in and is a faculty member
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}

$faculty_id = $_SESSION['student_no'] ?? $_SESSION['faculty_id'] ?? '';

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

// Fetch announcements for dropdown
$announcements = [];
$conn->query("SET time_zone = '+08:00'");
$queryNotif = "SELECT title, message, created_at, 
               (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
               FROM announcements 
               WHERE status = 'published' 
               AND (target_audience = 'all' OR target_audience = 'faculty' OR (target_audience = 'specific_user' AND target_user_id = ?))
               ORDER BY created_at DESC LIMIT 10";

$stmtNotif = $conn->prepare($queryNotif);
$stmtNotif->bind_param("s", $faculty_id);
$stmtNotif->execute();
$resultNotif = $stmtNotif->get_result();

if ($resultNotif && $resultNotif->num_rows > 0) {
    while ($rowNotif = $resultNotif->fetch_assoc()) {
        $announcements[] = $rowNotif;
    }
}
$stmtNotif->close();

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
    <title>Faculty Record</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    <link rel="stylesheet" href="../assets/css/student-view-record.css">
</head>

<body>

<div class="dashboard-container" style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    <!-- HEADER -->
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

        <div class="header-right">
            <!-- NOTIFICATION DROPDOWN -->
            <div class="notification-container">
                <button class="notification-btn" id="notifBtn">
                    <i class="fa-solid fa-bell"></i><span class="notif-badge" id="notifBadge" <?= count($announcements) == 0 ? 'style="display:none;"' : '' ?>><?= count($announcements); ?></span>
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
                            <h4><?= htmlspecialchars($full_name); ?></h4>
                            <p>Faculty Member</p>
                        </div>
                    </div>
                    <a href="../auth/logout.php" class="profile-logout" id="logoutBtn" style="cursor: pointer; display: block; text-decoration: none; color: inherit;">
                        <i class="fa-solid fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container" style="width: 100%; padding: 20px 0; box-sizing: border-box;">
        <h2 style="margin-bottom: 20px; color: #1e293b;">My Faculty Record</h2>

        <?php if(!empty($email) || !empty($contact_no)): ?>

        <!-- PROFILE LAYOUT CARD -->
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
                <a href="faculty-add-portfolio.php" style="display: inline-block; background: #f4b42c; color: black; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-size: 14px;">Add Faculty Portfolio Record</a>
            </div>
        <?php endif; ?>

        <!-- BACK BUTTON AT BOTTOM -->
        <div class="back-container" style="margin-top: 30px;">
            <a href="faculty-dashboard.php" class="back-btn" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; background: #f1f5f9; color: #1e293b; padding: 10px 18px; border-radius: 8px; font-weight: 500;">
                <i class="fa-solid fa-arrow-left"></i> Back 
            </a>
        </div>

    </div>

    <div style="text-align: center; padding: 30px 0 10px 0; color: #999; font-size: 13px;">
        ©2026 College of Criminal Justice | Version 1.1
    </div>
</div>

<!-- INTERACTION CONTROL SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const profileBtn = document.getElementById('profileBtn');
    const profileDropdown = document.getElementById('profileDropdown');
    const notifBadge = document.getElementById('notifBadge');
    const logoutBtn = document.getElementById('logoutBtn');

    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            if (profileDropdown) profileDropdown.classList.remove('active');
        });
    }

    if (notifBtn) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notifDropdown.classList.toggle('active');
            if (profileDropdown) profileDropdown.classList.remove('active');

            if (notifBadge && notifDropdown.classList.contains('active')) {
                notifBadge.style.display = 'none';
            }
        });
    }

    if (profileBtn) {
        profileBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            profileDropdown.classList.toggle('active');
            if (notifDropdown) notifDropdown.classList.remove('active');
        });
    }

    document.addEventListener('click', function(event) {
        if (notifDropdown && !notifBtn.contains(event.target) && !notifDropdown.contains(event.target)) {
            notifDropdown.classList.remove('active');
        }
        if (profileDropdown && !profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
            profileDropdown.classList.remove('active');
        }
    });

    // Accordion Script
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