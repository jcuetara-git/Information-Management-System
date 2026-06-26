<?php
session_start();
include("../config/db.php");

// Protect page access - ensure user is logged in and is a faculty member
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}

$first_name = $_SESSION['first_name'] ?? 'Faculty';
$faculty_id = $_SESSION['student_no'];

// Initialize profile data variables
$email = "";
$contact_no = "";
$status = "";

// Initialize portfolio document paths
$portfolio = [
    'cv' => null, 'tor' => null, 'diploma' => null, 'prc_license' => null,
    'certificates_membership' => null, 'seminars_regional' => null, 
    'seminars_national' => null, 'seminars_international' => null,
    'research_cert' => null, 'research_presenter' => null, 
    'community_extension' => null, 'test_questionnaires' => null, 
    'syllabi' => null, 'tos' => null
];

// Fetch faculty profile details along with document file paths
$stmt = $conn->prepare("SELECT email, contact_no, status, cv, tor, diploma, prc_license, certificates_membership, seminars_regional, seminars_national, seminars_international, research_cert, research_presenter, community_extension, test_questionnaires, syllabi, tos FROM faculty_profile WHERE faculty_no = ? LIMIT 1");
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$res = $stmt->get_result();

if ($row = $res->fetch_assoc()) {
    $email = $row['email'];
    $contact_no = $row['contact_no'];
    $status = $row['status'];
    
    // Map paths from database
    foreach ($portfolio as $key => $val) {
        $portfolio[$key] = $row[$key];
    }
}
$stmt->close();

/**
 * Helper function to output a dedicated target view link cleanly without triggering immediate downloads
 */
function renderDocumentStatus($filePath) {
    if (!empty($filePath) && file_exists($filePath)) {
        return '<a href="' . htmlspecialchars($filePath) . '" target="_blank" class="doc-link-attached">
                    <i class="fa-solid fa-circle-check"></i> View Document
                </a>';
    } else {
        return '<span class="doc-not-attached">
                    <i class="fa-solid fa-circle-xmark"></i> Not Attached
                </span>';
    }
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
    <link rel="stylesheet" href="../assets/css/faculty-view-record.css?v=1.0">
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

        <!-- Faculty Header Card -->
        <div class="profile-card-header">
            <div class="avatar-circle">
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="header-details">
                <h1><?= htmlspecialchars($first_name) ?></h1>
                <p>ID Number: <span><?= htmlspecialchars($faculty_id) ?></span></p>
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