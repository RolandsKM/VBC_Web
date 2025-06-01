<?php 
include '../css/templates/header.php'; 
include '../functions/get_categories_count.php'; 
include '../config/con_db.php';


$stmtEvents = $pdo->prepare("SELECT COUNT(*) as count FROM Events WHERE date > NOW() AND deleted = 0");
$stmtEvents->execute();
$upcomingEvents = $stmtEvents->fetch()['count'] ?? 0;

$stmtUsers = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE banned = 0");
$stmtUsers->execute();
$activeUsers = $stmtUsers->fetch()['count'] ?? 0;

$stmtPopularEvents = $pdo->prepare("
    SELECT e.*, COUNT(v.ID_Volunteers) AS volunteer_count
    FROM Events e
    LEFT JOIN Volunteers v ON e.ID_Event = v.event_id AND v.status IN ('waiting', 'accepted')
    WHERE e.date > NOW() AND e.deleted = 0
    GROUP BY e.ID_Event
    ORDER BY volunteer_count DESC, e.created_at DESC
    LIMIT 3
");
$stmtPopularEvents->execute();
$popularEvents = $stmtPopularEvents->fetchAll();

?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
   
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script> 
    
    <script src="script.js" defer></script> 

</head>

<body>
<div id="app">
    <!-- Hero Section -->
    <section class="hero">
        <h1>Sveicināti <strong>Vietējo brīvprātīgo centrā!</strong></h1>
        <p>Nepieciešama palīdzība kopienas projektā vai vēlaties palīdzēt? Mūsu platforma ļauj jums izveidot un kopīgot plakātus jebkuram nolūkam — vai tas ir labdarības pasākuma organizēšana, palīdzība kaimiņam vai vietējās iniciatīvas atbalstīšana. Publicējiet savu pieprasījumu, un brīvprātīgie jūsu reģionā varēs jūs atrast un pievienoties. Kopā mēs varam kaut ko mainīt!</p>
        <?php if (!isset($_SESSION['username'])): ?>
            <a href="register.php">Pieteikties</a>
        <?php endif; ?>
    </section>
<section id="categories">
  <h2>Populārās Kategorijas</h2>

  <div class="categories-container" id="categories-container">
    <button class="nav-button left" onclick="scrollCategories(-1)">&#10094;</button>

    <div class="category-scroll-wrapper">
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
    </div>

    <button class="nav-button right" onclick="scrollCategories(1)">&#10095;</button>
  </div>
</section>
    <!-- Popular Events Section -->
    <section id="popular-events">
        <h2>Populārie Pasākumi</h2>
        <div class="events-container">
            <?php foreach ($popularEvents as $event): ?>
              <a href="post-event.php?id=<?= $event['ID_Event'] ?>" class="event-card-link">
                <div class="event-card">
                    <div class="event-image" style="background-image: url('../images/event-placeholder.jpg');">
                        <div class="event-date">
                            <?= date('d.m.Y', strtotime($event['date'])) ?>
                        </div>
                    </div>
                    <div class="event-content">
                        <h3><?= htmlspecialchars($event['title']) ?></h3>
                        <p><?= htmlspecialchars(substr($event['description'], 0, 100)) ?>...</p>
                        <div class="event-meta">
                            <span><i class="far fa-calendar-alt"></i> Publicēts: <?= date('d.m.Y', strtotime($event['created_at'])) ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['location']) ?></span>
                        </div>
                    </div>
                </div>
              </a>
            <?php endforeach; ?>
            
        </div>
        <div class="view-more">
            <a href="posts.php">Skatīt visus pasākumus</a>
        </div>
    </section>

    <!-- Stats Section -->
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
            <div class="stat-item">
                <div class="stat-number"><?= count($categories) ?></div>
                <div class="stat-label">Kategorijas</div>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section class="about-us" id="about">
        <h1>Par Mums2</h1>
        <div class="about-us-content">
            <div class="about-us-info">
                <p>
                    Mēs esam <strong>Vietējais Brīvprātīgais Centrs</strong> — digitāla platforma, kas veidota, lai palīdzētu cilvēkiem viegli atrast un piedāvāt brīvprātīgās palīdzības iespējas. Mūsu mērķis ir veicināt sabiedrības līdzdalību, sadarbību un saliedētību, savienojot tos, kuriem ir vajadzīga palīdzība, ar tiem, kuri vēlas palīdzēt.
                </p>
                <p>
                    Platforma piedāvā ērtu veidu, kā cilvēki var publicēt sludinājumus par palīdzības nepieciešamību vai piedāvājumu. Mēs ticam, ka nelielas labas darbības var radīt lielas izmaiņas mūsu kopienā.
                </p>
                <p>
                    Pievienojieties mums šodien un kļūstiet par daļu no pozitīvās pārmaiņu kustības savā apkaimē!
                </p>
            </div>
            <div class="about-us-picture">
                <img src="../images/group-hands.png" alt="Brīvprātīgie cilvēki ar sakrustotām rokām">
            </div>
        </div>
    </section>

    <!-- Contacts Section -->
    <section class="contact">
        <div class="contacts-container">
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
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="name" name="name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="surname">Uzvārds</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="surname" name="surname" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">E-pasts</label>
                    <div class="input-icon-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="message">Ziņojums</label>
                    <textarea id="message" name="message" rows="5" required placeholder="Kāpēc rakstat mums?"></textarea>
                </div>

                <button type="submit">Nosūtīt</button>
                </form>
            </div>


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
    

    function animateValue(id, start, end, duration) {
        const obj = document.getElementById(id);
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    }
    
    function isInViewport(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }
    
   
    $(window).scroll(function() {
        if (isInViewport(document.getElementById('volunteers-count'))) {
            animateValue('volunteers-count', 0, <?= $activeUsers ?>, 1000);
            animateValue('events-count', 0, <?= $upcomingEvents ?>, 1000);
      
            $(window).off('scroll');
        }
    });
 
    if (isInViewport(document.getElementById('volunteers-count'))) {
        animateValue('volunteers-count', 0, <?= $activeUsers ?>, 1000);
        animateValue('events-count', 0, <?= $upcomingEvents ?>, 1000);
    }
});
</script>
</body>
</html>




