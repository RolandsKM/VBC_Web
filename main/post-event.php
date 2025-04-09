<?php
session_start();
require_once '../database/fetch_events_from_categories.php';
include 'header.php';

$eventId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($eventId <= 0) {
    echo "<p>Pasākums nav atrasts.</p>";
    exit;
}

$event = fetchEventData($eventId);

if (!$event) {
    echo "<p>Pasākums nav atrasts.</p>";
    exit;
}

$eventDate = date("d.m.Y", strtotime($event['date']));
$isLoggedIn = isset($_SESSION['ID_user']);

?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($event['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style-post.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <script>
        var userId = <?= $isLoggedIn ? $_SESSION['ID_user'] : 'null' ?>;
        var eventId = <?= $eventId ?>;
    </script>

    <script src="../database/script.js" defer></script> 
</head>
<body>
    <div class="container">
        <a href="javascript:history.back()" class="btn mb-3">⬅ Atpakaļ</a>

        <div class="card shadow p-4">
            <h1><?= htmlspecialchars($event['title']) ?></h1>
            <p><strong>🏷️ Kategorija:</strong> <?= htmlspecialchars($event['category_name']) ?></p>
            <p><strong>📍 Pilsēta:</strong> <?= htmlspecialchars($event['city']) ?> | Zip: <?= htmlspecialchars($event['zip']) ?></p>
            <hr>
            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
            <hr>
            <p><strong>🗓 Datums:</strong> <?= $eventDate ?></p>
        </div>
    </div>

    <section id="info">
        <div class="container">
            <div class="row">
                
                <div class="col-md-6">
                    <div class="card shadow p-3">
                        <div class="d-flex align-items-center">
                            <img src="<?= htmlspecialchars($event['profile_pic']) ?>" alt="User Profile Picture" class="rounded-circle" width="100" height="100">
                            <div class="ms-3">
                                <h5 class="mb-0 text-start"><?= htmlspecialchars($event['username']) ?></h5>
                                <p class="text-muted mb-0 text-start"><?= htmlspecialchars($event['email']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="col-md-6">
                    <div class="card p-3 text-center">
                    
                        <?php if (!$isLoggedIn): ?>
                            <p class="text-danger">Lūdzu, piesakieties, lai pievienotos pasākumam.</p>
                        <?php else: ?>
                            <button id="applyButton" class="btn btn-success">Pieteikties</button>
                            <button id="msgButton" class="btn btn-msg">Rakstīt</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</body>
</html>
