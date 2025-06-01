<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="settings-aside">
    <h3>Iestatījumi</h3>
    
    <nav class="settings-nav">
        <a href="account_info.php" class="<?= $current_page === 'account_info.php' ? 'active' : '' ?>">
            <i class="bi bi-person me-2"></i>Konta informācija
        </a>
        <a href="change_password.php" class="<?= $current_page === 'change_password.php' ? 'active' : '' ?>">
            <i class="bi bi-lock me-2"></i>Parole
        </a>
        <!-- <a href="#" class="<?= $current_page === 'history.php' ? 'active' : '' ?>">
            <i class="bi bi-clock-history me-2"></i>Vēsture
        </a> -->
        <a href="../database/auth_functions.php?logout=1">
            <i class="bi bi-box-arrow-right me-2"></i>Iziet
        </a>
    </nav>
</aside>