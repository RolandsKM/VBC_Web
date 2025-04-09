<?php include 'header.php'; 
  include '../database/get_categories_count.php'; 
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
   
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js" defer></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script> 
    <link rel="stylesheet" href="style.css">
</head>
<body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>

<div id="app">
   
    <section class="hero">
        <h1>Sveicināti <strong>Vietējo brīvprātīgo centrā!</strong></h1>
        <p>Nepieciešama palīdzība kopienas projektā vai vēlaties palīdzēt? Mūsu platforma ļauj jums izveidot un kopīgot plakātus jebkuram nolūkam — vai tas ir labdarības pasākuma organizēšana, palīdzība kaimiņam vai vietējās iniciatīvas atbalstīšana. Publicējiet savu pieprasījumu, un brīvprātīgie jūsu reģionā varēs jūs atrast un pievienoties. Kopā mēs varam kaut ko mainīt!</p>
        <a href="register.php">Pieteikties</a>
    </section>

    <section id="categories">
        <h2>Populārās Kategorijas</h2>
        
        <div class="categories-container" id="categories-container">
            <button class="nav-button left" onclick="scrollCategories(-1)">&#10094;</button>
            <div class="category-list">
                <?php foreach ($categories as $category): ?>
                    <div class="category" onclick="window.location.href='posts.php?category_id=<?= $category['Kategorijas_ID']; ?>'"
                        style="background-color: <?= htmlspecialchars($category['color']); ?>;">
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
       Šī section tiks izveidota vēlāk
    </section>

    <section class="about-us" id="about">
        <h1>Par Mums</h1>
        <div class="about-us-content">
            <div class="about-us-info">
                <p>
                    Mēs esam <strong>Vietējais Brīvprātīgais Centrs</strong> — digitāla platforma, kas veidota, lai palīdzētu cilvēkiem viegli atrast un piedāvāt brīvprātīgās palīdzības iespējas. Mūsu mērķis ir veicināt sabiedrības līdzdalību, sadarbību un saliedētību, savienojot tos, kuriem ir vajadzīga palīdzība, ar tiem, kuri vēlas palīdzēt.
                </p>
                <p>
                    Platforma piedāvā ērtu veidu, kā cilvēki var publicēt sludinājumus par palīdzības nepieciešamību vai piedāvājumu.
                </p>
  
            </div>
            <div class="about-us-picture">
                <img src="../images/group-hands.png" alt="Brīvprātīgie cilvēki ar sakrustotām rokām">
            </div>
        </div>
    </section>


    <section class="contacts">
        <div class="form">
            <h2>Sazinieties ar mums</h2>
            <form action="#" method="post">
                <div class="form-group">
                    <label for="name">Vārds</label>
                    <input type="text" id="name" name="name" required>
                </div>

                <div class="form-group">
                    <label for="surname">Uzvārds</label>
                    <input type="text" id="surname" name="surname" required>
                </div>

                <div class="form-group">
                    <label for="email">E-pasts</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="message">Ziņojums</label>
                    <textarea id="message" name="message" rows="5" required placeholder="Kāpēc rakstat mums?"></textarea>
                </div>

                <button type="submit">Nosūtīt</button>
            </form>
        </div>

        <div class="image">
            <img src="../images/contact.jpg" alt="Sazināšanās ar darbinieku bilde">
        </div>
    </section>


    <footer>
        <div class="footer-container">
            <div class="footer-box-info">
                <h2>Vietējais brīvprātīgās centrs</h2>
              
                <p>Vietējo sadarbība un kopā strādāšana, lai pastiprinātu attiecības</p>
                <p class="copy">&copy; 2024 Vietējais brīvprātīgās centrs</p>
            </div>

            <div class="footer-box-follow">
                <h3>Seko mums</h3>
                <div class="footer-icons">
                    <i class="fa-brands fa-square-facebook"></i>
                    <i class="fa-brands fa-square-x-twitter"></i>
                    <i class="fa-brands fa-square-instagram"></i>
                </div>
                <h3>Zvaniet mums</h3>
                <p>+371 11 111 111</p>
            </div>

            <div class="footer-box-comp">
                <h3>Informācija</h3>
                <p>Par mums</p>
                <p>FAQs</p>
                <p>Sazināties</p>
            </div>
        </div>
        <div class="footer-bottom">
                    <a href="">Privātuma politika</a>
                    <a href="">Lietošanas noteikumi</a>
        </div>
    </footer>

</div>
</body>
</html>