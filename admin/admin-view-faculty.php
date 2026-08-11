<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// Get faculty ID from URL
$faculty_no = $_GET['id'] ?? null;

if(!$faculty_no){
    header("Location: manage-faculty.php?error=Invalid faculty ID");
    exit();
}

// Fetch all profile details including portfolio uploaded fields from faculty_profile
$query = "SELECT u.student_no AS faculty_no, u.first_name, u.last_name, u.email, 
                 p.contact_no, p.status, p.cv, p.tor, p.diploma, p.prc_license, 
                 p.certificates_membership, p.seminars_regional, p.seminars_national, 
                 p.seminars_international, p.research_cert, p.research_presenter, 
                 p.community_extension, p.test_questionnaires, p.syllabi, p.tos
          FROM users u 
          LEFT JOIN faculty_profile p ON u.student_no = p.faculty_no 
          WHERE u.student_no = ? AND u.role = 'faculty'";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("SQL Error: " . $conn->error);
}

$stmt->bind_param("s", $faculty_no);
$stmt->execute();
$result = $stmt->get_result();
$faculty = $result->fetch_assoc();

if(!$faculty){
    header("Location: manage-faculty.php?error=Faculty record not found");
    exit();
}

// Helper function to render file links nicely if paths are stored (supports JSON arrays or string paths)
function renderFileLink($filePath) {
    if (empty($filePath)) {
        return '<span class="text-muted">Not Uploaded</span>';
    }
    
    // Check if it's a JSON array (multiple files)
    $decoded = json_decode($filePath, true);
    if (is_array($decoded)) {
        $links = [];
        foreach ($decoded as $path) {
            $filename = basename($path);
            $links[] = '<a href="' . htmlspecialchars($path) . '" target="_blank" class="file-link"><i class="fa-solid fa-file-arrow-down"></i> ' . htmlspecialchars($filename) . '</a>';
        }
        return implode('<br>', $links);
    } else {
        $filename = basename($filePath);
        return '<a href="' . htmlspecialchars($filePath) . '" target="_blank" class="file-link"><i class="fa-solid fa-file-arrow-down"></i> ' . htmlspecialchars($filename) . '</a>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="View Faculty Details - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>admin-view-faculty</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
    <link rel="stylesheet" href="../assets/css/admin-view-student.css">
    <style>
        .file-link {
            color: #0066cc;
            text-decoration: none;
            font-weight: 500;
            display: inline-block;
            margin-top: 4px;
        }
        .file-link:hover {
            text-decoration: underline;
        }
        .text-muted {
            color: #94a3b8;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="view-container">
    <div class="view-card">
        <div class="view-header">
            <a href="manage-faculty.php" class="close-btn">&times;</a>
        </div>
        
        <div class="view-body">
            <!-- Faculty Information -->
            <div class="view-section">
                <h3><i class="fa-solid fa-user"></i> Faculty Information</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>ID Number:</label>
                        <p><?= htmlspecialchars($faculty['faculty_no']) ?></p>
                    </div>
                    <div class="view-item">
                        <label>First Name:</label>
                        <p><?= htmlspecialchars($faculty['first_name'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Last Name:</label>
                        <p><?= htmlspecialchars($faculty['last_name'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <!-- Employment & Contact -->
            <div class="view-section">
                <h3><i class="fa-solid fa-briefcase"></i> Employment & Contact</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>Employment Status:</label>
                        <p><?= htmlspecialchars($faculty['status'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Email Address:</label>
                        <p><?= htmlspecialchars($faculty['email'] ?? 'N/A') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Contact Number:</label>
                        <p><?= htmlspecialchars($faculty['contact_no'] ?? 'N/A') ?></p>
                    </div>
                </div>
            </div>

            <!-- Professional Credentials & Uploaded Documents -->
            <div class="view-section">
                <h3><i class="fa-solid fa-folder-open"></i> Uploaded Portfolio Credentials</h3>
                <div class="view-grid">
                    <div class="view-item">
                        <label>Curriculum Vitae (CV):</label>
                        <p><?= renderFileLink($faculty['cv'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Transcript of Records (TOR):</label>
                        <p><?= renderFileLink($faculty['tor'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Diploma:</label>
                        <p><?= renderFileLink($faculty['diploma'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>PRC License:</label>
                        <p><?= renderFileLink($faculty['prc_license'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Certificates of Membership:</label>
                        <p><?= renderFileLink($faculty['certificates_membership'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Regional Seminars:</label>
                        <p><?= renderFileLink($faculty['seminars_regional'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>National Seminars:</label>
                        <p><?= renderFileLink($faculty['seminars_national'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>International Seminars:</label>
                        <p><?= renderFileLink($faculty['seminars_international'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Research Certification:</label>
                        <p><?= renderFileLink($faculty['research_cert'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Research Presenter Credentials:</label>
                        <p><?= renderFileLink($faculty['research_presenter'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Community Extension Records:</label>
                        <p><?= renderFileLink($faculty['community_extension'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Test Questionnaires:</label>
                        <p><?= renderFileLink($faculty['test_questionnaires'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Syllabi:</label>
                        <p><?= renderFileLink($faculty['syllabi'] ?? '') ?></p>
                    </div>
                    <div class="view-item">
                        <label>Table of Specifications (TOS):</label>
                        <p><?= renderFileLink($faculty['tos'] ?? '') ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="view-footer">
            <a href="manage-faculty.php" class="close-btn-full"><i class="fa-solid fa-arrow-left"></i> Back</a>
            <a href="admin-edit-faculty.php?id=<?= urlencode($faculty['faculty_no']) ?>" class="edit-btn"><i class="fa-solid fa-edit"></i> Edit</a>
        </div>
    </div>
</div>

</body>
</html>