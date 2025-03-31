<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);
$is_category_page = in_array($current_page, ['category.php', 'posts.php']);

$logged_in = isset($_SESSION['username']);
?>

<link rel="stylesheet" href="style.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

<header>
    <h1>Vietējais Brīvprātīgais Centrs</h1>
    <nav>
        <ul>
            <li><a href="index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Sākums</a></li>
            <li><a href="category.php" class="<?= $is_category_page ? 'active' : '' ?>">Kategorijas</a></li>
            <li><a href="about.php" class="<?= $current_page == 'about.php' ? 'active' : '' ?>">Par Mums</a></li>

            <?php if ($logged_in): ?>
              
                <li class="dropdown">
                    <a href="#" class="dropbtn"><?= htmlspecialchars($_SESSION['username']) ?> ▼</a>
                    <div class="dropdown-content">
                        <a href="../user/profile.php">Profils</a>
                        <a href="../database/logout.php" class="text-danger">Izlogoties</a>
                    </div>
                </li>
            <?php else: ?>
              
                <li><a href="login.php" class="<?= $current_page == 'login.php' ? 'active' : '' ?>">Pieslēgties</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>


<style>
   .dropdown {
        position: relative;
        display: inline-block;
    }

    .dropbtn {
        background: none;
        border: none;
        cursor: pointer;
        font-size: 16px;
        color: black;
        padding: 10px 15px;
        border-radius: 5px;
        transition: 0.3s;
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
        background-color: #4CAF50;
        color: white;
        font-weight: bold;
    }
</style>
