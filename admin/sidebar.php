<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<aside>
    <h3>VBC - ADMIN</h3>
    <hr>
    <nav class="d-flex flex-column">

        <a href="index.php" class="py-2 px-3 text-decoration-none border-bottom <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Dashboard</a>

        <div class="dropdown-container">
            <button class="dropdown-toggle2 py-2 px-3 text-decoration-none border-bottom text-start w-100">
                Pārvaldība 
            </button>
            <div class="dropdown-content2 flex-column" style="display: none;">
                <a href="user_manager.php" class="py-2 ps-4 text-decoration-none <?= basename($_SERVER['PHP_SELF']) == 'user_manager.php' ? 'active' : '' ?>">Lietotāju pārvaldība</a>

                <a href="event_manager.php" class="py-2 ps-4 text-decoration-none <?= basename($_SERVER['PHP_SELF']) == 'event_manager.php' ? 'active' : '' ?>">Sludinājuma pārvaldība</a>

                <a href="#" class="py-2 ps-4 text-decoration-none">Admina pārvaldība</a>
            </div>
        </div>

        <a href="#" class="py-2 px-3 text-decoration-none border-bottom">Ziņojumi & Moderācija</a>
        <a href="#" class="py-2 px-3 text-decoration-none border-bottom">CMS</a>
        <a href="#" class="py-2 px-3 text-decoration-none border-bottom">Setting</a>
        
        <a href="../database/auth_functions.php?logout=1" class="py-2 px-3 text-decoration-none border-bottom">Logout</a>
    </nav>
</aside>

<script>
    const dropdownToggle = document.querySelector('.dropdown-toggle2');
    const dropdownContent = document.querySelector('.dropdown-content2');

    dropdownToggle.addEventListener('click', () => {
        const isVisible = dropdownContent.style.display === 'flex';
        dropdownContent.style.display = isVisible ? 'none' : 'flex';
        dropdownToggle.classList.toggle('active', !isVisible);
    });
</script>


<style>
.dropdown-toggle2 {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 1rem;
    transition: 0.3s;
}

.dropdown-content2 a {
    padding-left: 2rem; 
}
</style>
