<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../main/header.php'; ?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../database/script.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script> 
</head>
<body id="user">

<section id="info" class="py-5">
    <div class="container">
        <div class="profile-container d-flex align-items-start gap-4 flex-wrap">
            
            <div class="left-profile d-flex flex-column align-items-center">
                <div class="avatar mb-2">
                    <img src="avatar.png" class="img-fluid rounded-circle" alt="Avatar" width="100">
                </div>
                <p class="mb-0"><strong></strong> Lietotājs</p>
            </div>

          
            <div class="right-profile flex-grow-1">
                <div class="stats-boxes d-flex gap-4 flex-wrap">
                    <div class="stat-card box text-center">
                        <h5>Sludinājumi</h5>
                        <p>0</p>
                    </div>
                    <div class="stat-card box text-center">
                        <h5>Pieteicies</h5>
                        <p>0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id="event">
    <div class="button-box">
        <div class="action-btn">
            <button class="sludinajumi-btn active">Sludinājumi</button>
            <button class="pieteicies-btn">Pieteicies</button>
        </div>

        <div class="create-btn">
           
            <a href="create.php" class="btn">Izveidot</a>
        </div>

    </div>

    <div class="event-container">
    <!-- Sludinājumi -->
    <p>Nav sludinājuma.</p>
    </div>

    <div class="joined-container" style="display: none;">
        <p>Pagaidām nav pieteikumu.</p>
    </div>

</section>




</body>
</html>
