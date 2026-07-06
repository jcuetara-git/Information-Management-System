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

// --- AUTOMATICALLY FETCH REGISTRATION EMAIL & EXISTING VALUES ---
$registered_email = "";
$existing_contact = "";
$existing_status = "";

// 1. Fetch Email from registration records
$user_stmt = $conn->prepare("SELECT email FROM users WHERE student_no = ? LIMIT 1");
if ($user_stmt) {
    $user_stmt->bind_param("s", $faculty_id);
    $user_stmt->execute();
    $user_res = $user_stmt->get_result();
    if ($user_row = $user_res->fetch_assoc()) {
        $registered_email = $user_row['email'];
    }
    $user_stmt->close();
}

// 2. Fetch any saved profile info if they are updating it
$profile_stmt = $conn->prepare("SELECT email, contact_no, status FROM faculty_profile WHERE faculty_no = ? LIMIT 1");
if ($profile_stmt) {
    $profile_stmt->bind_param("s", $faculty_id);
    $profile_stmt->execute();
    $profile_res = $profile_stmt->get_result();
    if ($profile_row = $profile_res->fetch_assoc()) {
        if (empty($registered_email)) {
            $registered_email = $profile_row['email'];
        }
        $existing_contact = $profile_row['contact_no'];
        $existing_status = $profile_row['status'];
    }
    $profile_stmt->close();
}


