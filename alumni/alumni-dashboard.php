<?php
session_start();
include("../config/db.php");

// Ensure the alumni is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'alumni') {
    header("Location: ../auth/login.php");
    exit();
}

// Retrieve the student/alumni number dynamically from the session
$student_no = $_SESSION['student_no'] ?? $_SESSION['student_number'] ?? '';
$first_name = $_SESSION['firstname'] ?? $_SESSION['first_name'] ?? 'Alumni';
$last_name = $_SESSION['lastname'] ?? $_SESSION['last_name'] ?? '';

// 1. Check if the alumni has filled out their profile information and retrieve alumni_no
$is_profile_complete = false;
$alumni_no = $_SESSION['alumni_no'] ?? $student_no; // Fallback to student_no if session variable is missing

$check_query = "SELECT alumni_no FROM alumni_profile WHERE student_no = ? LIMIT 1"; 
$check_stmt = $conn->prepare($check_query);
if ($check_stmt) {
    $check_stmt->bind_param("s", $student_no);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    if ($check_result && $check_result->num_rows > 0) {
        $is_profile_complete = true;
        $row = $check_result->fetch_assoc();
        if (!empty($row['alumni_no'])) {
            $alumni_no = $row['alumni_no'];
        }
    }
    $check_stmt->close();
}

// Fetch announcements for header dropdown
$announcements = [];
$conn->query("SET time_zone = '+08:00'");

// Change the session identifier depending on the user type (e.g., alumni_no, student_no, or faculty_no)
$user_id = $_SESSION['alumni_no'] ?? $_SESSION['student_no'] ?? $_SESSION['faculty_no'] ?? '';
$role = $_SESSION['role'] ?? 'student'; // 'student', 'faculty', 'alumni'

$query = "SELECT title, message, created_at, 
          (created_at >= NOW() - INTERVAL 1 DAY) AS is_new 
          FROM announcements 
          WHERE status = 'published' 
          AND (
              target_audience = 'all' 
              OR target_audience = ? 
              OR (target_audience = 'specific_user' AND target_user_id = ?)
          )
          ORDER BY created_at DESC LIMIT 10";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $role, $user_id);
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
    <title>alumni-dashboard</title>
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
    
        <!-- Include alumni sidebar -->
        <?php include("../includes/alumni-sidebar.php"); ?>

        <!-- MAIN PAGE CONTENT -->
        <main class="main-content">
            <div class="content-wrapper">
                
                <!-- Welcome Banner -->
                <div style="background: #ffffff; padding: 24px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 24px;">
                    <h2 style="font-size: 24px; color: #1e293b; margin-bottom: 8px;">Hi, <?= htmlspecialchars($first_name); ?>! 👋</h2>
                    <p style="color: #64748b; font-size: 14px;">Keep your alumni records updated and stay connected with the college using the sidebar menu.</p>
                </div>

                <!-- Quick Stats / Status Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                    <div style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 16px;">
                        <div style="background: #eff6ff; color: #2563eb; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <div>
                            <p style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Alumni ID Number</p>
                            <h4 style="font-size: 18px; color: #1e293b;"><?= htmlspecialchars($alumni_no); ?></h4>
                        </div>
                    </div>

                    <div style="background: #ffffff; padding: 20px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); display: flex; align-items: center; gap: 16px;">
                        <?php if ($is_profile_complete): ?>
                            <!-- Completed State -->
                            <div style="background: #f0fdf4; color: #16a34a; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fa-solid fa-circle-check"></i>
                            </div>
                            <div>
                                <p style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Profile Status</p>
                                <h4 style="font-size: 18px; color: #1e293b;">Completed & Saved</h4>
                            </div>
                        <?php else: ?>
                            <!-- Pending State -->
                            <div style="background: #fefce8; color: #ca8a04; width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                                <i class="fa-solid fa-triangle-exclamation"></i>
                            </div>
                            <div>
                                <p style="font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-bottom: 4px;">Profile Status</p>
                                <h4 style="font-size: 18px; color: #1e293b;">Pending Information</h4>
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