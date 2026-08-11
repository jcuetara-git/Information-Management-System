<?php
include("../config/auth.php");
include("../config/db.php"); 

if(!isset($_SESSION['role']) || $_SESSION['role'] != "student"){
    header("Location: ../auth/login.php");
    exit();
}

$student_no = $_SESSION['student_no'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';
$last_name  = $_SESSION['last_name'] ?? '';

// Check if student has already added records
$record_filled = false;
$stmt = $conn->prepare("SELECT id FROM student_profile WHERE student_no = ?");
if ($stmt) {
    $stmt->bind_param("s", $student_no);
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
          AND (target_audience = 'all' OR target_audience = 'students' OR (target_audience = 'specific_user' AND target_user_id = ?))
          ORDER BY created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $_SESSION['student_no']);
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
    <title>student-record</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
</head>
<body>

<div class="dashboard-container">
    <!-- HEADER -->
    <?php include("../includes/header.php"); ?>

    <!-- LAYOUT -->
    <div class="dashboard-layout">
        <?php include("../includes/student-sidebar.php"); ?>
        
        <main class="main-content">
            <div class="card welcome-card">
                <h1>Student Record Management</h1>
                <p>Manage, review, and submit your official academic documents and student records.</p>
            </div>

            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-folder-open" style="font-size: 48px; color: #f4b42a; margin-bottom: 15px;"></i>
                <h3>Record Status: <strong><?= $record_filled ? 'Submitted' : 'No Records Added' ?></strong></h3>
                <p style="color: #64748b; margin: 10px 0 20px 0;">
                    <?= $record_filled ? 'Your student records are up to date.' : 'View your student record' ?>
                </p>
                
                <?php if (!$record_filled): ?>
                    <button type="button" class="save-btn" onclick="openRecordModal()" style="display: inline-block; padding: 12px 30px; font-size: 15px;">
                        <i class="fa-solid fa-eye"></i> View My Student Information
                    </button>
                <?php else: ?>
                    <button type="button" class="save-btn" onclick="openRecordModal()" style="display: inline-block; padding: 10px 24px; font-size: 14px; background: #f4b42a; color: #000;">
                        <i class="fa-solid fa-folder-open"></i> View My Student Information
                    </button>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- ================= ADD RECORD MODAL ================= -->
<?php include("student-record-modal.php"); ?>

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

    function confirmSaveRecord() {
        return confirm("Are you sure you want to save this student record?");
    }
</script>
</body>
</html>