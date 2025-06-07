<?php

require_once '../functions/AdminController.php';
checkAdminAccess();

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

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    $userId = $_GET['id'] ?? null;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;
    $table = $_GET['table'] ?? '';
    $sortField = $_GET['sort'] ?? 'created_at';
    $sortOrder = $_GET['order'] ?? 'DESC';

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Missing user ID']);
        exit;
    }

    try {
        if ($table === 'created') {
            $events = getPaginatedEventsCreatedByUser($userId, $perPage, $offset, $sortField, $sortOrder);
            $total = getEventsCreatedByUserCount($userId);
        } elseif ($table === 'volunteered') {
            $events = getPaginatedEventsUserVolunteered($userId, $perPage, $offset, $sortField, $sortOrder);
            $total = getEventsUserVolunteeredCount($userId);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid table']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'events' => $events,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
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
            --hover-color: #45a049;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --success-color: #28a745;
            --info-color: #17a2b8;
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
            padding: 1rem;
        }
        
        .admin-body {
            padding: 1rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin: 1rem;
        }

        .container {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        
        .col-md-8 {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0 15px;
        }

        .d-flex {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .user-info-table {
            flex: 1;
            min-width: 300px;
        }

        .stats-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-card {
            flex: 1;
            min-width: 250px;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .action-buttons .btn {
            flex: 1;
            min-width: 120px;
            max-width: 200px;
        }

        @media (min-width: 768px) {
            .main-content {
                padding: 1.5rem;
            }

            .admin-body {
                padding: 1.5rem;
                margin: 1.5rem;
            }

            .col-md-8 {
                flex: 0 0 66.666667%;
                max-width: 66.666667%;
            }

            .d-flex {
                flex-wrap: nowrap;
            }

            .stats-cards {
                margin-top: 0;
            }
        }

        @media (max-width: 767px) {
            .table th, .table td {
                padding: 0.5rem;
                font-size: 0.9rem;
            }

            .section-title {
                font-size: 1.2rem;
                margin-bottom: 1rem;
            }

            .stat-card {
                min-width: 100%;
            }

            .action-buttons .btn {
                width: 100%;
                max-width: none;
            }

            .pagination-container {
                flex-wrap: wrap;
            }

            .pagination-btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.9rem;
            }
        }
        
        .card-header-style {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-radius: 8px 8px 0 0 !important;
            padding: 1.2rem 1.5rem;
        }
        
        .table-header-style {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        
        .table th {
            font-weight: 600;
            padding: 1rem;
            border-bottom: 2px solid rgba(0,0,0,0.1);
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
            transition: background-color 0.2s ease;
        }
        
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .btn-primary-style {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary-style:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 0.5em 1em;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-deleted {
            background-color: var(--danger-color);
            color: white;
        }
        
        .status-pending {
            background-color: var(--warning-color);
            color: #212529;
        }
        
        .status-rejected {
            background-color: var(--danger-color);
            color: white;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.8rem;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .user-info-table {
            width: 100%;
            max-width: 600px;
            margin-bottom: 2rem;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        
        .user-info-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .user-info-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .pagination-container {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }
        
        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            background-color: white;
            color: var(--text-color);
            border-radius: 6px;
            transition: all 0.2s ease;
        }
        
        .pagination-btn:hover:not(:disabled) {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .pagination-btn:disabled {
            background-color: #f5f5f5;
            color: #999;
            cursor: not-allowed;
        }
        
        .sortable {
            cursor: pointer;
            position: relative;
            padding-right: 1.5rem;
        }
        
        .sortable:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .modal-content {
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            border-radius: 12px 12px 0 0;
        }
        
        .form-select {
            border-radius: 6px;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            transition: border-color 0.2s ease;
        }
        
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .stat-card:nth-child(1) .stat-icon {
            background: var(--primary-color);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: var(--info-color);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: var(--success-color);
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
                <div class="row">
                    <div class="col-md-8">
                <h4 class="section-title">Lietotāja informācija</h4>
                        <div class="d-flex">
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

                            <div class="stats-cards">
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 id="created-events-count">0</h3>
                                        <p>Izveidotie pasākumi</p>
                                    </div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-handshake"></i>
                                    </div>
                                    <div class="stat-content">
                                        <h3 id="volunteered-events-count">0</h3>
                                        <p>Pievienotie pasākumi</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                <form method="POST" class="action-buttons">
                    <input type="hidden" name="user_id" value="<?= $user['ID_user'] ?>">
                    <?php if (!$user['banned']): ?>
                                <button type="button" onclick="banUser(<?= $user['ID_user'] ?>)" class="btn btn-warning">Bloķēt</button>
                    <?php else: ?>
                                <button type="button" onclick="unbanUser(<?= $user['ID_user'] ?>)" class="btn btn-success">Atbloķēt</button>
                    <?php endif; ?>
                            <button type="button" onclick="deleteUser(<?= $user['ID_user'] ?>)" class="btn btn-danger">Dzēst</button>
                    <a href="user_manager.php" class="btn btn-secondary">Atpakaļ</a>
                </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <h4 class="section-title">Lietotāja izveidotie pasākumi</h4>
                    <table class="table">
                        <thead>
                            <tr class="table-header-style">
                                <th class="sortable" data-sort="title">Nosaukums</th>
                                <th class="sortable" data-sort="description">Apraksts</th>
                                <th class="sortable" data-sort="date">Datums</th>
                                <th class="sortable" data-sort="deleted">Statuss</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody id="created-events-body">
                           
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="created-events-pagination"></div>

                <div class="table-responsive">
                    <h4 class="section-title">Pievienotie pasākumi kā brīvprātīgais</h4>
                    <table class="table">
                        <thead>
                            <tr class="table-header-style">
                                <th class="sortable" data-sort="title">Nosaukums</th>
                                <th class="sortable" data-sort="description">Apraksts</th>
                                <th class="sortable" data-sort="date">Datums</th>
                                <th class="sortable" data-sort="status">Statuss</th>
                            </tr>
                        </thead>
                        <tbody id="volunteered-events-body">
                           
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="volunteered-events-pagination"></div>
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
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
//pagination
function renderPagination(containerId, currentPage, totalPages, table) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let html = '';
    
    html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="1" data-table="${table}" class="pagination-btn btn btn-sm me-1">First</button>`;
    html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}" data-table="${table}" class="pagination-btn btn btn-sm me-1">Prev</button>`;

    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    if (endPage - startPage < 4) {
        if (startPage === 1) {
            endPage = Math.min(totalPages, startPage + 4);
        } else if (endPage === totalPages) {
            startPage = Math.max(1, endPage - 4);
        }
    }

    for (let i = startPage; i <= endPage; i++) {
        html += `<button ${i === currentPage ? 'disabled' : ''} data-page="${i}" data-table="${table}" class="pagination-btn btn btn-sm me-1">${i}</button>`;
    }

    html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}" data-table="${table}" class="pagination-btn btn btn-sm me-1">Next</button>`;
    html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${totalPages}" data-table="${table}" class="pagination-btn btn btn-sm">Last</button>`;

    container.innerHTML = html;
}
//Model
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

function banUser(userId) {
    if (!confirm('Vai tiešām vēlaties bloķēt šo lietotāju?')) return;

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
            action: 'ban_user',
            user_id: userId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            
            const banButton = document.querySelector('.action-buttons button.btn-warning');
            if (banButton) {
                banButton.outerHTML = `
                    <button type="button" onclick="unbanUser(${userId})" class="btn btn-success">Atbloķēt</button>
                `;
            }
            // Update the banned status in the table
            const bannedCell = document.querySelector('table.user-info-table tr:last-child td');
            if (bannedCell) {
                bannedCell.textContent = 'Jā';
            }
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
    });
}

function unbanUser(userId) {
    if (!confirm('Vai tiešām vēlaties atbloķēt šo lietotāju?')) return;

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
            action: 'unban_user',
            user_id: userId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            
            const unbanButton = document.querySelector('.action-buttons button.btn-success');
            if (unbanButton) {
                unbanButton.outerHTML = `
                    <button type="button" onclick="banUser(${userId})" class="btn btn-warning">Bloķēt</button>
                `;
            }
            
            const bannedCell = document.querySelector('table.user-info-table tr:last-child td');
            if (bannedCell) {
                bannedCell.textContent = 'Nē';
            }
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
    });
}

function deleteUser(userId) {
    if (!confirm('Vai tiešām vēlaties dzēst šo lietotāju?')) return;

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
            action: 'delete_user',
            user_id: userId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'user_manager.php';
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
    });
}