if (isset($_POST['submit_portfolio'])) {
    // Collect new text field and dropdown inputs
    $form_email = $_POST['email'] ?? $registered_email;
    $form_contact = trim($_POST['contact_no']);
    $form_status = $_POST['status'] ?? '';

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

    // 3. Process structural file streams iteratively (supporting 2 or more uploads per field)
    foreach ($file_fields as $field) {
        if (isset($_FILES[$field])) {
            // Check if it's an array of multiple files
            if (is_array($_FILES[$field]['name'])) {
                $file_count = count($_FILES[$field]['name']);
                $paths_arr = [];

                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES[$field]['error'][$i] == 0) {
                        $file_tmp   = $_FILES[$field]['tmp_name'][$i];
                        $orig_name  = basename($_FILES[$field]["name"][$i]);
                        $file_ext   = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

                        if (in_array($file_ext, $allowed_extensions)) {
                            // Unique filename parsing per index loop step
                            $clean_filename = time() . "_" . $i . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $orig_name);
                            $target_file    = $target_dir . $clean_filename;
                            
                            if (move_uploaded_file($file_tmp, $target_file)) {
                                $paths_arr[] = $target_file;
                            } else {
                                $msg = "<p class='alert-msg system-error'>System failed to save file: $orig_name</p>";
                                $upload_error = true;
                                break 2; // Break out of file counter iteration loop cleanly
                            }
                        } else {
                            $msg = "<p class='alert-msg validation-error'>Invalid file format for $orig_name. Allowed: PDF, DOC, DOCX, PNG, JPG.</p>";
                            $upload_error = true;
                            break 2;
                        }
                    }
                }
                // Save list of multiple file paths as a JSON string to fit nicely into database text column structure
                $uploaded_paths[$field] = !empty($paths_arr) ? json_encode($paths_arr) : null;

            } else {
                // Fallback standard single file stream handling
                if ($_FILES[$field]['error'] == 0) {
                    $file_tmp   = $_FILES[$field]['tmp_name'];
                    $orig_name  = basename($_FILES[$field]["name"]);
                    $file_ext   = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

                    if (in_array($file_ext, $allowed_extensions)) {
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
                    $uploaded_paths[$field] = null;
                }
            }
        }
    }

    // 4. Update database tables upon validation check completion
    if (!$upload_error) {
        $check = $conn->prepare("SELECT id FROM faculty_profile WHERE faculty_no = ?");
        $check->bind_param("s", $faculty_id);
        $check->execute();
        $res = $check->get_result();

        if ($res->num_rows > 0) {
            // Update existing record
            $query = "UPDATE faculty_profile SET 
                        email = ?, contact_no = ?, status = ?,
                        cv = COALESCE(?, cv), tor = COALESCE(?, tor), diploma = COALESCE(?, diploma), 
                        prc_license = COALESCE(?, prc_license), certificates_membership = COALESCE(?, certificates_membership), 
                        seminars_regional = COALESCE(?, seminars_regional), seminars_national = COALESCE(?, seminars_national), 
                        seminars_international = COALESCE(?, seminars_international), research_cert = COALESCE(?, research_cert), 
                        research_presenter = COALESCE(?, research_presenter), community_extension = COALESCE(?, community_extension), 
                        test_questionnaires = COALESCE(?, test_questionnaires), syllabi = COALESCE(?, syllabi), tos = COALESCE(?, tos) 
                      WHERE faculty_no = ?";
            $stmt = $conn->prepare($query);
            
            $stmt->bind_param("ssssssssssssssssss", 
                $form_email, $form_contact, $form_status,
                $uploaded_paths['cv'], $uploaded_paths['tor'], $uploaded_paths['diploma'], $uploaded_paths['prc_license'],
                $uploaded_paths['certificates_membership'], $uploaded_paths['seminars_regional'], $uploaded_paths['seminars_national'],
                $uploaded_paths['seminars_international'], $uploaded_paths['research_cert'], $uploaded_paths['research_presenter'],
                $uploaded_paths['community_extension'], $uploaded_paths['test_questionnaires'], $uploaded_paths['syllabi'], $uploaded_paths['tos'],
                $faculty_id
            );
        } else {
            // Insert fresh record
            $query = "INSERT INTO faculty_profile (faculty_no, email, contact_no, status, cv, tor, diploma, prc_license, certificates_membership, seminars_regional, seminars_national, seminars_international, research_cert, research_presenter, community_extension, test_questionnaires, syllabi, tos) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssssssssssssssss", 
                $faculty_id, $form_email, $form_contact, $form_status,
                $uploaded_paths['cv'], $uploaded_paths['tor'], $uploaded_paths['diploma'], $uploaded_paths['prc_license'],
                $uploaded_paths['certificates_membership'], $uploaded_paths['seminars_regional'], $uploaded_paths['seminars_national'],
                $uploaded_paths['seminars_international'], $uploaded_paths['research_cert'], $uploaded_paths['research_presenter'],
                $uploaded_paths['community_extension'], $uploaded_paths['test_questionnaires'], $uploaded_paths['syllabi'], $uploaded_paths['tos']
            );
        }

        if ($stmt->execute()) {
            $_SESSION['portfolio_success_msg'] = "Faculty Portfolio Successfully Submitted and Updated!";
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
    <link class="img-cdn" rel="shortcut icon" href="../assets/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/faculty-dashboard.css?v=1.8">
    <link rel="stylesheet" href="../assets/css/faculty-add-portfolio.css?v=1.0">
    <script>
        function confirmSubmission() {
            return confirm("Are you sure all information is correct and you want to submit your portfolio?");
        }

        function confirmCancel(event) {
            if (!confirm("Are you sure you want to cancel? Any unsaved document attachments will be lost.")) {
                event.preventDefault(); 
                return false;
            }
            return true;
        }
    </script>
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
            <a href="../auth/logout.php" title="Logout Account" onclick="return confirm('Are you sure you want to logout? Any unsaved form progress will be lost.');">
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
                <a href="faculty-dashboard.php" class="close-btn" title="Close Form" onclick="return confirmCancel(event);">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
            
            <!-- Clear instructions showing user they can pick 2 or more files -->
            <p class="form-subtitle">Please select and upload your official credentials and academic file work here. <span class="highlight-subtitle">You can select 2 or more files at the same time for any field below by holding down Ctrl or Cmd while picking files.</span></p>
            
            <?= $msg ?>

            <form method="POST" enctype="multipart/form-data" onsubmit="return confirmSubmission();">
                <div class="form-grid">
                    
                    <div class="section-title"><span>Contact & Status Information</span></div>
                    
                    <div class="form-group">
                        <label>Registered Email Address</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($registered_email) ?>" readonly title="Managed by registration details">
                    </div>
                    
                    <div class="form-group">
                        <label>Contact Number <span class="required-asterisk">*</span></label>
                        <input type="text" name="contact_no" value="<?= htmlspecialchars($existing_contact) ?>" placeholder="e.g. +63 912 345 6789" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Status <span class="required-asterisk">*</span></label>
                        <select name="status" required>
                            <option value="" disabled <?= empty($existing_status) ? 'selected' : '' ?>>Select your professional status</option>
                            <option value="Full-time Regular" <?= $existing_status === 'Full-time Regular' ? 'selected' : '' ?>>Full-time Regular</option>
                            <option value="Full-time Probationary" <?= $existing_status === 'Full-time Probationary' ? 'selected' : '' ?>>Full-time Probationary</option>
                            <option value="Part-time Lawyers" <?= $existing_status === 'Part-time Lawyers' ? 'selected' : '' ?>>Part-time Lawyer</option>
                            <option value="Part-time Instructor" <?= $existing_status === 'Part-time Instructor' ? 'selected' : '' ?>>Part-time Instructor</option>
                        </select>
                    </div>

                    <div class="form-group hidden-spacer"></div>

                    <div class="section-title"><span>Personal Records</span></div>
                    <div class="form-group">
                        <label>Curriculum Vitae (CV) <span class="required-asterisk">*</span><span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="cv[]" multiple <?= empty($existing_contact) ? 'required' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label>Updated PRC License <span class="required-asterisk">*</span><span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="prc_license[]" multiple <?= empty($existing_contact) ? 'required' : '' ?>>
                    </div>

                    <div class="section-title"><span>Academic Credentials</span></div>
                    <div class="form-group">
                        <label>Transcript of Records (TOR) <span class="required-asterisk">*</span><span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="tor[]" multiple <?= empty($existing_contact) ? 'required' : '' ?>>
                    </div>
                    <div class="form-group">
                        <label>Diploma <span class="required-asterisk">*</span><span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="diploma[]" multiple <?= empty($existing_contact) ? 'required' : '' ?>>
                    </div>

                    <div class="section-title"><span>Professional Associations & Trainings</span></div>
                    <div class="form-group">
                        <label>Certificate of Professional Membership <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="certificates_membership[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Seminars & Trainings Attended (Regional) <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="seminars_regional[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Seminars & Trainings Attended (National) <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="seminars_national[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Seminars & Trainings Attended (International) <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="seminars_international[]" multiple>
                    </div>

                    <div class="section-title"><span>Research Works</span></div>
                    <div class="form-group">
                        <label>Certificate of Researchers <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="research_cert[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Certificate as Research Presenter <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="research_presenter[]" multiple>
                    </div>

                    <div class="section-title"><span>Instructional & Extension Materials</span></div>
                    <div class="form-group">
                        <label>Community Extension Documentation <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="community_extension[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Syllabi <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="syllabi[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Test Questionnaires <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="test_questionnaires[]" multiple>
                    </div>
                    <div class="form-group">
                        <label>Table of Specifications (TOS) <span class="optional-hint">(2 or more files allowed)</span></label>
                        <input type="file" name="tos[]" multiple>
                    </div>

                </div>

                <div class="flex-buttons-row">
                    <a href="faculty-dashboard.php" class="cancel-action-btn" onclick="return confirmCancel(event);">Cancel</a>
                    <button type="submit" name="submit_portfolio" class="view-btn"><i class="fa-solid fa-file-arrow-up"></i> Submit Portfolio Documents</button>
                </div>
            </form>
        </div>
    </main>

</body>
</html>