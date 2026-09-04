<?php
session_start();
include("../config/db.php"); 

// Protect page access - ensure user is logged in and is a faculty member
if(!isset($_SESSION['role']) || $_SESSION['role'] != "faculty"){
    header("Location: ../auth/login.php");
    exit();
}

$faculty_id = $_SESSION['student_no'] ?? $_SESSION['faculty_id'] ?? '';
$first_name = $_SESSION['first_name'] ?? 'Faculty';
$last_name  = $_SESSION['last_name'] ?? '';
$msg = "";

// --- FETCH REGISTRATION EMAIL & EXISTING VALUES ---
$registered_email = "";
$existing_contact = "";
$existing_status = "";
$info_filled = false;

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

// 2. Fetch any saved profile info
$profile_stmt = $conn->prepare("SELECT id, email, contact_no, status FROM faculty_profile WHERE faculty_no = ? LIMIT 1");
if ($profile_stmt) {
    $profile_stmt->bind_param("s", $faculty_id);
    $profile_stmt->execute();
    $profile_res = $profile_stmt->get_result();
    if ($profile_row = $profile_res->fetch_assoc()) {
        $info_filled = true; 
        if (empty($registered_email)) {
            $registered_email = $profile_row['email'];
        }
        $existing_contact = $profile_row['contact_no'];
        $existing_status = $profile_row['status'];
    }
    $profile_stmt->close();
}


// --- FORM SUBMISSION LOGIC ---
if (isset($_POST['submit_portfolio'])) {
    $form_email = $_POST['email'] ?? $registered_email;
    $form_contact = trim($_POST['contact_no']);
    $form_status = $_POST['status'] ?? '';

    $target_dir = "../uploads/portfolios/" . $faculty_id . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_fields = [
        'cv', 'tor', 'diploma', 'prc_license', 'certificates_membership', 
        'seminars_regional', 'seminars_national', 'seminars_international',
        'research_cert', 'research_presenter', 'community_extension', 
        'test_questionnaires', 'syllabi', 'tos'
    ];

    $allowed_extensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $uploaded_paths = [];
    $upload_error = false;

    foreach ($file_fields as $field) {
        if (isset($_FILES[$field])) {
            if (is_array($_FILES[$field]['name'])) {
                $file_count = count($_FILES[$field]['name']);
                $paths_arr = [];

                for ($i = 0; $i < $file_count; $i++) {
                    if ($_FILES[$field]['error'][$i] == 0) {
                        $file_tmp   = $_FILES[$field]['tmp_name'][$i];
                        $orig_name  = basename($_FILES[$field]["name"][$i]);
                        $file_ext   = strtolower(pathinfo($orig_name, PATHINFO_EXTENSION));

                        if (in_array($file_ext, $allowed_extensions)) {
                            $clean_filename = time() . "_" . $i . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $orig_name);
                            $target_file    = $target_dir . $clean_filename;
                            
                            if (move_uploaded_file($file_tmp, $target_file)) {
                                $paths_arr[] = $target_file;
                            } else {
                                $msg = "<p class='alert-msg system-error'>System failed to save file: $orig_name</p>";
                                $upload_error = true;
                                break 2;
                            }
                        } else {
                            $msg = "<p class='alert-msg validation-error'>Invalid file format for $orig_name. Allowed: PDF, DOC, DOCX, PNG, JPG.</p>";
                            $upload_error = true;
                            break 2;
                        }
                    }
                }
                $uploaded_paths[$field] = !empty($paths_arr) ? json_encode($paths_arr) : null;
            } else {
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

    if (!$upload_error) {
        if ($info_filled) {
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
            header("Location: faculty-personal-info.php"); 
            exit();
        } else {
            $msg = "<p class='alert-msg database-error'>Database saving failed.</p>";
        }
        $stmt->close();
    }
}

// --- FETCH ANNOUNCEMENTS ---
$announcements = [];
$conn->query("SET time_zone = '+08:00'");
$query = "SELECT title, message, created_at, 
          (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
          FROM announcements 
          WHERE status = 'published' 
          AND (target_audience = 'all' OR target_audience = 'faculty' OR (target_audience = 'specific_user' AND target_user_id = ?))
          ORDER BY created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $announcements[] = $row;
    }
}
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Information</title>
    <link class="img-cdn" rel="shortcut icon" href="../assets/logo.png" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Shared Dashboard Layout CSS -->
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    
    <!-- Externalized Stylesheet for Faculty Personal Info & Modal Layout -->
    <link rel="stylesheet" href="../assets/css/faculty-personal-info.css">
    
    <script>
        function confirmSubmission() {
            return confirm("Are you sure all information is correct and you want to submit your portfolio?");
        }

        function confirmCancel(event) {
            if (!confirm("Are you sure you want to cancel? Any unsaved document attachments will be lost.")) {
                if(event) event.preventDefault(); 
                return false;
            }
            closeModal();
            return true;
        }

        function openModal() {
            document.getElementById('portfolioModalOverlay').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeModal() {
            document.getElementById('portfolioModalOverlay').style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    </script>
</head>
<body>

<div class="dashboard-container">
    <!-- HEADER -->
    <?php include("../includes/header.php"); ?>

    <!-- LAYOUT -->
    <div class="dashboard-layout">
        <?php include("../includes/faculty-sidebar.php"); ?>
        
        <main class="main-content">
            <?php if(isset($_SESSION['portfolio_success_msg'])): ?>
                <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 24px; border: 1px solid #bbf7d0;">
                    <i class="fa-solid fa-circle-check"></i> <?= $_SESSION['portfolio_success_msg'] ?>
                </div>
                <?php unset($_SESSION['portfolio_success_msg']); ?>
            <?php endif; ?>

            <!-- Welcome Card -->
            <div class="card welcome-card">
                <h2>Faculty Portfolio Management</h2>
                <p>Manage and complete your professional credentials and institutional document records.</p>
            </div>

            <!-- Status Card -->
            <div class="card status-card">
                <i class="fa-solid fa-file-shield status-icon"></i>
                <h3>Portfolio Status: <strong><?= $info_filled ? 'Completed' : 'Pending Portfolio' ?></strong></h3>
                <p class="status-desc">
                    <?= $info_filled ? 'Your faculty portfolio documents have been saved successfully.' : 'Please provide your required professional records, licenses, and academic files.' ?>
                </p>
                
                <?php if (!$info_filled): ?>
                    <button type="button" class="save-btn status-btn" onclick="openModal()">
                        <i class="fa-solid fa-plus"></i> Add Portfolio Information
                    </button>
                <?php else: ?>
                    <button type="button" class="save-btn status-btn" onclick="openModal()">
                        <i class="fa-solid fa-pen-to-square"></i> Update Portfolio
                    </button>
                    <p class="success-text"><i class="fa-solid fa-circle-check"></i> Portfolio records submitted.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- ================= INCLUDE MODAL ================= -->
<?php include("faculty-personal-info-modal.php"); ?>

<script src="../assets/js/script.js"></script>
</body>
</html>