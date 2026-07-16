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

// Check if portfolio already exists to dynamically change to a checkmark (just like the student card)
$portfolio_exists = false;
$stmt = $conn->prepare("SELECT id FROM faculty_profile WHERE faculty_no = ?");
$stmt->bind_param("s", $faculty_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $portfolio_exists = true;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>faculty dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/faculty-dashboard.css?v=1.4">
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

    <main style="max-width: 1200px; margin: 0 auto; padding: 0 20px; box-sizing: border-box;">

        <?php 
        if (isset($_SESSION['portfolio_success_msg'])): ?>
            <!-- Clean Success Alert Banner with Close Button and timed JavaScript dismiss logic -->
            <div id="successAlertBanner" style="background-color: #d1fae5; color: #065f46; padding: 15px 20px; border-radius: 12px; margin: 20px 0 0 0; border: 1px solid #a7f3d0; font-weight: 500; display: flex; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); font-family: sans-serif; transition: opacity 0.5s ease, margin 0.5s ease, padding 0.5s ease, height 0.5s ease; overflow: hidden; opacity: 1;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i class="fa-solid fa-circle-check" style="color: #10b981; font-size: 1.2rem;"></i>
                    <span>Portfolio is submitted successfully!</span>
                </div>
                <button type="button" onclick="dismissAlertBanner()" style="background: none; border: none; color: #065f46; font-size: 1.4rem; cursor: pointer; line-height: 1; padding: 0 5px; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.7;">&times;</button>
            </div>
        <?php 
            unset($_SESSION['portfolio_success_msg']);
        endif; 
        ?>
        
        <div class="card welcome-card" style="margin-top: 20px;">
            <h1>Hi, <?= htmlspecialchars($first_name) ?>!👋</h1>
            <p>Manage your professional portfolio information and view your teaching record.</p>
        </div>

        <!-- ANNOUNCEMENTS FEED -->
        <div class="card announcements-section" style="margin-top: 20px;">
            <h2 style="margin-bottom: 15px;"><i class="fa-solid fa-bullhorn" style="color: #f3b12b; margin-right: 0;"></i> Recent Announcements</h2>
            
            <?php
            // Enforce synchronization of the time zone connection environment with PHP's timezone context
            $conn->query("SET time_zone = '+08:00'");

            // Announcements Query tailored for Faculty audience
            $query = "SELECT title, message, created_at, 
                      (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
                      FROM announcements 
                      WHERE status = 'published' 
                      AND (target_audience = 'all' OR target_audience = 'faculty' OR (target_audience = 'specific_user' AND target_user_id = ?))
                      ORDER BY created_at DESC LIMIT 5";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $faculty_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0): 
                while ($row = $result->fetch_assoc()): ?>
                    <div class="announcement-item" style="border-bottom: 1px solid #eee; padding: 10px 0;">
                        <h3 style="font-size: 16px; margin: 0; display: flex; align-items: center; flex-wrap: wrap; gap: 6px;">
                            <?= htmlspecialchars($row['title']) ?>
                            <?php if ($row['is_new'] == 1): ?>
                                <span style="background: #f4b42c; color: white; padding: 2px 6px; border-radius: 4px; font-size: 10px;">NEW</span>
                            <?php endif; ?>
                            <span style="font-size: 11px; color: #999; font-weight: normal;">
                                • <?= date('M d, Y h:i A', strtotime($row['created_at'])) ?>
                            </span>
                        </h3>
                        <p style="font-size: 14px; color: #666; margin: 5px 0; line-height: 1.4;"><?= htmlspecialchars($row['message']) ?></p>
                    </div>
                <?php endwhile; 
            else: ?>
                <p style='color:#777;'>No new announcements at this time.</p>
            <?php endif; 
            $stmt->close();
            ?>
        </div>

        <?php if ($portfolio_exists): ?>
            <div class="card add-info-card disabled" style="margin-top: 20px;">
                <div class="check-icon"></div>
                <p>Faculty Portfolio Added</p>
            </div>
        <?php else: ?>
            <a href="faculty-add-portfolio.php" class="add-info-link">
                <div class="card add-info-card" style="margin-top: 20px;">
                    <div class="plus-icon"></div>
                    <p>Add Faculty Portfolio</p>
                </div>
            </a>
        <?php endif; ?>

        <div class="button-container">
            <button class="view-btn" onclick="window.location.href='faculty-view-record.php'">
                <i class="fa-solid fa-file-lines"></i> View Record
            </button>
        </div>

    </main>

    <script>
        function dismissAlertBanner() {
            const banner = document.getElementById('successAlertBanner');
            if (banner) {
                banner.style.opacity = '0';
                setTimeout(() => {
                    banner.style.padding = '0px';
                    banner.style.margin = '0px';
                    banner.style.height = '0px';
                    banner.style.border = 'none';
                    setTimeout(() => banner.remove(), 500);
                }, 400);
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const banner = document.getElementById('successAlertBanner');
            if (banner) {
                setTimeout(() => {
                    dismissAlertBanner();
                }, 5000);
            }
        });
    </script>

</body>
</html>