<?php include 'header.php'; 
  include '../database/get_categories_count.php'; 
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script> 
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

<div id="app">
   
    <section class="hero">
        <div class="hero-content">
            <input type="text" id="city_search" placeholder="Enter city name..." onkeyup="filterCategoriesByCity()">

            <button @click="searchHelp">Meklēt</button>
        </div>
    </section>

    <section id="categories">
        <h2>Populārās Kategorijas</h2>
        
        <div class="categories-container" id="categories-container">
            <button class="nav-button left" onclick="scrollCategories(-1)">&#10094;</button>
            <div class="category-list">
                <?php foreach ($categories as $category): ?>
                    <div class="category" onclick="window.location.href='posts.php?category_id=<?= $category['Kategorijas_ID']; ?>'">
                        <i class="fa-regular fa-sun"></i>
                        <p><?= htmlspecialchars($category['Nosaukums']); ?></p>
                        <div class="amount"><?= htmlspecialchars($category['amount']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="nav-button right" onclick="scrollCategories(1)">&#10095;</button>
        </div>
    </section>

    <section id="posters">
        <h2>Populārākie Paziņojumi</h2>
        <div class="posters-container">
            
            <div class="poster-list">
                <table class="poster-table">
                    <tr v-for="poster in posters" :key="poster.title">
                        <td class="poster" @click="showDetails(poster.title, poster.description)">
                            <div class="poster-content">
                                <div class="poster-text">
                                    <h3>{{ poster.title }}</h3>
                                    <p>{{ poster.description }}</p>
                                </div>
                                <button>Join</button>
                            </div>
                        </td>
                    </tr>
                </table>
            </div>

            <div class="poster-details">
                <h3 id="detail-title">{{ detailTitle }}</h3>
                <p id="detail-description">{{ detailDescription }}</p>
            </div>
        </div>
    </section>

    <section class="about-us">
        <h2>Par Mums</h2>
        <p>Mēs esam vietējais brīvprātīgo centrs, kas savieno cilvēkus, kuri vēlas palīdzēt, ar tiem, kam palīdzība nepieciešama. Mūsu mērķis ir veicināt sabiedrības saliedētību un atbalstīt savstarpēju palīdzību.</p>
    </section>

  
    <footer>
        <p>&copy; 2023 Vietējais Brīvprātīgais Centrs. Visas tiesības aizsargātas.</p>
    </footer>
</div>
</body>
</html>