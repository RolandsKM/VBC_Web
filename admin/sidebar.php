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
<style>
    .admin-sidebar-title{
       color: #2c3e50;
    }
    .admin-nav a.active,
    .dropdown-toggle-admin.active {
        color: green; 
        font-weight: bold; 
    }
    .dropdown-content-admin a.active {
        color: green;
        font-weight: bold;
    }
</style>
<aside class="admin-sidebar">
    <h3 class="admin-sidebar-title">VBC - ADMIN</h3>

    <nav class="admin-nav">
        <?php if (hasRole(['admin', 'mod', 'supper-admin'])): ?>
            <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Dashboard</a>
        <?php endif; ?>

        <?php if (hasRole(['admin', 'supper-admin'])): ?>
            <div class="dropdown-admin <?= in_array($currentPage, ['user_manager.php', 'event_manager.php', 'admin_manager.php']) ? 'open' : '' ?>">
                <button class="dropdown-toggle-admin <?= in_array($currentPage, ['user_manager.php', 'event_manager.php', 'admin_manager.php']) ? 'active' : '' ?>">
                    Pārvaldība
                </button>
                <div class="dropdown-content-admin">
                    <a href="user_manager.php" class="<?= $currentPage === 'user_manager.php' ? 'active' : '' ?>">Lietotāju pārvaldība</a>
                    <a href="event_manager.php" class="<?= $currentPage === 'event_manager.php' ? 'active' : '' ?>">Sludinājumu pārvaldība</a>
                    <?php if (hasRole(['supper-admin'])): ?>
                        <a href="admin_manager.php" class="<?= $currentPage === 'admin_manager.php' ? 'active' : '' ?>">Admina pārvaldība</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (hasRole(['mod', 'admin', 'supper-admin'])): ?>
            <a href="report_manager.php" class="<?= $currentPage === 'report_manager.php' ? 'active' : '' ?>">Ziņojumi & Moderācija</a>
        <?php endif; ?>

        <a href="../database/auth_functions.php?logout=1">Izrakstīties</a>
    </nav>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.dropdown-toggle-admin').forEach(toggle => {
            toggle.addEventListener('click', () => {
                const dropdown = toggle.closest('.dropdown-admin');
                dropdown.classList.toggle('open');
            });
        });
    });
</script>
