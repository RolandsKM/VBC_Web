<?php
include '../css/templates/header.php'; 

session_start();
require_once '../functions/event_functions.php';


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

$eventDate = date("d.m.Y H:i", strtotime($event['date']));

$isLoggedIn = isset($_SESSION['ID_user']);

?>

<!DOCTYPE html>
<html lang="lv">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($event['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style-post.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <script>
        var userId = <?= $isLoggedIn ? $_SESSION['ID_user'] : 'null' ?>;
        var eventId = <?= $eventId ?>;
    </script>
    <style>


    </style>
    <script src="../functions/script.js" defer></script> 
</head>
<body>
    <div id="chatSidebar" class="chat-sidebar" style="display:none;">
        <div class="chat-header">
            <h5>Čats ar <span id="chatUsername"><?= htmlspecialchars($event['username']) ?></span></h5>
            <button id="closeChatBtn" class="btn btn-sm btn-outline-danger">X</button>
        </div>
        <div id="chatMessages" class="chat-messages"></div>
        <textarea id="chatInput" placeholder="Rakstiet ziņu..." rows="3"></textarea>
        <button id="sendChatBtn" class="btn btn-primary">Sūtīt</button>
    </div>


    <div class="container first shadow">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="javascript:history.back()" class="btn">⬅ Atpakaļ</a>
            <?php if ($isLoggedIn && $_SESSION['ID_user'] != $event['user_id']): ?>
                <button id="reportBtn" class="btn btn-warning">
                    <i class="fa-solid fa-flag"></i>
                </button>
            <?php endif; ?>
        </div>

        <div class=" p-4">


            <h1><?= htmlspecialchars($event['title']) ?></h1>
            <!-- <p><strong>🏷️ Kategorija:</strong> <?= htmlspecialchars($event['category_name']) ?></p> -->
            
            <hr>
            <p><?= nl2br(htmlspecialchars($event['description'])) ?></p>
            <hr>
            
        </div>
    </div>

    <section id="info">
        <div class="container">
            <div class=" d-flex flex-wrap gap-3 mb-3">
                <div class="small-box">
                    <p><strong>Atrašanās vieta:</strong> <?= htmlspecialchars($event['city']) ?>, <?= htmlspecialchars($event['location']) ?> | Zip: <?= htmlspecialchars($event['zip']) ?></p>
                </div>
                <div class="small-box">
                    <p><strong>🗓 Datums:</strong> <?= $eventDate ?></p>
                </div>
                <div class="small-box ms-auto">
                    <p><strong>Pievienojusies:</strong> <?= intval($event['accepted_count']) ?> cilvēki</p>
                </div>

                
            </div>
        <hr>
        <div class="top row g-4">
            <div class="col-12 col-md-6">

                    <div class=" p-3">
                            <div class="d-flex align-items-center">
                                <img src="../functions/assets/<?= htmlspecialchars($event['profile_pic']) ?>" alt="User Profile Picture" class="rounded-circle" width="100" height="100">
                                
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
<div id="reportModalOverlay"></div>
<div id="reportModal">
    <h3>Ziņot par pasākumu</h3>
    <p>Lūdzu, izvēlieties iemeslu:</p>
    
    <div class="report-option">
        <input type="radio" id="reason1" name="reportReason" value="Nepareiza atrašanās vieta">
        <label for="reason1">Nepareiza atrašanās vieta</label>
    </div>
    
    <div class="report-option">
        <input type="radio" id="reason2" name="reportReason" value="Nepareizs datums/laiks">
        <label for="reason2">Nepareizs datums/laiks</label>
    </div>
    
    <div class="report-option">
        <input type="radio" id="reason3" name="reportReason" value="Aizvainojošs saturs">
        <label for="reason3">Aizvainojošs saturs</label>
    </div>
    
    <div class="report-option">
        <input type="radio" id="reason4" name="reportReason" value="Mākslīgais pasākums">
        <label for="reason4">Mākslīgais pasākums</label>
    </div>
    
    <div class="report-option">
        <input type="radio" id="reason5" name="reportReason" value="Citi">
        <label for="reason5">Citi</label>
    </div>
    
    <textarea id="reportCustomReason" placeholder="Lūdzu, aprakstiet problēmu..." class="form-control"></textarea>
    
    <div id="reportModalButtons">
        <button id="cancelReport" class="btn btn-outline-secondary">Atcelt</button>
        <button id="submitReport" class="btn btn-danger">Iesniegt ziņojumu</button>
    </div>
</div>

    <script>
        const APP_DATA = {
            userId: <?= $isLoggedIn ? $_SESSION['ID_user'] : 'null' ?>,
            eventUserId: <?= json_encode($event['user_id']) ?>,
            eventId: <?= $eventId ?>
        };
    </script>
<?php include '../css/templates/footer.php'; ?>

</body>
</html>
