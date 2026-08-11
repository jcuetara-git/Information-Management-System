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

// FIX: Prioritize faculty-specific session keys first so it doesn't fall back to an old student_no value
$faculty_id = $_SESSION['faculty_no'] ?? $_SESSION['faculty_id'] ?? $_SESSION['student_no'] ?? $_SESSION['id_number'] ?? '';

// Synchronize standard session keys for the header dropdown
if (!empty($faculty_id)) {
    $_SESSION['student_no'] = $faculty_id;
    $_SESSION['faculty_no'] = $faculty_id;
}

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

// Fetch announcements for header dropdown
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
    <title>faculty-dashboard</title>
    <!-- Include global stylesheets -->
    <link rel="stylesheet" href="../assets/css/student-dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    
    <!-- SEPARATED HEADER INCLUDE -->
    <?php include("../includes/header.php"); ?>

    <!-- LAYOUT: Sidebar + Main Content -->
    <div class="dashboard-layout">
        
        <!-- Include Faculty Sidebar -->
        <?php include("../includes/faculty-sidebar.php"); ?>

        <!-- MAIN PAGE CONTENT -->
        <main class="main-content">
            <div class="content-wrapper">
                
                <!-- Welcome Banner -->
                <div style="background: #ffffff; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
                    <h2 style="font-size: 24px; color: #1e293b; margin-bottom: 8px;">Hi, <?= htmlspecialchars($first_name); ?>! 👋</h2>
                    <p style="color: #64748b; font-size: 14px;">Manage your professional portfolio information and view your teaching record using the sidebar menu.</p>
                </div>

                <!-- Quick Stats / Status Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <div style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 16px;">
                        <div style="background: #eff6ff; color: #2563eb; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            <i class="fa-solid fa-id-badge"></i>
                        </div>
                        <div>
                            <p style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Faculty ID</p>
                            <h4 style="font-size: 18px; color: #1e293b;"><?= htmlspecialchars($faculty_id ?: 'N/A'); ?></h4>
                        </div>
                    </div>

                    <div style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 16px;">
                        <?php if ($portfolio_exists): ?>
                            <!-- Completed State -->
                            <div style="background: #f0fdf4; color: #16a34a; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <p style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Portfolio Status</p>
                                <h4 style="font-size: 18px; color: #1e293b;">Portfolio Saved</h4>
                            </div>
                        <?php else: ?>
                            <!-- Pending State -->
                            <div style="background: #fefce8; color: #ca8a04; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <p style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Portfolio Status</p>
                                <h4 style="font-size: 18px; color: #1e293b;">Pending Portfolio</h4>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </main>
        <!-- END MAIN CONTENT -->

    </div> 
</div>

</body>
</html>