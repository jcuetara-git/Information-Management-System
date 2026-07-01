<link rel="stylesheet" href="../assets/css/admin-dashboard.css">
<?php 
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page == 'admin-students.php') echo '<link rel="stylesheet" href="../assets/css/admin-students.css">';
    if ($current_page == 'admin-edit-student.php') echo '<link rel="stylesheet" href="../assets/css/admin-edit-student.css">';
    if ($current_page == 'admin-view-student.php') echo '<link rel="stylesheet" href="../assets/css/admin-view-student.css">';
?>

<nav class="sidebar" id="sidebar" role="navigation" aria-label="Main Navigation">
    <div class="sidebar-header">
        <button id="toggleSidebar" class="hamburger-btn" aria-label="Toggle Sidebar">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span class="sidebar-title">UC-MAIN CCJ</span>
    </div>

    <ul>
        <?php
        // 1. Get the current filename
        $current_page = basename($_SERVER['PHP_SELF']);
        
        // 2. Map sub-pages & management pages to their parent menu items
        $active_page = $current_page;
        
        // Student pages tracking group
        $student_pages = ['admin-students.php', 'manage-students.php', 'admin-edit-student.php', 'admin-view-student.php'];
        if (in_array($current_page, $student_pages)) {
            $active_page = 'admin-students.php'; 
        }

        // Faculty pages tracking group (Keeps "Faculty" highlighted)
        $faculty_pages = ['admin-faculty.php', 'manage-faculty.php', 'admin-edit-faculty.php', 'admin-view-faculty.php'];
        if (in_array($current_page, $faculty_pages)) {
            $active_page = 'admin-faculty.php';
        }

        $menu_items = [
            ['file' => 'admin-dashboard.php', 'icon' => 'fa-chart-line', 'label' => 'Dashboard'],
            ['file' => 'admin-students.php', 'icon' => 'fa-users', 'label' => 'Students'],
            ['file' => 'admin-faculty.php', 'icon' => 'fa-user-tie', 'label' => 'Faculty'],
            ['file' => 'admin-internship.php', 'icon' => 'fa-briefcase', 'label' => 'Internship'],
            ['file' => 'admin-orgs.php', 'icon' => 'fa-folder', 'label' => 'Organizations'],
            ['file' => 'admin-comm-ext.php', 'icon' => 'fa-house', 'label' => 'Community Extension'],
            ['file' => 'admin-jones.php', 'icon' => 'fa-file', 'label' => 'Indiana Jones'],
            ['file' => 'admin-retention.php', 'icon' => 'fa-user-check', 'label' => 'Retention Policy'],
            ['file' => 'admin-alumni.php', 'icon' => 'fa-user-graduate', 'label' => 'Alumni']
        ];

        foreach ($menu_items as $item) {
            $active = ($active_page == $item['file']) ? 'class="active"' : '';
            echo "<li role='menuitem' $active onclick=\"window.location.href='{$item['file']}'\">
                    <i class='fa-solid {$item['icon']}'></i> <span>{$item['label']}</span>
                  </li>";
        }
        ?>
    </ul>
    <div class="profile-menu">
        <a href="../auth/logout.php" role="menuitem">
            <i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span>
        </a>
    </div>
</nav>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleSidebar');
        const sidebar = document.getElementById('sidebar');

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const mainContent = document.getElementById('mainContent');

                if (sidebar) sidebar.classList.toggle('collapsed');
                if (mainContent) mainContent.classList.toggle('expanded');
            });
        }
    });
</script>