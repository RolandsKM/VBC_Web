<?php include '../main/header.php'; ?>

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
                <button class="btn btn-outline-secondary settings-btn">⚙</button>
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
            <button>Izveidot</button>
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

<div class="pop-up-creat">
    <div class="pop-up-content">
        <span class="close-btn">&times;</span>
        <form id="event-form">
            <div class="about-info">
                <div class="col">
                    <label>Nosaukums</label>
                    <input type="text" id="event-title" class="form-control" placeholder="Nosaukums..." required>
                </div>
                <div class="col">
                    <label>Apraksts</label>
                    <textarea id="event-description" class="form-control" rows="6" placeholder="Apraksts..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Adrese</label>
                    <input type="text" id="event-location" class="form-control" placeholder="Adrese..." required>
                </div>
                <div class="form-row d-flex">
                    <div class="form-group col-md-8 me-3">
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
    </div>
</div>






</body>
</html>
