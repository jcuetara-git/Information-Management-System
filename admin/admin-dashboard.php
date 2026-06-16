<?php
include("../config/db.php");
include("../config/auth.php");

if($_SESSION['role'] != "admin"){
    header("Location: ../auth/login.php");
    exit();
}

// Fetch Student Counts by Year Level
$summary_query = "SELECT year_level, COUNT(*) as count FROM users WHERE role = 'student' GROUP BY year_level ORDER BY year_level ASC";
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

    <!-- ================= SIDEBAR ================= -->
    <nav class="sidebar" id="sidebar" role="navigation" aria-label="Main Navigation">
        <div class="sidebar-header">
            <button id="toggleSidebar" class="hamburger-btn" aria-label="Toggle Sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <span class="sidebar-title">UC-MAIN CCJ</span>
        </div>

        <ul class="sidebar-nav">
            <li class="active" role="menuitem" onclick="window.location.href='admin-dashboard.php'">
                <i class="fa-solid fa-chart-line"></i> 
                <span>Dashboard</span>
            </li>
            <li role="menuitem" onclick="window.location.href='admin-students.php'">
                <i class="fa-solid fa-users"></i> 
                <span>Students</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-user-tie"></i> 
                <span>Faculty</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-briefcase"></i> 
                <span>Internship</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-folder"></i> 
                <span>Organizations</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-people-roof"></i>
                <span>Community Extension</span>
            </li>
            <li role="menuitem">
                <i class="fa-solid fa-file"></i> 
                <span>Indiana Jones</span>
            </li>
        </ul>

        <div class="profile-menu">
            <a href="../auth/logout.php" role="menuitem">
                <i class="fa-solid fa-sign-out-alt"></i> 
                <span>Logout</span>
            </a>
        </div>
    </nav>

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
                <p>View students summary per year level.</p>
            </div>
            <div class="welcome-date">
                <p><i class="fa-regular fa-calendar"></i> <?php echo date('F d, Y'); ?></p>
            </div>
        </section>

        <!-- Summary Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                <div class="stat-info">
                    <h3>1st Year</h3>
                    <p><?= number_format($stats['1st Year']) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                <div class="stat-info">
                    <h3>2nd Year</h3>
                    <p><?= number_format($stats['2nd Year']) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                <div class="stat-info">
                    <h3>3rd Year</h3>
                    <p><?= number_format($stats['3rd Year']) ?></p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-user-graduate"></i></div>
                <div class="stat-info">
                    <h3>4th Year</h3>
                    <p><?= number_format($stats['4th Year']) ?></p>
                </div>
            </div>
            <div class="stat-card total-card">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <h3>Total Students</h3>
                    <p><?= number_format($total_students) ?></p>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
    });
</script>

</body>
</html>
