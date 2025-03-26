<?php 
session_start(); 

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../main/login.php"); // Redirect to login page
    exit();
}

include '../main/header.php'; 
?>


<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs-Profils</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js" defer></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script> 
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<header>
    <h1>Vietējais Brīvprātīgais Centrs</h1>
    <nav>
        <ul>
            <li><a href="../main/index.php" class="<?= $current_page == 'index.php' ? 'active' : '' ?>">Sākums</a></li>
            <li><a href="../main/category.php" class="<?= $is_category_page ? 'active' : '' ?>">Kategorijas</a></li>
            <li><a href="../main/about.php" class="<?= $current_page == 'about.php' ? 'active' : '' ?>">Par Mums</a></li>

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
<section style="background-color: #eee;">
  <div class="container py-5">


    <div class="row">
      <div class="col-lg-4">
        <div class="card mb-4">
          <div class="card-body text-center">
            <img src="https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava3.webp" alt="avatar"
              class="rounded-circle img-fluid" style="width: 150px;">
            <h5 class="my-3">John Smith</h5>
            <p class="text-muted mb-1">Full Stack Developer</p>
            <p class="text-muted mb-4">Bay Area, San Francisco, CA</p>
            <div class="d-flex justify-content-center mb-2">
              
              <button  type="button" data-mdb-button-init data-mdb-ripple-init class="btn btn-outline-primary ms-1">Rakstīt</button>
            </div>
          </div>
        </div>
       
      </div>
      
        
    </div>
  </div>
</section>
</body>
</html>
