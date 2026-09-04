<?php
session_start();
include("../config/db.php"); 

if(!isset($_SESSION['role']) || $_SESSION['role'] != "faculty"){
    header("Location: ../auth/login.php");
    exit();
}

$faculty_id = $_SESSION['student_no'] ?? $_SESSION['faculty_id'] ?? '';
$first_name = $_SESSION['first_name'] ?? 'Faculty';
$last_name  = $_SESSION['last_name'] ?? '';
$full_name  = trim($first_name . " " . $last_name);

// Check if faculty has already added records
$record_filled = false;
$stmt = $conn->prepare("SELECT id FROM faculty_profile WHERE faculty_no = ?");
if ($stmt) {
    $stmt->bind_param("s", $faculty_id);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $record_filled = true;
    }
    $stmt->close();
}

// Fetch announcements for dropdown
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
    <title>Faculty Record</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Shared Dashboard Layout CSS -->
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
</head>
<body>

<div class="dashboard-container">
    <!-- HEADER -->
    <?php include("../includes/header.php"); ?>

    <!-- LAYOUT -->
    <div class="dashboard-layout">
        <?php include("../includes/faculty-sidebar.php"); ?>
        
        <main class="main-content">
            <!-- Welcome Card -->
            <div class="card welcome-card">
                <h2>Faculty Record Management</h2>
                <p>View your faculty records.</p>
            </div>

            <!-- Status Card -->
            <div class="card status-card">
                <i class="fa-solid fa-folder-open status-icon"></i>
                <h3>Record Status: <strong><?= $record_filled ? 'Submitted' : 'No Records Added' ?></strong></h3>
                <p class="status-desc">
                    <?= $record_filled ? 'Your faculty records are up to date.' : 'View your faculty record' ?>
                </p>
                
                <?php if (!$record_filled): ?>
                    <button type="button" class="save-btn status-btn" onclick="openRecordModal()">
                        <i class="fa-solid fa-eye"></i> View My Faculty Personal Information
                    </button>
                <?php else: ?>
                    <button type="button" class="save-btn status-btn" onclick="openRecordModal()">
                        <i class="fa-solid fa-folder-open"></i> View My Faculty Information
                    </button>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- ================= ADD RECORD MODAL ================= -->
<?php include("faculty-record-modal.php"); ?>

<script src="../assets/js/script.js"></script>
<script>
    function openRecordModal() {
        document.getElementById('recordModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeRecordModal() {
        document.getElementById('recordModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // AJAX function to upload faculty profile photo without page reload
    function uploadProfilePhoto() {
        const fileInput = document.getElementById('photoInput');
        const file = fileInput.files[0];
        
        if (!file) return;

        const formElement = document.getElementById('photoUploadForm');
        const formData = new FormData(formElement);

        const imgElement = document.getElementById('facultyProfileImg');
        if (!imgElement) return;

        const originalSrc = imgElement.src;
        imgElement.style.opacity = '0.5';

        fetch('faculty-upload-photo.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                imgElement.src = data.new_image + '?t=' + new Date().getTime();
                imgElement.style.opacity = '1';
            } else {
                alert("Upload failed: " + data.message);
                imgElement.src = originalSrc;
                imgElement.style.opacity = '1';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred during upload.');
            imgElement.src = originalSrc;
            imgElement.style.opacity = '1';
        });
        
        fileInput.value = '';
    }
</script>
</body>
</html>