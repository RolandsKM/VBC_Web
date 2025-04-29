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
<body>

<section id="info" class="container py-5">
    <div class="row g-4"> 
     
        <div class="col-md-4 col-12">
            <div class="box bg-white p-3 text-center h-100 d-flex flex-column align-items-center">
                <div class="avatar mb-3">
                    <img src="avatar.png" class="img-fluid rounded-circle" alt="Avatar" width="100">
                </div>
                <div class="user-name fw-bold">Lietotājvārds</div>
                <button class="btn mt-3">Rakstīt</button>
            </div>
        </div>

       
        <div class="col-md-8 col-12">
            <div class="box bg-white p-3 position-relative h-100">
                <a href="setting.php" class="btn btn-outline-secondary settings-btn">⚙</a>
                <div class="user-info">
                    <p><strong>E-pasts:</strong> example@mail.com</p>
                    <p><strong>Vārds:</strong> Jānis</p>
                    <p><strong>Uzvārds:</strong> Bērziņš</p>
                    <p><strong>Pilsēta:</strong> Rīga</p>
                    <p><strong>Bio:</strong> Brīvprātīgais un sabiedriskais darbinieks.</p>
                    <p><strong>Social:</strong> @example</p>
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
            <!-- Redirect to create.php instead of showing pop-up -->
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
