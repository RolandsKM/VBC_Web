<?php
include 'header.php';
$categoryId = isset($_GET['category_id']) ? $_GET['category_id'] : '';
$city = isset($_GET['city']) ? $_GET['city'] : '';
$dateFrom = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$dateTo = isset($_GET['date_to']) ? $_GET['date_to'] : '';
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
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const categoryId = "<?= $categoryId ?>";
</script>

<div class="container mt-4">
    <div class="row">
      
<aside class="col-md-3">
    <h4>Filtri</h4>
    <form id="filter_form">

    <div class="mb-3">
        <label for="filter_category" class="form-label">Kategorija</label>
        <select class="form-select" id="filter_category">
            <option value="">Izvēlies kategoriju...</option>
            
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Datums</label>
        <input type="date" class="form-control" id="date_from" value="<?= $dateFrom ?>">
        <input type="date" class="form-control mt-2" id="date_to" value="<?= $dateTo ?>">
    </div>
    
    <div class="mb-3">
    <label for="city" class="form-label">Pilsēta</label>
    <select class="form-select" id="city">
        <option value="">Izvēlies pilsētu...</option>
        <option value="riga" <?= $city == 'riga' ? 'selected' : '' ?>>Rīga</option>
        <option value="liepaja" <?= $city == 'liepaja' ? 'selected' : '' ?>>Liepāja</option>
        <option value="ventspils" <?= $city == 'ventspils' ? 'selected' : '' ?>>Ventspils</option>
    </select>
</div>

    <div class="d-flex gap-2">
       
        <button type="button" id="clear_filters" class="btn btn-secondary w-100">Notīrīt filtrus</button>
    </div>
    </form>
</aside>
        

<main class="col-md-9">
    <section id="events">
        <p>Izvēlieties kategoriju, lai redzētu pasākumus.</p>
    </section>
</main>

    </div>
</div>
</body>
</html>
