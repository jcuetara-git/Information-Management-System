// Accessible sidebar + dropdown toggles (updated to add body.sidebar-visible to push main content)
// Usage: include <script src="sidebar-toggle.js" defer></script> at end of <body>

(function () {
  document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const sidebar = document.querySelector('.sidebar');
    const toggleButtons = Array.from(document.querySelectorAll('.sidebar-toggle-btn'));
    let overlay = document.querySelector('.sidebar-overlay');

    if (!sidebar) {
      // No sidebar found — nothing to do
      return;
    }

    // Ensure sidebar has an id for aria-controls
    if (!sidebar.id) sidebar.id = 'sidebar';

    // Create overlay if not present
    if (!overlay) {
      overlay = document.createElement('div');
      overlay.className = 'sidebar-overlay';
      document.body.appendChild(overlay);
    }

    // Helper to determine if current viewport is "mobile"
    function isMobile() {
      return window.innerWidth <= 767;
    }

    // Initialize body class state based on sidebar presence and viewport
    function syncBodyClassOnLoad() {
      // If sidebar is not collapsed and not mobile => show it and push content
      if (!sidebar.classList.contains('collapsed') && !isMobile()) {
        document.body.classList.add('sidebar-visible');
      } else {
        document.body.classList.remove('sidebar-visible');
      }

      // If sidebar is open on mobile, show overlay
      if (!sidebar.classList.contains('collapsed') && isMobile()) {
        overlay.classList.add('active');
      } else {
        overlay.classList.remove('active');
      }

      // ARIA states
      sidebar.setAttribute('aria-hidden', sidebar.classList.contains('collapsed') ? 'true' : 'false');
    }

    // Open/close helpers
    function openSidebar() {
      sidebar.classList.remove('collapsed');
      sidebar.setAttribute('aria-hidden', 'false');

      if (isMobile()) {
        overlay.classList.add('active');
        document.body.classList.remove('sidebar-visible'); // overlay mode, don't push content
      } else {
        overlay.classList.remove('active');
        document.body.classList.add('sidebar-visible'); // push content on larger screens
      }

      toggleButtons.forEach(btn => btn.setAttribute('aria-expanded', 'true'));
      document.documentElement.classList.add('sidebar-open'); // optional to prevent scroll styling if you add it
    }

    function closeSidebar() {
      sidebar.classList.add('collapsed');
      sidebar.setAttribute('aria-hidden', 'true');
      overlay.classList.remove('active');
      document.body.classList.remove('sidebar-visible');

      toggleButtons.forEach(btn => btn.setAttribute('aria-expanded', 'false'));
      document.documentElement.classList.remove('sidebar-open');
    }

    function toggleSidebar() {
      if (sidebar.classList.contains('collapsed')) openSidebar();
      else closeSidebar();
    }

    // Wire toggle buttons
    if (toggleButtons.length) {
      toggleButtons.forEach(btn => {
        btn.setAttribute('aria-controls', sidebar.id);
        btn.setAttribute('aria-expanded', sidebar.classList.contains('collapsed') ? 'false' : 'true');
        btn.addEventListener('click', function (e) {
          e.stopPropagation();
          toggleSidebar();
        });
      });
    }

    // Overlay click closes sidebar (mobile)
    overlay.addEventListener('click', function () {
      closeSidebar();
    });

    // Escape closes sidebar and other open dropdowns
    document.addEventListener('keydown', function (evt) {
      if (evt.key === 'Escape' || evt.key === 'Esc') {
        // close sidebar if open
        if (!sidebar.classList.contains('collapsed')) {
          closeSidebar();
        }
        // close dropdowns
        closeDropdown(notificationDropdown);
        closeDropdown(profileDropdown);
      }
    });

    // --- Notifications & Profile dropdown handling ---
    const notificationBtn = document.querySelector('.notification-btn');
    const notificationDropdown = document.querySelector('.notification-dropdown');
    const profileMenu = document.querySelector('.profile-menu');
    const profileDropdown = document.querySelector('.profile-dropdown');

    function closeDropdown(drop) {
      if (!drop) return;
      drop.classList.remove('active');
      const trigger = (drop === notificationDropdown) ? notificationBtn : (drop === profileDropdown) ? profileMenu : null;
      if (trigger) trigger.setAttribute('aria-expanded', 'false');
    }

    if (notificationBtn && notificationDropdown) {
      notificationBtn.setAttribute('aria-expanded', 'false');
      // make sure container is referenceable for badge placement
      notificationBtn.addEventListener('click', function (e) {
        e.stopPropagation();
        const open = notificationDropdown.classList.toggle('active');
        notificationBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        // close profile if open
        if (profileDropdown && profileDropdown.classList.contains('active')) {
          closeDropdown(profileDropdown);
        }
      });
    }

    if (profileMenu && profileDropdown) {
      profileMenu.setAttribute('aria-expanded', 'false');
      profileMenu.addEventListener('click', function (e) {
        e.stopPropagation();
        const open = profileDropdown.classList.toggle('active');
        profileMenu.setAttribute('aria-expanded', open ? 'true' : 'false');
        // close notification if open
        if (notificationDropdown && notificationDropdown.classList.contains('active')) {
          closeDropdown(notificationDropdown);
        }
      });
    }

    // Click outside to close dropdowns
    document.addEventListener('click', function (e) {
      if (notificationDropdown && !notificationDropdown.contains(e.target) && notificationBtn && !notificationBtn.contains(e.target)) {
        closeDropdown(notificationDropdown);
      }
      if (profileDropdown && !profileDropdown.contains(e.target) && profileMenu && !profileMenu.contains(e.target)) {
        closeDropdown(profileDropdown);
      }
    });

    // Sync initial state
    syncBodyClassOnLoad();

    // Update behavior on window resize:
    window.addEventListener('resize', function () {
      // If viewport becomes large, remove overlay and push content if sidebar is open
      if (window.innerWidth > 767) {
        overlay.classList.remove('active');
        if (!sidebar.classList.contains('collapsed')) {
          document.body.classList.add('sidebar-visible');
        }
      } else {
        // On mobile widths, if sidebar is open show overlay
        if (!sidebar.classList.contains('collapsed')) {
          overlay.classList.add('active');
          document.body.classList.remove('sidebar-visible');
        } else {
          overlay.classList.remove('active');
          document.body.classList.remove('sidebar-visible');
        }
      }
    });
  });
})();