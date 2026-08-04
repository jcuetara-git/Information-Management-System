<link rel="stylesheet" href="../assets/css/student-dashboard.css">

<?php
// Determine the current page filename to dynamically highlight the active sidebar menu link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" id="appSidebar">

    <div class="sidebar-nav">
        <div class="nav-section">Student</div>

        <a href="student-dashboard.php" class="nav-item <?= ($current_page == 'student-dashboard.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-house nav-icon"></i>
            <span class="nav-text">Dashboard</span>
        </a>
        
        <a href="student-personal-info.php" class="nav-item <?= ($current_page == 'student-personal-info.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-user nav-icon"></i>
            <span class="nav-text">Personal Information</span>
        </a>

        <a href="student-record.php" class="nav-item <?= ($current_page == 'student-record.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-folder-open nav-icon"></i>
            <span class="nav-text">Student Record</span>
        </a>

        <div class="nav-section">Academic Support</div>

        <a href="retention-policy.php" class="nav-item <?= ($current_page == 'retention-policy.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-graduation-cap nav-icon"></i>
            <span class="nav-text">Retention Policy</span>
        </a>

        <a href="indiana-jones.php" class="nav-item <?= ($current_page == 'indiana-jones.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-calendar nav-icon"></i>
            <span class="nav-text">Indiana Jones Program</span>
        </a>

        <!-- UPDATED: Now links to submit-concern.php with active state matching Retention Policy -->
        <a href="submit-concern.php" class="nav-item <?= ($current_page == 'submit-concern.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-envelope nav-icon"></i>
            <span class="nav-text">Submit Concern</span>
        </a>
    </div>
</aside>