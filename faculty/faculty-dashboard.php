<?php
session_start();
include("../config/db.php");

// Protect page access - ensure user is logged in and is a faculty member
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'faculty') {
    header("Location: ../auth/login.php");
    exit();
}

$first_name = $_SESSION['first_name'] ?? 'Faculty';
$last_name  = $_SESSION['last_name'] ?? '';
$faculty_id = $_SESSION['student_no'] ?? $_SESSION['faculty_id'] ?? '';

// Check if portfolio already exists to dynamically change status
$portfolio_exists = false;
if (!empty($faculty_id)) {
    $stmt = $conn->prepare("SELECT id FROM faculty_profile WHERE faculty_no = ?");
    $stmt->bind_param("s", $faculty_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $portfolio_exists = true;
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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    <link rel="stylesheet" href="../assets/css/student-view-record.css">
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

    <!-- WELCOME -->
    <div class="card welcome-card">
        <h1>Hi, <?= htmlspecialchars($first_name); ?>!👋</h1>
        <p>Manage your professional portfolio information and view your teaching record.</p>
    </div>

    <!-- PROGRAM CARDS GRID (Student Dashboard Design Layout) -->
    <div class="program-grid">
        
        <!-- Faculty Portfolio Card -->
        <div class="program-card">
            <div class="card-header">
                <div class="icon-circle">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="card-title-area">
                    <h3>Faculty Portfolio</h3>
                    <p class="card-desc">
                        <?= $portfolio_exists ? "Your portfolio information is already saved." : "Add your professional faculty portfolio." ?>
                    </p>
                </div>
            </div>
            <div class="btn-container">
                <a href="<?= $portfolio_exists ? 'javascript:void(0)' : 'faculty-add-portfolio.php' ?>" 
                class="program-btn <?= $portfolio_exists ? 'disabled-btn' : '' ?>"
                <?= $portfolio_exists ? 'style="pointer-events: none; opacity: 0.6; cursor: not-allowed;"' : '' ?>>
                    <span>
                        <i class="fa-solid fa-user-gear"></i> 
                        <?= $portfolio_exists ? 'Portfolio Saved' : 'Add Portfolio' ?>
                    </span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
        </div>

        <!-- Faculty Record Card -->
        <div class="program-card">
            <div class="card-header">
                <div class="icon-circle">
                    <i class="fa-solid fa-folder-open"></i>
                </div>
                <div class="card-title-area">
                    <h3>My Teaching Record</h3>
                    <p class="card-desc">View your professional faculty record and details.</p>
                </div>
            </div>
            <div class="btn-container">
                <a href="faculty-view-record.php" class="program-btn">
                    <span><i class="fa-solid fa-file-lines"></i> View Record</span>
                    <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
        </div>

    </div>

    <div style="text-align: center; padding: 30px; color: #999; font-size: 13px;">
        ©2026 College of Criminal Justice | Version 1.1
    </div>
</div>

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
    });
</script>
</body>
</html>  