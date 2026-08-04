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

// Check if student has already filled up personal information
$info_filled = false;
$stmt = $conn->prepare("SELECT id FROM student_profile WHERE student_no = ?");
$stmt->bind_param("s", $student_no);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    $info_filled = true;
}
$stmt->close();

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
    <title>Student Personal Information</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    <style>
        /* Ensure sidebar links have no underlines and maintain active styling */
        .sidebar-nav .nav-item {
            text-decoration: none !important;
        }
        .sidebar-nav .nav-item.nav-active,
        .sidebar-nav a[href="student-personal-info.php"] {
            background: #f4b42a !important;
            color: #000000 !important;
            font-weight: 700 !important;
        }
        .sidebar-nav a[href="student-personal-info.php"] .nav-icon {
            color: #000000 !important;
        }
    </style>
</head>
<body>

<div class="dashboard-container">
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
                            <h4><?= htmlspecialchars($first_name . ' ' . $last_name); ?></h4>
                            <p><?= htmlspecialchars($student_no); ?></p>
                        </div>
                    </div>
                    <a href="../auth/logout.php" class="profile-logout" id="logoutBtn">
                        <i class="fa-solid fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- LAYOUT -->
    <div class="dashboard-layout">
        <?php include("../includes/student-sidebar.php"); ?>
        
        <main class="main-content">
            <div class="card welcome-card">
                <h1>Personal Information Management</h1>
                <p>Manage and complete your student profile details required by the institution.</p>
            </div>

            <div class="card" style="text-align: center; padding: 40px;">
                <i class="fa-solid fa-id-card-clip" style="font-size: 48px; color: #f4b42a; margin-bottom: 15px;"></i>
                <h3>Student Profile Status: <strong><?= $info_filled ? 'Completed' : 'Pending Information' ?></strong></h3>
                <p style="color: #64748b; margin: 10px 0 20px 0;">
                    <?= $info_filled ? 'Your information has been saved successfully.' : 'Please provide your personal, residential, and family background data.' ?>
                </p>
                
                <?php if (!$info_filled): ?>
                    <button type="button" class="save-btn" onclick="openInfoModal()" style="display: inline-block; padding: 12px 30px; font-size: 15px;">
                        <i class="fa-solid fa-plus"></i> Add Information
                    </button>
                <?php else: ?>
                    <p style="color: #10b981; font-weight: 600;"><i class="fa-solid fa-circle-check"></i> Information already submitted.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</div>

<!-- ================= ADD INFO MODAL ================= -->
<?php include("student-personal-info-modal.php"); ?>

<script src="../assets/js/script.js"></script>
<script>
    function openInfoModal() {
        document.getElementById('infoModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('infoModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function confirmSave() {
        return confirm("Are you sure you want to save this information?");
    }

    document.addEventListener('DOMContentLoaded', function() {
        const notifBtn = document.getElementById('notifBtn');
        const notifDropdown = document.getElementById('notifDropdown');
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        const infoModal = document.getElementById('infoModal');
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
            if (event.target === infoModal) {
                closeModal();
            }
        });
    });
</script>
</body>
</html>