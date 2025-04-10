<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../main/header.php';
require_once '../database/con_db.php';


$event_id = $_GET['id'];

$query = "SELECT * FROM Events WHERE ID_Event = ?";
$stmt = $savienojums->prepare($query);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Sludinājums nav atrasts!");
}

$event = $result->fetch_assoc();
$stmt->close();
$savienojums->close();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($event['title']); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../database/script.js"></script>
</head>
<body>

<section id="my-event">
    <div class="joined-event">
        <div class="joined-amount">
            <h3>Pieteikušies:</h3>
            <p>13</p> 
        </div>
        <div class="who-joined">
            <table></table>
        </div>
    </div>

    <div class="container">
        <a href="javascript:history.back()" class="btn mb-3">⬅ Atpakaļ</a>

        <div class="card shadow p-4">
            <div class="event-icons">
                <i class="bi bi-pencil edit-event-btn btn btn-outline-primary"></i>
                <i class="bi bi-trash edit-event-btn btn btn-outline-primary"></i>
            </div>

            <h1 class="title"><?= htmlspecialchars($event['title']) ?></h1>
            <p class="category"><strong>🏷️ Kategorija:</strong> <?= htmlspecialchars($event['category_name']) ?></p>
            <p class="location"><strong>📍 Pilsēta:</strong> <?= htmlspecialchars($event['city']) ?> | Zip: <?= htmlspecialchars($event['zip']) ?></p>
            <p class="description"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
            <p class="date"><strong>🗓 Datums:</strong> <?= date("d.m.Y H:i", strtotime($event['date'])); ?></p>
        </div>
    </div>

    
</section>
<div class="edit-pop-up">
    <div class="edit-pop-up-content">
        <div class="popup-header">
            <h3>Rediģēt sludinājumu</h3>
            <span class="close-edit-btn">&times;</span>
        </div>
        <form id="edit-event-form">
            <input type="hidden" id="edit-event-id" value="<?php echo $event_id; ?>">

            <div class="form-group">
                <label>Nosaukums</label>
                <input type="text" id="edit-event-title" class="form-control" value="<?php echo htmlspecialchars($event['title']); ?>" required>
            </div>

            <div class="form-group">
                <label>Apraksts</label>
                <textarea id="edit-event-description" class="form-control" rows="6" required><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Adrese</label>
                <input type="text" id="edit-event-location" class="form-control" value="<?php echo htmlspecialchars($event['location']); ?>" required>
            </div>

          
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Pilsēta</label>
                        <input type="text" id="edit-event-city" class="form-control" value="<?php echo htmlspecialchars($event['city']); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Pasta indekss</label>
                        <input type="text" id="edit-event-zip" class="form-control" value="<?php echo htmlspecialchars($event['zip']); ?>" required>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label>Datums</label>
                <input type="date" id="edit-event-date" class="form-control" value="<?php echo date("Y-m-d", strtotime($event['date'])); ?>" required>
            </div>

            <button type="submit" class="btn btn-success">Saglabāt izmaiņas</button>
        </form>
    </div>
</div>



</body>
</html>
