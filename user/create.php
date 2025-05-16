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
   <!-- create.php -->
<section id="create" class="container">
    <h1>Sadarbojies ar vietējiem!</h1>
    <h2>Izveido sludinājumu/pasākumu!</h2>
    <form id="event-form" class="shadow p-3">
        <div class="about-info">
            <div class="col">
                <label>Nosaukums</label>
                <input type="text" id="event-title" class="form-control" placeholder="Nosaukums..." required>
            </div>
            <div class="col">
                <label>Apraksts</label>
                <p>Apraksti savu sludinājumu/pasākumu plaši brīvprātīgajiem!</p>
                <textarea id="event-description" class="form-control" rows="6" placeholder="Apraksts..." required></textarea>
            </div>
            <div class="row">
                <div class="form-group col-md-4">
                    <label>Adrese</label>
                    <input type="text" id="event-location" class="form-control" placeholder="Adrese..." required>
                </div>
                <div class="form-group col-md-4">
                    <label>Pilsēta/Novads</label>
                    <select id="event-city" class="form-control">
                        <option selected>Izvēlies...</option>
                        <option>Rīga</option>
                        <option>Liepāja</option>
                        <option>Ventspils</option>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Pasta indekss</label>
                    <input type="text" id="event-zip" class="form-control" placeholder="LV-0000" required>
                </div>
            </div>

            <div class="form-group">
                <label>Kategorijas</label>
                <select id="event-categories" class="form-control">
                    <option value="">Ielādē kategorijas...</option>
                </select>
            </div>

            <div class="form-group">
                <label>Datums</label>
                <input type="date" id="event-date" class="form-control" required>
            </div>
        </div>
        <button type="submit">Izveidot</button>
    </form>
</section>



</body>
</html>
