<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$logged_in = isset($_SESSION['username']); 

if ($logged_in && isset($_SESSION['ID_user'])) {
    require_once '../config/con_db.php'; 

    $stmt = $pdo->prepare("SELECT banned FROM users WHERE ID_user = ?");
    $stmt->execute([$_SESSION['ID_user']]);
    $userStatus = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userStatus && (int)$userStatus['banned'] === 1) {
        session_unset();
        session_destroy();
        header("Location: ../main/login.php?banned=1");
        exit();
    }
}

$current_page = basename($_SERVER['PHP_SELF']);
$is_category_page = in_array($current_page, ['category.php', 'posts.php']);
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
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
                            <a href="../database/auth_functions.php?logout=1" class="text-danger">Izlogoties</a>
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

header {
    background-color: #ffffff;
    color: #333;
    padding: 1.5rem 12%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
}


body {
    padding-top: 80px;
}


header nav ul {
    list-style: none;
    display: flex;
    gap: 1rem;
    margin: 0;
    padding: 0;
}

header nav {
    width: 100%;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

header .nav-left {
    display: flex;
    gap: 1rem;
    flex-grow: 1;
}

header .nav-right {
    display: flex;
    gap: 1rem;
  
    padding: .5rem;
}


header nav ul li {
    margin: 0;
}

nav ul li a {
    color: #383838;
    font-weight: 100;
    padding: 0.5rem 1rem;
    text-decoration: none;
}

nav ul li a.active {
    border-bottom: .1rem solid #4CAF50;
    color: #45a049;
    font-weight: bold;
}

/* Navigation wrapper for mobile */
.nav-links {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}



.nav-right {
    display: flex;
    gap: 1rem;
    margin-left: auto;
}

/* Hamburger Button */
.hamburger {
    display: none;
    font-size: 2rem;
    background: none;
    border: none;
    cursor: pointer;
}

</style>
