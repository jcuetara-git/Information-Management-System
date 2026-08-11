<link rel="stylesheet" href="../assets/css/student-dashboard.css">
<?php
// Determine the current page filename to dynamically highlight the active sidebar menu link
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" id="appSidebar">
    
    <!-- Hamburger Toggle Header inside Sidebar -->
    <div class="sidebar-header">
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn" type="button" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span class="sidebar-brand-text">Information Management System</span>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section">Faculty</div>

        <a href="faculty-dashboard.php" class="nav-item <?= ($current_page == 'faculty-dashboard.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-house nav-icon"></i>
            <span class="nav-text">Dashboard</span>
        </a>
        
        <a href="faculty-personal-info.php" class="nav-item <?= ($current_page == 'faculty-personal-info.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-user-tie nav-icon"></i>
            <span class="nav-text">Faculty Information</span>
        </a>

        <a href="faculty-record.php" class="nav-item <?= ($current_page == 'faculty-record.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-folder-open nav-icon"></i>
            <span class="nav-text">Faculty Record</span>
        </a>

        <div class="nav-section">Support</div>

        <a href="faculty-concern.php" class="nav-item <?= ($current_page == 'faculty-concern.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-envelope nav-icon"></i>
            <span class="nav-text">Submit a Concern</span>
        </a>
    </div>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebar = document.getElementById('appSidebar');

        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
            });
        }
    });
</script>