<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['ID_user']) || !in_array($_SESSION['role'], ['admin', 'mod', 'supper-admin'])) {
    header("Location: ../main/login.php");
    exit();
}
?>


<header class="admin-header d-flex justify-content-between align-items-center px-4">
    <a href="../main/index.php" class="btn">VBC-Sadaļa</a>

    <div class="dropdown">
        <button class="dropbtn">
            <?= htmlspecialchars($_SESSION['username']) ?> <i class="fa-solid fa-angle-down"></i>
        </button>
        <div class="dropdown-content">
            <a href="../user/">Profils</a>
            <?php if (in_array($_SESSION['role'], ['admin', 'mod', 'supper-admin'])): ?>
                <a href="index.php">Admin</a>
            <?php endif; ?>
            <a href="../functions/auth_functions.php?logout=1" class="text-danger">Izlogoties</a>
        </div>
    </div>
</header>
