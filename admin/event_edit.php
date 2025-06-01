<?php

require_once '../functions/AdminController.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid event ID.');
}

$eventId = (int)$_GET['id'];
$event = getEventByIdWithUser($eventId);

if (!$event) {
    die('Event not found.');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $title = $_POST['title'] ?? '';
    $description = $_POST['description'] ?? '';
    $location = $_POST['location'] ?? '';
    $city = $_POST['city'] ?? '';
    $zip = $_POST['zip'] ?? '';
    $date = $_POST['date'] ?? '';

    if (!$title || !$date) {
        $error = 'Title and Date are required.';
    } else {
        $stmt = $pdo->prepare("
            UPDATE Events SET title = :title, description = :description, location = :location, city = :city, zip = :zip, date = :date
            WHERE ID_Event = :id
        ");
        $updated = $stmt->execute([
            'title' => $title,
            'description' => $description,
            'location' => $location,
            'city' => $city,
            'zip' => $zip,
            'date' => $date,
            'id' => $eventId
        ]);

        if ($updated) {
            header("Location: event_details.php?id=$eventId&updated=1");
            exit;
        } else {
            $error = 'Failed to update event.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <title>Edit Event - <?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="admin.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container py-4">
    <a href="event_details.php?id=<?= $eventId ?>" class="btn btn-secondary mb-3"><i class="fas fa-arrow-left"></i> Back to Event Details</a>

    <h1>Edit Event</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="title" class="form-label">Title *</label>
            <input type="text" name="title" id="title" class="form-control" value="<?= htmlspecialchars($event['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea name="description" id="description" class="form-control"><?= htmlspecialchars($event['description']) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="location" class="form-label">Location</label>
            <input type="text" name="location" id="location" class="form-control" value="<?= htmlspecialchars($event['location']) ?>">
        </div>

        <div class="mb-3">
            <label for="city" class="form-label">City</label>
            <input type="text" name="city" id="city" class="form-control" value="<?= htmlspecialchars($event['city']) ?>">
        </div>

        <div class="mb-3">
            <label for="zip" class="form-label">ZIP Code</label>
            <input type="text" name="zip" id="zip" class="form-control" value="<?= htmlspecialchars($event['zip']) ?>">
        </div>

        <div class="mb-3">
            <label for="date" class="form-label">Event Date *</label>
            <input type="datetime-local" name="date" id="date" class="form-control" 
                   value="<?= date('Y-m-d\TH:i', strtotime($event['date'])) ?>" required>
        </div>

        <button type="submit" class="btn btn-primary">Save Changes</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
