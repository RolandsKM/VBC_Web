<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$is_category_page = in_array($current_page, ['category.php', 'posts.php']);
$logged_in = isset($_SESSION['username']);
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : ''; // Assuming the user's role is stored in session
?>

<link rel="stylesheet" href="style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

<header>
    <nav class="d-flex justify-content-between align-items-center">
        <button class="hamburger" onclick="toggleMenu()">☰</button>
        
        <div class="nav-links" id="navLinks">
            <ul class="nav-left">
                <li><a href="<?= ($_SERVER['PHP_SELF'] == '/user/index.php' || strpos($_SERVER['PHP_SELF'], '/user/') !== false) ? '../main/index.php' : 'index.php' ?>" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Sākums</a></li>
                <li><a href="<?= ($_SERVER['PHP_SELF'] == '/user/category.php' || strpos($_SERVER['PHP_SELF'], '/user/') !== false) ? '../main/category.php' : 'category.php' ?>" class="<?= $is_category_page ? 'active' : '' ?>">Kategorijas</a></li>
                <li><a href="index.php#about" class="<?= $current_page == 'index.php#about' ? 'active' : '' ?>">Par Mums</a></li>
            </ul>
            <ul class="nav-right">
                <?php if ($logged_in): ?>
                    <li class="dropdown">
                        <a href="#" class="dropbtn"><?= htmlspecialchars($_SESSION['username']) ?> <i class="fa-solid fa-angle-down"></i></a>
                        <div class="dropdown-content">
                            <a href="../user/user.php">Profils</a>
                            <?php if ($user_role === 'admin'): ?>
                                <a href="../admin/index.php">Admin</a> <!-- Admin option for admins -->
                            <?php endif; ?>
                            <a href="../database/logout.php" class="text-danger">Izlogoties</a>
                        </div>
                    </li>
                <?php else: ?>
                    <li><a href="login.php" class="<?= $current_page == 'login.php' ? 'active' : '' ?>">Pieslēgties</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
</header>

<script>
    function toggleMenu() {
        document.getElementById("navLinks").classList.toggle("show");
    }
</script>

<style>
   .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropbtn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 1rem;
        color: #fff;
        padding: .5rem 1rem;
        background: #45a049;
        
        transition: 0.3s;
    }
    .dropbtn:hover{
        color: #fff;
        font-weight: bold;
    }
    .dropdown-content {
        display: none;
        position: absolute;
        background-color: white;
        box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.2);
        min-width: 150px;
        z-index: 1;
        border-radius: 5px;
    }

    .dropdown-content a {
        color: black;
        padding: 10px;
        text-decoration: none;
        display: block;
        transition: 0.3s;
    }

    .dropdown-content a:hover {
        background-color: #f1f1f1;
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }
    nav ul li a.active {
    border-bottom: .1rem solid #4CAF50;
    color: #45a049;
    font-weight: bold;
}

</style>
