<link rel="stylesheet" href="../assets/css/student-dashboard.css">

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
                    <span class="profile-name"><?= htmlspecialchars($first_name ?? $_SESSION['first_name'] ?? 'User'); ?></span>
                </div>
                <div class="profile-dropdown" id="profileDropdown">
                    <div class="profile-dropdown-header">
                        <div class="profile-avatar"><?= strtoupper(substr($first_name ?? $_SESSION['first_name'] ?? 'U', 0, 1)); ?></div>
                        <div class="profile-info">
                            <h4><?= htmlspecialchars($full_name ?? $faculty_name ?? $student_name ?? $_SESSION['full_name'] ?? ($first_name ?? 'Account Name')); ?></h4>
                            <p><?= htmlspecialchars($role ?? $_SESSION['role'] ?? 'User Role'); ?></p>
                        </div>
                    </div>
                    <a href="../auth/logout.php" class="profile-logout" id="logoutBtn">
                        <i class="fa-solid fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const notifBtn = document.getElementById('notifBtn');
        const notifDropdown = document.getElementById('notifDropdown');
        const profileBtn = document.getElementById('profileBtn');
        const profileDropdown = document.getElementById('profileDropdown');
        const notifBadge = document.getElementById('notifBadge');

        if (notifBtn && notifDropdown) {
            notifBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                notifDropdown.classList.toggle('active');
                if (profileDropdown) profileDropdown.classList.remove('active');
                if (notifBadge && notifDropdown.classList.contains('active')) {
                    notifBadge.style.display = 'none';
                }
            });
        }

        if (profileBtn && profileDropdown) {
            profileBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                profileDropdown.classList.toggle('active');
                if (notifDropdown) notifDropdown.classList.remove('active');
            });
        }

        document.addEventListener('click', function(event) {
            if (notifDropdown && notifBtn && !notifBtn.contains(event.target) && !notifDropdown.contains(event.target)) {
                notifDropdown.classList.remove('active');
            }
            if (profileDropdown && profileBtn && !profileBtn.contains(event.target) && !profileDropdown.contains(event.target)) {
                profileDropdown.classList.remove('active');
            }
        });
    });
</script>

<style>
    /* Ensures the active class properly overrides display settings */
    .notification-dropdown.active,
    .profile-dropdown.active {
        display: block !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    .header-right {
        position: relative;
        z-index: 1000;
    }
    .notification-container,
    .profile-container {
        position: relative;
    }
    .profile-dropdown-header h4,
    .profile-dropdown-header p,
    .profile-info h4,
    .profile-info p {
        text-decoration: none !important;
    }
</style>