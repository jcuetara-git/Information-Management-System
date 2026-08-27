<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar" id="appSidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle-btn" id="sidebarToggleBtn" type="button" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span class="sidebar-brand-text" style="font-weight:800; font-size: 18px;">UCMAIN CCJ IMS</span>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section">Alumni</div>

        <a href="alumni-dashboard.php" class="nav-item <?= ($current_page == 'alumni-dashboard.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-house nav-icon"></i>
            <span class="nav-text">Dashboard</span>
        </a>
        
        <a href="alumni-personal-info.php" class="nav-item <?= ($current_page == 'alumni-personal-info.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-user-graduate nav-icon"></i>
            <span class="nav-text">Alumni Information</span>
        </a>

        <a href="alumni-record.php" class="nav-item <?= ($current_page == 'alumni-record.php') ? 'nav-active' : '' ?>">
            <i class="fa-solid fa-folder-open nav-icon"></i>
            <span class="nav-text">Alumni Record</span>
        </a>

        <div class="nav-section" style="margin-top: 20px;">Support</div>

        <a href="alumni-concern.php" class="nav-item <?= ($current_page == 'alumni-concern.php') ? 'nav-active' : '' ?>">
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