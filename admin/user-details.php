<?php

require_once '../functions/AdminController.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['undelete_event'])) {
    $eventId = $_POST['undelete_event_id'];
    undeleteEvent($eventId);
    header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    $eventId = $_POST['event_id'];
    $reason = trim($_POST['delete_reason']);
   $adminId = $_SESSION['ID_user'];

    if ($eventId && $reason) {
        deleteEventWithReason($eventId, $adminId, $reason);
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id']);
        exit;
    }
}

?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBC-Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #f8f9fc;
            --text-color: #333;
            --border-color: #e0e0e0;
        }
        
        body {
            font-family: 'Quicksand', sans-serif;
            color: var(--text-color);
            background-color: #f5f5f5;
        }
        
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }
        
        .main-content {
            flex: 1;
            overflow-x: hidden;
        }
        
        .admin-body {
            padding: 2rem;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin: 1rem;
        }
        
        .card-header-style {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-radius: 8px 8px 0 0 !important;
            padding: 1rem 1.5rem;
        }
        
        .table-header-style {
            background-color: var(--primary-color);
            color: white;
        }
        
        .table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .table th {
            font-weight: 600;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
        }
        
        .btn-primary-style {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary-style:hover {
            background-color: #3d8b40;
            border-color: #3d8b40;
            color: white;
        }
        
        .status-badge {
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 0.25rem;
        }
        
        .status-active {
            background-color: #28a745;
            color: white;
        }
        
        .status-deleted {
            background-color: #dc3545;
            color: white;
        }
        
        .status-pending {
            background-color: #ffc107;
            color: #212529;
        }
        
        .status-rejected {
            background-color: #fd7e14;
            color: white;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.5rem;
            margin-bottom: 1.5rem;
        }
        
        .user-info-table {
            width: 100%;
            max-width: 600px;
            margin-bottom: 1.5rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
<div class="admin-layout">

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <?php include 'header.php'; ?>

        <div class="admin-body">
            <div class="container">
                <h4 class="section-title">Lietotāja informācija</h4>
                <table class="table user-info-table">
                    <tr><th>Lietotājvārds</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
                    <tr><th>Vārds</th><td><?= htmlspecialchars($user['name']) ?></td></tr>
                    <tr><th>Uzvārds</th><td><?= htmlspecialchars($user['surname']) ?></td></tr>
                    <tr><th>E-pasts</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
                    <tr><th>Lokācija</th><td><?= htmlspecialchars($user['location']) ?></td></tr>
                    <tr><th>Biogrāfija</th><td><?= htmlspecialchars($user['bio']) ?></td></tr>
                    <tr><th>Saite</th><td><?= htmlspecialchars($user['social_links']) ?></td></tr>
                    <tr><th>Loma</th><td><?= htmlspecialchars($user['role']) ?></td></tr>
                    <tr><th>Banned</th><td><?= $user['banned'] ? 'Jā' : 'Nē' ?></td></tr>
                </table>

                <form method="POST" class="action-buttons">
                    <input type="hidden" name="user_id" value="<?= $user['ID_user'] ?>">
                    <?php if (!$user['banned']): ?>
                        <button type="submit" name="ban" class="btn btn-warning">Bloķēt</button>
                    <?php else: ?>
                        <button type="submit" name="unban" class="btn btn-success">Atbloķēt</button>
                    <?php endif; ?>
                    <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Vai tiešām dzēst lietotāju?')">Dzēst</button>
                    <a href="user_manager.php" class="btn btn-secondary">Atpakaļ</a>
                </form>

                <h4 class="section-title">Lietotāja izveidotie pasākumi</h4>
                <table class="table">
                    <thead>
                        <tr class="table-header-style">
                            <th>Nosaukums</th>
                            <th>Apraksts</th>
                            <th>Datums</th>
                            <th>Statuss</th>
                            <th>Darbības</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventsCreated as $event): ?>
                            <tr data-event-id="<?= $event['ID_Event'] ?>" style="<?= $event['deleted'] ? 'background-color: #f0f0f0;' : '' ?>">
                                <td><?= htmlspecialchars($event['title']) ?></td>
                                <td><?= htmlspecialchars($event['description']) ?></td>
                                <td><?= htmlspecialchars($event['date']) ?></td>
                                <td class="status-cell">
                                    <span class="status-badge <?= $event['deleted'] ? 'status-deleted' : 'status-active' ?>">
                                        <?= $event['deleted'] ? 'Dzēsts' : 'Aktīvs' ?>
                                    </span>
                                </td>
                                <td class="action-cell">
                                    <?php if (!$event['deleted']): ?>
                                        <button class="btn btn-sm btn-danger" onclick="showDeleteModal(<?= $event['ID_Event'] ?>)">Dzēst</button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-success" onclick="undeleteEvent(<?= $event['ID_Event'] ?>)">Atjaunot</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <h4 class="section-title">Pievienotie pasākumi kā brīvprātīgais</h4>
                <table class="table">
                    <thead>
                        <tr class="table-header-style">
                            <th>Nosaukums</th>
                            <th>Apraksts</th>
                            <th>Datums</th>
                            <th>Statuss</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($volunteeredEvents as $event): ?>
                            <tr style="<?= 
                                $event['deleted'] ? 'background-color: #f0f0f0;' : 
                                ($event['status'] === 'approved' ? 'background-color: #d4edda;' : 
                                ($event['status'] === 'pending' ? 'background-color: #fff3cd;' : 
                                ($event['status'] === 'rejected' ? 'background-color: #f8d7da;' : ''))) ?>">
                                <td><?= htmlspecialchars($event['title']) ?></td>
                                <td><?= htmlspecialchars($event['description']) ?></td>
                                <td><?= htmlspecialchars($event['date']) ?></td>
                                <td>
                                    <span class="status-badge 
                                        <?= $event['deleted'] ? 'status-deleted' : 
                                        ($event['status'] === 'approved' ? 'status-active' : 
                                        ($event['status'] === 'pending' ? 'status-pending' : 
                                        ($event['status'] === 'rejected' ? 'status-rejected' : ''))) ?>">
                                        <?= ucfirst($event['status']) . ($event['deleted'] ? ' / Dzēsts' : '') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="deleteEventForm">
            <input type="hidden" name="event_id" id="event_id">
            <div class="modal-content">
                <div class="modal-header card-header-style">
                    <h5 class="modal-title">Dzēst pasākumu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="delete_reason" class="form-label">Iemesls dzēšanai</label>
                        <select class="form-select" name="delete_reason" required>
                            <option value="">-- Izvēlies iemeslu --</option>
                            <option value="Spam">Spams</option>
                            <option value="Nepiemērots saturs">Nepiemērots saturs</option>
                            <option value="Nepareiza kategorija / dublikāts">Nepareiza kategorija / dublikāts</option>
                            <option value="Cits">Cits</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atcelt</button>
                    <button type="submit" name="delete_event" class="btn btn-danger">Dzēst</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function showDeleteModal(eventId) {
    document.getElementById('event_id').value = eventId;
    const modal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
    modal.show();
}

document.getElementById('deleteEventForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const eventId = document.getElementById('event_id').value;
    const reason = this.delete_reason.value;

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
            action: 'delete_event',
            event_id: eventId,
            reason: reason
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`tr[data-event-id="${eventId}"]`);
            row.style.backgroundColor = "#f0f0f0";
            row.querySelector('.status-cell .status-badge').className = "status-badge status-deleted";
            row.querySelector('.status-cell .status-badge').innerText = "Dzēsts";
            row.querySelector('.action-cell').innerHTML = `
                <button class="btn btn-sm btn-success" onclick="undeleteEvent(${eventId})">Atjaunot</button>
            `;

            bootstrap.Modal.getInstance(document.getElementById('deleteEventModal')).hide();
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
    });
});

function undeleteEvent(eventId) {
    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
            action: 'undelete_event',
            event_id: eventId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const row = document.querySelector(`tr[data-event-id="${eventId}"]`);
            row.style.backgroundColor = "";
            row.querySelector('.status-cell .status-badge').className = "status-badge status-active";
            row.querySelector('.status-cell .status-badge').innerText = "Aktīvs";

            row.querySelector('.action-cell').innerHTML = `
                <button class="btn btn-sm btn-danger" onclick="showDeleteModal(${eventId})">Dzēst</button>
            `;
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
    });
}
</script>

</body>
</html>