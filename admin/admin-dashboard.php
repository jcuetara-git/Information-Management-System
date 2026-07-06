<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// 1. Fetch Student Counts by Year Level
$summary_query = "SELECT year_level, COUNT(*) as count FROM users WHERE LOWER(role) = 'student' GROUP BY year_level ORDER BY year_level ASC";
$summary_result = $conn->query($summary_query);

$stats = [
    '1st Year' => 0,
    '2nd Year' => 0,
    '3rd Year' => 0,
    '4th Year' => 0,
    'Unknown' => 0
];

$total_students = 0;

while($row = $summary_result->fetch_assoc()) {
    $yl = $row['year_level'];
    $count = (int)$row['count'];
    $total_students += $count;
    
    if (strpos($yl, '1') !== false) $stats['1st Year'] += $count;
    elseif (strpos($yl, '2') !== false) $stats['2nd Year'] += $count;
    elseif (strpos($yl, '3') !== false) $stats['3rd Year'] += $count;
    elseif (strpos($yl, '4') !== false) $stats['4th Year'] += $count;
    else $stats['Unknown'] += $count;
}

// 2. Fetch Faculty Subcategory Counts (FIXED: Added LEFT JOIN to fetch status from faculty_profile)
$faculty_stats = [
    'Full-time Regular' => 0,
    'Full-time Probationary' => 0,
    'Part-time Lawyer' => 0,
    'Part-time Instructor' => 0
];
$total_faculty = 0;

$faculty_query = "SELECT p.status, COUNT(u.id) as count 
                  FROM users u 
                  LEFT JOIN faculty_profile p ON u.student_no = p.faculty_no 
                  WHERE LOWER(u.role) = 'faculty' 
                  GROUP BY p.status";
$faculty_result = $conn->query($faculty_query);

if ($faculty_result) {
    while($row = $faculty_result->fetch_assoc()) {
        $status = strtolower(trim($row['status'] ?? ''));
        $count = (int)$row['count'];
        $total_faculty += $count;

        // Flexible keyword matching to catch all variations or spacing styles
        if (strpos($status, 'regular') !== false) {
            $faculty_stats['Full-time Regular'] += $count;
        } elseif (strpos($status, 'probationary') !== false) {
            $faculty_stats['Full-time Probationary'] += $count;
        } elseif (strpos($status, 'lawyer') !== false || strpos($status, 'law') !== false) {
            $faculty_stats['Part-time Lawyer'] += $count;
        } else {
            // Fallback: If it doesn't match any keyword or is empty, group it under Instructor so it displays
            $faculty_stats['Part-time Instructor'] += $count;
        }
    }
}

// 3. Fetch Total Alumni Registered Count
$total_alumni = 0;
$alumni_query = "SELECT COUNT(*) as count FROM users WHERE LOWER(role) = 'alumni'";
$alumni_result = $conn->query($alumni_query);
if ($alumni_result) {
    $alumni_row = $alumni_result->fetch_assoc();
    $total_alumni = (int)$alumni_row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="description" content="Admin Dashboard - College of Criminal Justice">
    <meta name="theme-color" content="#f4b42c">
    <title>admin-dashboard</title>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/admin-dashboard.css">
</head>

<body>

<div class="main-container">

    <?php 
    include("../includes/sidebar.php");
    ?>
    
    <!-- ================= MAIN CONTENT ================= -->
    <main class="dashboard-container" id="mainContent" role="main">

        <!-- HEADER -->
        <header class="logo-section">
            <div class="logo-left">
                <div class="logo-circle">
                    <img src="../assets/logo.png" alt="College of Criminal Justice Logo">
                </div>

                <div class="logo-text">
                    <h1>College of Criminal Justice</h1>
                    <p>Center of Development in Criminology</p>
                </div>
            </div>
        </header>

        <!-- Welcome Section -->
        <section class="card welcome-card" aria-label="Welcome Section">
            <div class="welcome-content">
                <h2>Welcome, Admin!</h2>
                <p>View administrative overview data counters across accounts.</p>
            </div>
            <div class="welcome-date">
                <i class="fa-regular fa-calendar"></i> <?php echo date('F d, Y'); ?>
            </div>
        </section>

        <!-- Summary Statistics Grid -->
        <div class="stats-grid">
            
            <!-- 1st Group: Student Records -->
            <div class="grid-group-header">Students By Year Level</div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="stat-info">
                    <h3>1st Year</h3>
                    <p><?= number_format($stats['1st Year']) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="stat-info">
                    <h3>2nd Year</h3>
                    <p><?= number_format($stats['2nd Year']) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="stat-info">
                    <h3>3rd Year</h3>
                    <p><?= number_format($stats['3rd Year']) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="stat-info">
                    <h3>4th Year</h3>
                    <p><?= number_format($stats['4th Year']) ?></p>
                </div>
            </div>

            <!-- 2nd Group: Faculty Classifications -->
            <div class="grid-group-header">Faculty Members by Employment Status</div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-tie"></i></div>
                <div class="stat-info">
                    <h3>Full-Time Regular</h3>
                    <p><?= number_format($faculty_stats['Full-time Regular']) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-shield"></i></div>
                <div class="stat-info">
                    <h3>Full-Time Probationary</h3>
                    <p><?= number_format($faculty_stats['Full-time Probationary']) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-scale-balanced"></i></div>
                <div class="stat-info">
                    <h3>Part-Time Lawyers</h3>
                    <p><?= number_format($faculty_stats['Part-time Lawyer']) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-chalkboard-user"></i></div>
                <div class="stat-info">
                    <h3>Part-Time Instructors</h3>
                    <p><?= number_format($faculty_stats['Part-time Instructor']) ?></p>
                </div>
            </div>

            <!-- 3rd Group: Global Summary Counters -->
            <div class="grid-group-header">Overall Summary Counters</div>

            <div class="stat-card total-card">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <h3>Total Students</h3>
                    <p><?= number_format($total_students) ?></p>
                </div>
            </div>
            
            <div class="stat-card total-card">
                <div class="stat-icon"><i class="fa-solid fa-address-book"></i></div>
                <div class="stat-info">
                    <h3>Total Faculty</h3>
                    <p><?= number_format($total_faculty) ?></p>
                </div>
            </div>

            <div class="stat-card total-card">
                <div class="stat-icon"><i class="fa-solid fa-award"></i></div>
                <div class="stat-info">
                    <h3>Total Alumni</h3>
                    <p><?= number_format($total_alumni) ?></p>
                </div>
            </div>

        </div>
    </main>
</div>

</body>
</html>