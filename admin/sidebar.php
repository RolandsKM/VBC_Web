<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function hasRole($requiredRoles) {
    return isset($_SESSION['role']) && in_array($_SESSION['role'], (array)$requiredRoles);
}

$currentPage = basename($_SERVER['PHP_SELF']);

$pageAccess = [
    'index.php' => ['admin', 'mod', 'supper-admin'],
    'user_manager.php' => ['admin', 'supper-admin'],
    'event_manager.php' => ['admin', 'supper-admin'],
    'admin_manager.php' => ['supper-admin'],
    'report_manager.php' => ['mod', 'admin', 'supper-admin']
];

if (isset($pageAccess[$currentPage]) && !hasRole($pageAccess[$currentPage])) {
    header("Location: " . $_SERVER['HTTP_REFERER'] ?? 'index.php');
    exit();
}
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<aside class="admin-sidebar" id="adminSidebar">
    <h3 class="admin-sidebar-title">VBC - ADMIN</h3>

    <nav class="admin-nav">
        <?php if (hasRole(['admin', 'mod', 'supper-admin'])): ?>
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        <?php endif; ?>

        <?php if (hasRole(['admin', 'supper-admin'])): ?>
            <div class="dropdown-admin <?= in_array($currentPage, ['user_manager.php', 'event_manager.php', 'admin_manager.php']) ? 'open' : '' ?>">
                <button class="dropdown-toggle-admin <?= in_array($currentPage, ['user_manager.php', 'event_manager.php', 'admin_manager.php']) ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i>
                    <span>Pārvaldība</span>
                </button>
                <div class="dropdown-content-admin">
                    <a href="user_manager.php" class="<?= $currentPage === 'user_manager.php' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>Lietotāju pārvaldība</span>
                    </a>
                    <a href="event_manager.php" class="<?= $currentPage === 'event_manager.php' ? 'active' : '' ?>">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Sludinājumu pārvaldība</span>
                    </a>
                    <?php if (hasRole(['supper-admin'])): ?>
                        <a href="admin_manager.php" class="<?= $currentPage === 'admin_manager.php' ? 'active' : '' ?>">
                            <i class="fas fa-user-shield"></i>
                            <span>Admina pārvaldība</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasRole(['mod', 'admin', 'supper-admin'])): ?>
            <a href="report_manager.php" class="<?= $currentPage === 'report_manager.php' ? 'active' : '' ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Ziņojumi & Moderācija</span>
            </a>
        <?php endif; ?>

        <a href="../database/auth_functions.php?logout=1">
            <i class="fas fa-sign-out-alt"></i>
            <span>Izrakstīties</span>
        </a>
    </nav>
</aside>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('adminSidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const mainContent = document.querySelector('.main-content');
    const overlay = document.getElementById('sidebarOverlay');
    
    // Toggle sidebar
    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        } else {
            sidebar.classList.toggle('collapsed');
            toggleBtn.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            
            // Update toggle button icon
            const icon = toggleBtn.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-chevron-right');
            } else {
                icon.classList.remove('fa-chevron-right');
                icon.classList.add('fa-bars');
            }
        }
    }

    toggleBtn.addEventListener('click', toggleSidebar);
    
    // Close sidebar when clicking overlay
    overlay.addEventListener('click', () => {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('show');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    });

    // Handle dropdowns
    document.querySelectorAll('.dropdown-toggle-admin').forEach(toggle => {
        toggle.addEventListener('click', () => {
            const dropdown = toggle.closest('.dropdown-admin');
            dropdown.classList.toggle('open');
        });
    });

    // Handle mobile responsiveness
    function handleResize() {
        if (window.innerWidth <= 768) {
            sidebar.classList.remove('collapsed');
            toggleBtn.classList.remove('collapsed');
            mainContent.classList.remove('expanded');
            overlay.classList.remove('show');
            document.body.style.overflow = '';
        }
    }

    window.addEventListener('resize', handleResize);
    handleResize(); // Initial check
});
</script>
