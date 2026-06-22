<?php
session_start();
include("../config/db.php");

// Protect page access - ensure user is logged in and is a faculty member
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}

$faculty_id = $_SESSION['student_no'];
$msg = "";

if (isset($_POST['submit_portfolio'])) {
    // 1. Establish isolated storage directory paths per instructor
    $target_dir = "../uploads/portfolios/" . $faculty_id . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // 2. Map structural form elements from your upload catalog
    $file_fields = [
        'cv', 'tor', 'diploma', 'prc_license', 'certificates_membership', 
        'seminars_regional', 'seminars_national', 'seminars_international',
        'research_cert', 'research_presenter', 'community_extension', 
        'test_questionnaires', 'syllabi', 'tos'
    ];

    // Allowed extensions for academic documentation tracking
    $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $uploaded_paths = [];
    $upload_error = false;

    // 3. Process structural file streams iteratively
    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file_tmp   = $_FILES[$field]['tmp_name'];
            $orig_name  = basename($_FILES[$field]["name"]);
            $file_ext   = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

            // Verify safe MIME extensions
            if (in_array($file_ext, $allowed_extensions)) {
                // Sanitize file names to prevent system exploits
                $clean_filename = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $orig_name);
                $target_file    = $target_dir . $clean_filename;
                
                if (move_uploaded_file($file_tmp, $target_file)) {
                    $uploaded_paths[$field] = $target_file;
                } else {
                    $msg = "<p class='alert-msg system-error'>System failed to save file: $orig_name</p>";
                    $upload_error = true;
                    break;
                }
            } else {
                $msg = "<p class='alert-msg validation-error'>Invalid file format for $orig_name. Allowed: PDF, DOC, DOCX, PNG, JPG.</p>";
                $upload_error = true;
                break;
            }
        } else {
            // Keep current value if empty or optional
            $uploaded_paths[$field] = null;
        }
    }

    // 4. Update data store mapping upon successful file parsing
    if (!$upload_error) {
        // Evaluate history mapping for profile record status
        $check = $conn->prepare("SELECT id FROM faculty_profile WHERE faculty_no = ?");
        $check->bind_param("s", $faculty_id);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            // Update existing record, conditionally checking if a new file path was supplied
            $query = "UPDATE faculty_profile SET 
                        cv = COALESCE(?, cv), tor = COALESCE(?, tor), diploma = COALESCE(?, diploma), 
                        prc_license = COALESCE(?, prc_license), certificates_membership = COALESCE(?, certificates_membership), 
                        seminars_regional = COALESCE(?, seminars_regional), seminars_national = COALESCE(?, seminars_national), 
                        seminars_international = COALESCE(?, seminars_international), research_cert = COALESCE(?, research_cert), 
                        research_presenter = COALESCE(?, research_presenter), community_extension = COALESCE(?, community_extension), 
                        test_questionnaires = COALESCE(?, test_questionnaires), syllabi = COALESCE(?, syllabi), tos = COALESCE(?, tos) 
                      WHERE faculty_no = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssssssssss", 
                $uploaded_paths['cv'], $uploaded_paths['tor'], $uploaded_paths['diploma'], $uploaded_paths['prc_license'],
                $uploaded_paths['certificates_membership'], $uploaded_paths['seminars_regional'], $uploaded_paths['seminars_national'],
                $uploaded_paths['seminars_international'], $uploaded_paths['research_cert'], $uploaded_paths['research_presenter'],
                $uploaded_paths['community_extension'], $uploaded_paths['test_questionnaires'], $uploaded_paths['syllabi'], $uploaded_paths['tos'],
                $faculty_id
            );
        } else {
            // Write pristine layout index row
            $query = "INSERT INTO faculty_profile (faculty_no, cv, tor, diploma, prc_license, certificates_membership, seminars_regional, seminars_national, seminars_international, research_cert, research_presenter, community_extension, test_questionnaires, syllabi, tos) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssssssssssss", 
                $faculty_id, $uploaded_paths['cv'], $uploaded_paths['tor'], $uploaded_paths['diploma'], $uploaded_paths['prc_license'],
                $uploaded_paths['certificates_membership'], $uploaded_paths['seminars_regional'], $uploaded_paths['seminars_national'],
                $uploaded_paths['seminars_international'], $uploaded_paths['research_cert'], $uploaded_paths['research_presenter'],
                $uploaded_paths['community_extension'], $uploaded_paths['test_questionnaires'], $uploaded_paths['syllabi'], $uploaded_paths['tos']
            );
        }

        if ($stmt->execute()) {
            header("Location: faculty-dashboard.php");
            exit();
        } else {
            $msg = "<p class='alert-msg database-error'>Database saving failed.</p>";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>add-faculty-portfolio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/faculty-dashboard.css?v=1.8">
</head>
<body>

    <header class="logo-section">
        <div class="logo-left">
            <div class="logo-circle"><img src="../assets/logo.png" alt="Logo"></div>
            <div class="logo-text">
                <h2>College of Criminal Justice</h2>
                <p>Center of Development in Criminology</p>
            </div>
        </div>
        <div class="profile-menu">
            <a href="../auth/logout.php" title="Logout Account">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="logout-text">Logout</span>
            </a>
        </div>
    </header>

    <main>
        <div class="form-container">
            
            <div class="form-header-container">
                <div class="form-header-title">
                    <h1>Upload Faculty Portfolio</h1>
                </div>
                <a href="faculty-dashboard.php" class="close-form-btn" title="Close Form">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
            
            <p class="form-subtitle">Please select and upload your official credentials and academic file work here.</p>
            
            <?= $msg ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="form-grid">
                    
                    <div class="section-title"><span>Personal Records</span></div>
                    <div class="form-group">
                        <label>Curriculum Vitae (CV) <span class="required-asterisk">*</span></label>
                        <input type="file" name="cv" required>
                    </div>
                    <div class="form-group">
                        <label>Updated PRC License <span class="required-asterisk">*</span></label>
                        <input type="file" name="prc_license" required>
                    </div>

                    <div class="section-title"><span>Academic Credentials</span></div>
                    <div class="form-group">
                        <label>Transcript of Records (TOR) <span class="required-asterisk">*</span></label>
                        <input type="file" name="tor" required>
                    </div>
                    <div class="form-group">
                        <label>Diploma <span class="required-asterisk">*</span></label>
                        <input type="file" name="diploma" required>
                    </div>

                    <div class="section-title"><span>Professional Associations & Trainings</span></div>
                    <div class="form-group">
                        <label>Certificate of Professional Membership</label>
                        <input type="file" name="certificates_membership">
                    </div>
                    <div class="form-group">
                        <label>Seminars & Trainings Attended (Regional)</label>
                        <input type="file" name="seminars_regional">
                    </div>
                    <div class="form-group">
                        <label>Seminars & Trainings Attended (National)</label>
                        <input type="file" name="seminars_national">
                    </div>
                    <div class="form-group">
                        <label>Seminars & Trainings Attended (International)</label>
                        <input type="file" name="seminars_international">
                    </div>

                    <div class="section-title"><span>Research Works</span></div>
                    <div class="form-group">
                        <label>Certificate of Researchers</label>
                        <input type="file" name="research_cert">
                    </div>
                    <div class="form-group">
                        <label>Certificate as Research Presenter</label>
                        <input type="file" name="research_presenter">
                    </div>

                    <div class="section-title"><span>Instructional & Extension Materials</span></div>
                    <div class="form-group">
                        <label>Community Extension Documentation</label>
                        <input type="file" name="community_extension">
                    </div>
                    <div class="form-group">
                        <label>Syllabi</label>
                        <input type="file" name="syllabi">
                    </div>
                    <div class="form-group">
                        <label>Test Questionnaires</label>
                        <input type="file" name="test_questionnaires">
                    </div>
                    <div class="form-group">
                        <label>Table of Specifications (TOS)</label>
                        <input type="file" name="tos">
                    </div>

                </div>

                <div class="flex-buttons-row">
                    <a href="faculty-dashboard.php" class="cancel-action-btn">Cancel</a>
                    <button type="submit" name="submit_portfolio" class="view-btn">Submit Portfolio Documents</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>