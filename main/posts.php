<?php
include 'header.php';
include '../database/get_events.php'; // Function to fetch events

$category_id = isset($_GET['category_id']) ? (int) $_GET['category_id'] : 0;
$events = getEventsByCategory($category_id);
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js" defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<div class="container mt-4">
    <div class="row">
      
<aside class="col-md-3">
    <h4>Filtri</h4>
    <form>
        <?php
            include '../database/get_categories.php'; 
        ?>


    <div class="mb-3">
        <label for="category" class="form-label">Kategorija</label>
        <select class="form-select" id="category" onchange="filterByCategory(this.value)">
            <option value="0">Izvēlies kategoriju...</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['Kategorijas_ID']; ?>" <?= ($category['Kategorijas_ID'] == $category_id) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($category['Nosaukums']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <script>
    function filterByCategory(categoryId) {
        if (categoryId > 0) {
            window.location.href = 'posts.php?category_id=' + categoryId;
        }
    }
    </script>

    <div class="mb-3">
        <label class="form-label">Datums</label>
        <input type="date" class="form-control" id="date_from" placeholder="No">
        <input type="date" class="form-control mt-2" id="date_to" placeholder="Līdz">
    </div>
    
    
    <div class="mb-3">
        <label for="city" class="form-label">Pilsēta</label>
        <select class="form-select" id="city">
            <option selected>Izvēlies pilsētu...</option>
            <option value="riga">Rīga</option>
            <option value="liepaja">Liepāja</option>
            <option value="ventspils">Ventspils</option>
        </select>
    </div>
        
     
        <div class="mb-3">
            <label class="form-label">Tagi</label>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-primary">Brīvprātīgais</button>
                <button type="button" class="btn btn-outline-primary">Ģimenei draudzīgs</button>
                <button type="button" class="btn btn-outline-primary">Tiešsaistē</button>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary w-100">Filtrēt</button>
    </form>
</aside>
        

    <main class="col-md-9">
        <section id="events">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Pasākumi Kategorijā</h2>
                <input type="text" class="form-control w-25" id="search" placeholder="Meklēt...">
            </div>
            
            <?php if (empty($events)): ?>
                <p>Nav atrasti pasākumi šajā kategorijā.</p>
            <?php else: ?>
                <div class="row" id="event-list">
                    <?php foreach ($events as $event): ?>
                        <div class="col-md-4 mb-3 event-card">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title"> <?= htmlspecialchars($event['title']); ?> </h5>
                                    <p class="card-text"> <?= htmlspecialchars($event['description']); ?> </p>
                                    <p><strong>Lokācija:</strong> <?= htmlspecialchars($event['location']); ?></p>
                                    <p><strong>Datums:</strong> <?= htmlspecialchars($event['date']); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    </div>
</div>
</body>
</html>
