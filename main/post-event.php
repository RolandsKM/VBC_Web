<?php
include 'header.php'
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
   
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../database/script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style2.css">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<section id="my-event">

    <div class="col-md-4 col-12">
        <div class="box bg-white p-3 text-center h-100 d-flex flex-column align-items-center">
            <div class="avatar mb-3">
                <img src="avatar.png" class="img-fluid rounded-circle" alt="Avatar" width="100">
            </div>
            <div class="user-name fw-bold">Lietotājvārds</div>
            <button class="btn mt-3">Rakstīt</button>
        </div>
    </div>

    <div class="event-info">
        <div class="event-header">
            <h3 class="title">Pasākuma Nosaukums</h3>
        </div>
        <p>Pilsēta: Rīga</p>
        <div class="description">
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Sapiente fuga, culpa fugit assumenda expedita ipsum ut eligendi blanditiis animi at doloremque? Ipsam animi distinctio, neque quos tempora dolor ut officia.</p>
        </div>
        <div class="location">
            Dzintaru iela 88
        </div>
        <p>Datums/Laiks: 2025-04-05 18:00</p>
        <button class="join">Pieteikties</button>
    </div>
   
</section>
</body>
</html>
