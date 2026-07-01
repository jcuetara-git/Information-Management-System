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

// Fetch faculty profile details (Removed first_name and last_name from here since they live in the session/user table)
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

        return '<a href="' . htmlspecialchars($viewerUrl) . '" target="_blank" class="doc-link-attached">
                    <i class="fa-solid fa-circle-check"></i> View Document
                </a>';
    }
    
    return '<span class="doc-not-attached">
                <i class="fa-solid fa-circle-xmark"></i> Not Attached
            </span>';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>faculty-view-record</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/faculty-dashboard.css?v=1.5">
    <link rel="stylesheet" href="../assets/css/faculty-view-record.css?v=2.0">
</head>
<body>

    <header class="logo-section">
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
            <a href="../auth/logout.php" title="Logout">
                <i class="fa-solid fa-sign-out-alt"></i>
                <span class="logout-text">Logout</span>
            </a>
        </div>
    </header>

    <main class="record-container">
        
        <a href="faculty-dashboard.php" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
        </a>

       <!-- Faculty Header Card Matching Student Style Layout -->
        <div class="profile-card">
            <div class="profile-left">
                <div class="student-pic-container">
                    <form id="avatarForm" action="faculty-view-record.php" method="POST" enctype="multipart/form-data">
                        <label for="profile_pic_input" style="cursor: pointer;">
                            <?php if (!empty($profile_pic) && file_exists("../" . $profile_pic)): ?>
                                <img src="../<?= htmlspecialchars($profile_pic) ?>" alt="Profile Picture" class="student-pic">
                            <?php else: ?>
                                <div class="student-pic" style="display: flex; align-items: center; justify-content: center; background: #e2e8f0; font-size: 2rem; color: #a0a0a0; height: 100%; border-radius: 50%;">
                                    <i class="fa-solid fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </label>
                        <input type="file" name="profile_pic" id="profile_pic_input" accept="image/*" onchange="document.getElementById('avatarForm').submit();" style="display: none;">
                    </form>
                </div>
                <div class="header-details">
                    <h3><?= htmlspecialchars($full_name) ?></h3>
                    <p>ID Number: <span><?= htmlspecialchars($faculty_id) ?></span></p>
                </div>
            </div>
        </div>

        <!-- Portfolio Documentation Information Details -->
        <div class="info-panel">
            <h2 class="panel-title">
                <i class="fa-solid fa-id-card"></i> Portfolio Information
            </h2>

            <div class="info-grid">
                
                <div class="info-item">
                    <label>Status</label>
                    <p><?= !empty($status) ? htmlspecialchars($status) : '---' ?></p>
                </div>

                <div class="info-item">
                    <label>Email Address</label>
                    <p><?= !empty($email) ? htmlspecialchars($email) : '---' ?></p>
                </div>

                <div class="info-item">
                    <label>Contact Number</label>
                    <p><?= !empty($contact_no) ? htmlspecialchars($contact_no) : '---' ?></p>
                </div>

                <div class="spacer-item"></div>

                <!-- PERSONAL RECORDS SECTION -->
                <div class="section-divider">Personal Records</div>

                <div class="info-item">
                    <label>Curriculum Vitae (CV)</label>
                    <?= renderDocumentStatus($portfolio['cv']) ?>
                </div>

                <div class="info-item">
                    <label>Updated PRC License</label>
                    <?= renderDocumentStatus($portfolio['prc_license']) ?>
                </div>

                <!-- ACADEMIC CREDENTIALS SECTION -->
                <div class="section-divider">Academic Credentials</div>

                <div class="info-item">
                    <label>Transcript of Records (TOR)</label>
                    <?= renderDocumentStatus($portfolio['tor']) ?>
                </div>

                <div class="info-item">
                    <label>Diploma</label>
                    <?= renderDocumentStatus($portfolio['diploma']) ?>
                </div>

                <!-- PROFESSIONAL ASSOCIATIONS SECTION -->
                <div class="section-divider">Professional Associations & Trainings</div>

                <div class="info-item">
                    <label>Certificate of Professional Membership</label>
                    <?= renderDocumentStatus($portfolio['certificates_membership']) ?>
                </div>

                <div class="info-item">
                    <label>Seminars & Trainings Attended (Regional)</label>
                    <?= renderDocumentStatus($portfolio['seminars_regional']) ?>
                </div>

                <div class="info-item">
                    <label>Seminars & Trainings Attended (National)</label>
                    <?= renderDocumentStatus($portfolio['seminars_national']) ?>
                </div>

                <div class="info-item">
                    <label>Seminars & Trainings Attended (International)</label>
                    <?= renderDocumentStatus($portfolio['seminars_international']) ?>
                </div>

                <!-- RESEARCH WORKS SECTION -->
                <div class="section-divider">Research Works</div>

                <div class="info-item">
                    <label>Certificate of Researchers</label>
                    <?= renderDocumentStatus($portfolio['research_cert']) ?>
                </div>

                <div class="info-item">
                    <label>Certificate as Research Presenter</label>
                    <?= renderDocumentStatus($portfolio['research_presenter']) ?>
                </div>

                <!-- INSTRUCTIONAL MATERIALS SECTION -->
                <div class="section-divider">Instructional & Extension Materials</div>

                <div class="info-item">
                    <label>Community Extension Documentation</label>
                    <?= renderDocumentStatus($portfolio['community_extension']) ?>
                </div>

                <div class="info-item">
                    <label>Syllabi</label>
                    <?= renderDocumentStatus($portfolio['syllabi']) ?>
                </div>

                <div class="info-item">
                    <label>Test Questionnaires</label>
                    <?= renderDocumentStatus($portfolio['test_questionnaires']) ?>
                </div>

                <div class="info-item">
                    <label>Table of Specifications (TOS)</label>
                    <?= renderDocumentStatus($portfolio['tos']) ?>
                </div>

            </div>
        </div>

    </main>

</body>
</html>