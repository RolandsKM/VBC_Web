<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../css/templates/header.php';  ?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../functions/script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body id="cb">
   
    <section id="create-top" class="hero-section">
        <div class="container">
            <h1 class="hero-title">Sadarbojies ar vietējiem!</h1>
            <h2 class="hero-subtitle">Izveido sludinājumu/pasākumu</h2>
            <div class="hero-divider"></div>
        </div>
    </section>

   
    <section id="create" class="container">
        <div class="form-card">
            <form id="event-form" class="p-4">
              
                <div class="form-section">
                    <div class="form-group">
                        <label class="form-label">Nosaukums</label>
                        <input type="text" id="event-title" class="form-control form-input" placeholder="Ievadiet pasākuma nosaukumu..." required>
                    </div>
                </div>

              
                <div class="form-section">
                    <div class="form-group">
                        <label class="form-label">Apraksts</label>
                        <p class="form-hint">Apraksti savu sludinājumu/pasākumu plaši brīvprātīgajiem!</p>
                        <textarea id="event-description" class="form-control form-textarea" rows="6" placeholder="Detalizēts apraksts par pasākumu..." required></textarea>
                    </div>
                </div>

               
                <div class="form-section">
                    <h3 class="section-title">Atrašanās vieta</h3>
                    <div class="row">
                        <div class="form-group col-md-4">
                            <label class="form-label">Adrese</label>
                            <input type="text" id="event-location" class="form-control form-input" placeholder="Ielas nosaukums, mājas nr." required>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label">Pilsēta/Novads</label>
                            <select id="event-city" class="form-control form-select">
                                <option selected disabled>Izvēlieties pilsētu...</option>
                                <option>Rīga</option>
                                <option>Liepāja</option>
                                <option>Ventspils</option>
                                <option>Jelgava</option>
                                <option>Daugavpils</option>
                                <option>Valmiera</option>
                                <option>Jūrmala</option>
                                <option>Cēsis</option>
                                <option>Rēzekne</option>
                            </select>
                        </div>
                        <div class="form-group col-md-4">
                            <label class="form-label">Pasta indekss</label>
                            <input type="text" id="event-zip" class="form-control form-input" placeholder="LV-0000" pattern="LV-[0-9]{4}" required>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3 class="section-title">Kategorijas</h3>
                    <p class="form-hint">Izvēlieties vienu vai vairākas kategorijas, kas raksturo jūsu pasākumu</p>
                    <div id="category-cards" class="category-grid"></div>
                    <input type="hidden" id="event-categories" name="event-categories">
                </div>

                <div class="form-section">
                    <div class="form-group">
                        <label class="form-label">Datums</label>
                        <input type="date" id="event-date" class="form-control form-input" required>
                    </div>
                </div>


                <div class="form-actions">
                    <button class="btn btn-primary" type="submit">
                        <i class="fas fa-plus-circle me-2"></i>Izveidot pasākumu
                    </button>
                </div>
            </form>
        </div>
    </section>

    <?php include '../main/footer.php'; ?>

    <style>
 
        :root {
            --primary-color: #2E7D32;
            --primary-light: #4CAF50;
            --primary-dark: #1B5E20;
            --secondary-color: #FFC107;
            --text-color: #333;
            --light-gray: #f8f9fa;
            --medium-gray: #e9ecef;
            --dark-gray: #495057;
            --white: #ffffff;
            --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s ease;
        }



    </style>
</body>
</html>