function renderEvents(tableId, events, isVolunteered = false) {
    const tbody = document.getElementById(tableId);
    if (!tbody) return;

    if (events.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center">Nav pasākumu.</td></tr>';
        return;
    }

    tbody.innerHTML = events.map(event => {
        const statusClass = event.deleted ? 'status-deleted' : 
            (isVolunteered ? 
                (event.status === 'approved' ? 'status-active' : 
                (event.status === 'pending' ? 'status-pending' : 
                (event.status === 'rejected' ? 'status-rejected' : ''))) : 
                'status-active');
        
        const statusText = event.deleted ? 'Dzēsts' : 
            (isVolunteered ? 
                ucfirst(event.status) + (event.deleted ? ' / Dzēsts' : '') : 
                'Aktīvs');

        const actionCell = !isVolunteered ? `
            <td class="action-cell">
                ${!event.deleted ? 
                    `<button class="btn btn-sm btn-danger" onclick="showDeleteModal(${event.ID_Event})">Dzēst</button>` :
                    `<button class="btn btn-sm btn-success" onclick="undeleteEvent(${event.ID_Event})">Atjaunot</button>`
                }
            </td>` : '';

        return `
            <tr data-event-id="${event.ID_Event}" style="${event.deleted ? 'background-color: #f0f0f0;' : 
                (isVolunteered ? 
                    (event.status === 'approved' ? 'background-color: #d4edda;' : 
                    (event.status === 'pending' ? 'background-color: #fff3cd;' : 
                    (event.status === 'rejected' ? 'background-color: #f8d7da;' : ''))) : '')}">
                <td>${escapeHtml(event.title)}</td>
                <td>${escapeHtml(event.description)}</td>
                <td>${escapeHtml(event.date)}</td>
                <td class="status-cell">
                    <span class="status-badge ${statusClass}">${statusText}</span>
                </td>
                ${actionCell}
            </tr>
        `;
    }).join('');
}


