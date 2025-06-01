<?php 

include '../css/templates/header.php'; 
  include '../functions/get_categories_count.php'; 
include '../config/con_db.php';

// Get count of upcoming events (date > NOW and not deleted)
$stmtEvents = $pdo->prepare("SELECT COUNT(*) as count FROM Events WHERE date > NOW() AND deleted = 0");
$stmtEvents->execute();
$upcomingEvents = $stmtEvents->fetch()['count'] ?? 0;

// Get count of active users (not banned)
$stmtUsers = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE banned = 0");
$stmtUsers->execute();
$activeUsers = $stmtUsers->fetch()['count'] ?? 0;

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
   
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script> 
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
/* Stats Section */
:root {
  --primary-color: #4CAF50;
  --primary-dark: #3e8e41;
  --secondary-color: #2196F3;
  --secondary-dark: #0b7dda;
  --accent-color: #FF9800;
  --dark-color: #333;
  --light-color: #f8f9fa;
  --gray-color: #6c757d;
  --light-gray: #e9ecef;
  --white: #fff;
  --black: #000;
  --box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
  --transition: all 0.3s ease;
}

/* Existing styles (for reference) */
.stats-section {
  background-color: var(--primary-color);
  color: var(--white);
  padding: 3rem 0;
  text-align: center;
}

.stats-container {
  display: flex;
  justify-content: space-around;
  flex-wrap: nowrap; /* Keep next to each other */
  gap: 30px;
}

.stat-item {
  flex: 1;
  min-width: 150px;
}

.stat-number {
  font-size: 3rem;
  font-weight: 700;
  margin-bottom: 10px;
}

.stat-label {
  font-size: 1.1rem;
}

/* Responsive tweaks */
@media (max-width: 480px) {
  .stats-section {
    padding: 2rem 1rem;
  }
  
  .stats-container {
    gap: 15px;
  }
  
  .stat-item {
    min-width: 100px;
  }
  
  .stat-number {
    font-size: 2rem;
    margin-bottom: 6px;
  }
  
  .stat-label {
    font-size: 0.9rem;
  }
}

@media (max-width: 360px) {
  .stats-section {
    padding: 1.5rem 0.5rem;
  }
  
  .stats-container {
    gap: 10px;
  }
  
  .stat-item {
    min-width: 90px;
  }
  
  .stat-number {
    font-size: 1.6rem;
    margin-bottom: 4px;
  }
  
  .stat-label {
    font-size: 0.8rem;
  }
}


</style>
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
                        <i class="<?= htmlspecialchars($category['icon']); ?>"></i>
                        <p><?= htmlspecialchars($category['Nosaukums']); ?></p>
                        <div class="amount"><?= htmlspecialchars($category['amount']); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="nav-button right" onclick="scrollCategories(1)">&#10095;</button>
        </div>
    </section>




    <section class="stats-section">
    <div class="stats-container">
        <div class="stat-item">
            <div class="stat-number" id="volunteers-count"><?= htmlspecialchars($activeUsers) ?></div>
            <div class="stat-label">Aktīvi brīvprātīgie</div>
        </div>

        <div class="stat-item">
            <div class="stat-number" id="events-count"><?= htmlspecialchars($upcomingEvents) ?></div>
            <div class="stat-label">Aktīvi sludinājumi</div>
        </div>
    </div>
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
            <?php if ($message_sent): ?>
    <div class="alert alert-success">Paldies! Ziņojums tika nosūtīts.</div>
<?php elseif ($error_message): ?>
    <div class="alert alert-danger"><?= $error_message ?></div>
<?php endif; ?>

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


<?php include '../css/templates/footer.php'; ?>


</div>
<script>
$(document).ready(function() {
    $('form').submit(function(e) {
        e.preventDefault(); 

        $.ajax({
            url: 'send_mail.php',  
            method: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    alert(response.message);
                    $('form')[0].reset(); 
                } else {
                    alert('Kļūda: ' + response.message);
                }
            },
            error: function() {
                alert('Radās neparedzēta kļūda. Lūdzu mēģiniet vēlreiz.');
            }
        });
    });
});
</script>

</body>
</html>