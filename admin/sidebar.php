<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentPage = basename($_SERVER['PHP_SELF']);
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
        <a href="index.php" class="<?= $currentPage === 'index.php' ? 'active' : '' ?>">Dashboard</a>

        <div class="dropdown-admin <?= in_array($currentPage, ['user_manager.php', 'event_manager.php, admin_manager']) ? 'open' : '' ?>">
            <button class="dropdown-toggle-admin <?= in_array($currentPage, ['user_manager.php', 'event_manager.php, admin_manager']) ? 'active' : '' ?>">
                Pārvaldība
            </button>
            <div class="dropdown-content-admin">
                <a href="user_manager.php" class="<?= $currentPage === 'user_manager.php' ? 'active' : '' ?>">Lietotāju pārvaldība</a>
                <a href="event_manager.php" class="<?= $currentPage === 'event_manager.php' ? 'active' : '' ?>">Sludinājumu pārvaldība</a>
                <a href="admin_manager.php" class="<?= $currentPage === 'admin_manager.php' ? 'active' : '' ?>">Admina pārvaldība</a>
            </div>
        </div>

        <a href="report_manager.php" class="<?= $currentPage === 'report_manager.php' ? 'active' : '' ?>">Ziņojumi & Moderācija</a>
        <a href="#" class="<?= $currentPage === 'cms.php' ? 'active' : '' ?>">CMS</a>
        <a href="#" class="<?= $currentPage === 'settings.php' ? 'active' : '' ?>">Iestatījumi</a>
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