let currentSortField = 'created_at';
let currentSortOrder = 'DESC';
let currentPage = {
    created: 1,
    volunteered: 1
};

function fetchEvents(table, page) {
    const userId = new URLSearchParams(window.location.search).get('id');
    if (!userId) return;

    
    currentPage[table] = page;

    fetch(`user-details.php?ajax=1&id=${userId}&table=${table}&page=${page}&sort=${currentSortField}&order=${currentSortOrder}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Kļūda: ' + data.message);
                return;
            }

            const totalPages = Math.ceil(data.total / data.perPage);
            const tableId = table === 'created' ? 'created-events-body' : 'volunteered-events-body';
            const paginationId = table === 'created' ? 'created-events-pagination' : 'volunteered-events-pagination';

            renderEvents(tableId, data.events, table === 'volunteered');
            renderPagination(paginationId, data.page, totalPages, table);
        })
        .catch(err => alert('Kļūda ielādējot datus: ' + err));
}

function ucfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}


document.addEventListener('DOMContentLoaded', () => {
    
    fetchEvents('created', 1);
    fetchEvents('volunteered', 1);
    updateStats();

  
    document.querySelectorAll('.sortable').forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', () => {
            const table = header.closest('table').querySelector('tbody').id === 'created-events-body' ? 'created' : 'volunteered';
            const sortField = header.getAttribute('data-sort');
            const currentOrder = header.getAttribute('data-order') || 'desc';
            const newOrder = currentOrder === 'desc' ? 'asc' : 'desc';
            
            header.setAttribute('data-order', newOrder);
            const headerText = header.textContent.replace(/[↑↓]/, '').trim();
            header.innerHTML = headerText + (newOrder === 'asc' ? ' ↑' : ' ↓');
           
            currentSortField = sortField;
            currentSortOrder = newOrder;
            
       
            fetchEvents(table, currentPage[table]);
        });
    });
});

document.body.addEventListener('click', function(e) {
    if (e.target.classList.contains('pagination-btn')) {
        const page = parseInt(e.target.getAttribute('data-page'));
        const table = e.target.getAttribute('data-table');
        fetchEvents(table, page);
    }
});

function updateStats() {
    const userId = new URLSearchParams(window.location.search).get('id');
    if (!userId) return;

    fetch(`user-details.php?ajax=1&id=${userId}&table=created&page=1&sort=created_at&order=DESC`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('created-events-count').textContent = data.total;
            }
        });

    fetch(`user-details.php?ajax=1&id=${userId}&table=volunteered&page=1&sort=created_at&order=DESC`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('volunteered-events-count').textContent = data.total;

                const approvedCount = data.events.filter(event => event.status === 'approved').length;
                document.getElementById('approved-events-count').textContent = approvedCount;
        }
    });
}
</script>

</body>
</html>