<?php
include '../functions/get_categories.php'; 
include '../css/templates/header.php'; 
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Populārās Kategorijas</title>
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js" defer></script>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<link rel="stylesheet" href="style.css">
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

<section id="categories">
    <h2>Populārās Kategorijas</h2>
    <div class="categories-container2 row">
        <?php foreach ($categories as $category): ?>
            <div class="category_k col-sm-6 col-md-4 col-lg-2 mb-1">
                <a href="posts.php?category_id=<?= $category['Kategorijas_ID']; ?>" class="category-link">
                    <div class="category-card">
                        <div class="icon" style="background: <?= htmlspecialchars($category['color']); ?>;">
                            
                            <p><i class="<?= htmlspecialchars($category['icon']); ?>"></i>
                        </div>
                        <p class="category-name"><?= htmlspecialchars($category['Nosaukums']); ?></p>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<?php include '../css/templates/footer.php'; ?>


</body>
</html>
