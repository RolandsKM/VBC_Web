<?php
require_once '../functions/AdminController.php';
checkAdminAccess();

// Validate and sanitize event ID
$eventId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$eventId) {
    echo "<div class='alert alert-danger m-4'>Nederīgs sludinājuma ID.</div>";
    exit();
}

$volunteersPerPage = 2;

// Get event details with prepared statement
$event = getEventByIdWithUser($eventId);
if (!$event) {
    echo "<div class='alert alert-danger m-4'>Sludinājums nav atrasts.</div>";
    exit();
}

// Get volunteers with prepared statement
$volunteers = getVolunteersByEventId($eventId);
$totalVolunteers = count($volunteers);
$totalPages = ceil($totalVolunteers / $volunteersPerPage);

// Get user details with prepared statement
$userDetails = getUserById($event['user_id']);
if (!$userDetails) {
    echo "<div class='alert alert-danger m-4'>Lietotāja informācija nav atrasta.</div>";
    exit();
}

// Handle POST requests with CSRF protection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        echo "<div class='alert alert-danger m-4'>Nederīga pieprasījuma sesija.</div>";
        exit();
    }
    
    if (isset($_POST['delete'])) {
        $deleted = deleteEventById($eventId);
        if ($deleted) {
            header('Location: event_manager.php?deleted=1');
            exit;
        } else {
            $error = 'Neizdevās dzēst sludinājumu.';
        }
    }

    if (isset($_POST['undelete'])) {
        $undeleted = undeleteEvent($eventId);
        if ($undeleted) {
            header('Location: event_details.php?id=' . $eventId . '&undeleted=1');
            exit;
        } else {
            $error = 'Neizdevās atjaunot sludinājumu. Lūdzu, mēģiniet vēlreiz.';
        }
    }
}

if (isset($_GET['undeleted']) && $_GET['undeleted'] == 1) {
    $success = 'Sludinājums veiksmīgi atjaunots!';
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Event Details - <?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="admin.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #f8f9fc;
            --accent-color: #45a049;
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
        }
        
        .admin-body {
            padding: 2rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin: 1.5rem;
        }
        
        .card-header-style {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
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
            font-weight: 500;
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
            font-weight: 500;
            border-radius: 20px;
            text-transform: uppercase;
        }

        .sortable {
            cursor: pointer;
            user-select: none;
            position: relative;
        }

        .sortable:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .sortable i {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 0.8rem;
            opacity: 0.7;
        }

        .sortable:hover i {
            opacity: 1;
        }

        .pagination-btn {
            min-width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 2px;
            border-radius: 4px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            color: #495057;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .pagination-btn:hover:not(:disabled) {
            background-color: #e9ecef;
            border-color: #dee2e6;
            color: #495057;
        }

        .pagination-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .table-header-style th {
            padding: 1rem;
            border-bottom: 2px solid rgba(255, 255, 255, 0.1);
            position: relative;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(76, 175, 80, 0.05);
            transition: background-color 0.2s ease;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <?php include 'header.php'; ?>
                
        <div class="container py-4">
            <a href="event_manager.php" class="btn btn-outline-secondary mb-4">
                <i class="fas fa-arrow-left me-2"></i>Atpakaļ uz Sludinājumiem
            </a>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0"><?= htmlspecialchars($event['title']) ?></h1>
                <div class="d-flex gap-2">
                    <?php if ($event['deleted']): ?>
                        <button onclick="undeleteEvent(<?= $event['ID_Event'] ?>)" class="btn btn-success">
                            <i class="fas fa-undo me-2"></i>Atjaunot
                        </button>
                    <?php else: ?>
                    <a href="event_edit.php?id=<?= htmlspecialchars($event['ID_Event']) ?>" class="btn btn-primary-style">
                        <i class="fas fa-edit me-2"></i>Rediģēt
                    </a>
                    
                        <button onclick="showDeleteModal(<?= $event['ID_Event'] ?>)" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Dzēst
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <div id="alert-container"></div>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header card-header-style">
                            <i class="fas fa-info-circle me-2"></i>Sludinājuma Apraksts
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h5 class="text-muted mb-2">Apraksts</h5>
                                <p class="mb-4"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                                
                                <h5 class="text-muted mb-2">Atrašanās vieta</h5>
                                <p class="mb-4">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <?= htmlspecialchars($event['location']) ?>, <?= htmlspecialchars($event['city']) ?>, <?= htmlspecialchars($event['zip']) ?>
                                </p>
                                
                                <h5 class="text-muted mb-2">Datums un Laiks</h5>
                                <p class="mb-4">
                                    <i class="far fa-calendar-alt text-muted me-2"></i>
                                    <?= date('F j, Y, g:i a', strtotime($event['date'])) ?>
                                </p>
                                
                                <h5 class="text-muted mb-2">Statuss</h5>
                                <p class="mb-0">
                                    <?= $event['deleted'] ? 
                                        '<span class="badge bg-danger status-badge">Dzēsts</span>' : 
                                        '<span class="badge bg-success status-badge">Aktīvs</span>' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header card-header-style">
                            <i class="fas fa-user me-2"></i>Izveidoja
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <img src="../functions/assets/<?= htmlspecialchars($userDetails['profile_pic'] ?? 'default-profile.png') ?>" 
                                     alt="User Profile Picture" 
                                     class="rounded-circle me-3" 
                                     width="100" 
                                     height="100">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($event['name'] . ' ' . $event['surname']) ?></h5>
                                    <p class="text-muted mb-0">@<?= htmlspecialchars($event['username']) ?></p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h5 class="text-muted mb-2">Kontakti</h5>
                                <p class="mb-4">
                                    <i class="fas fa-envelope text-muted me-2"></i>
                                    <?= htmlspecialchars($event['email']) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Brīvprātīgie</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Vārds</th>
                                            <th>Uzvārds</th>
                                            <th>Lietotājvārds</th>
                                            <th>E-pasts</th>
                                            <th>Pieteikšanās datums</th>
                                            <th>Statuss</th>
                                        </tr>
                                    </thead>
                                    <tbody id="volunteersTableBody">
                                        <!-- Volunteers will be loaded here -->
                                    </tbody>
                                </table>
                            </div>
                            <div id="volunteersPagination" class="d-flex justify-content-center mt-3">
                                <!-- Pagination will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header card-header-style">
                <h5 class="modal-title">Dzēst pasākumu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="delete_reason" class="form-label">Iemesls dzēšanai</label>
                    <select class="form-select" id="delete_reason" required>
                        <option value="">Izvēlies iemeslu</option>
                        <option value="Nepareiza informācija">Nepareiza informācija</option>
                        <option value="Nepiemērots saturs">Nepiemērots saturs</option>
                        <option value="Dublēts sludinājums">Dublēts sludinājums</option>
                        <option value="Cits">Cits</option>
                    </select>
                    <div id="custom_reason_container" style="display: none;" class="mt-3">
                        <label for="custom_reason" class="form-label">Ievadi iemeslu:</label>
                        <input type="text" class="form-control" id="custom_reason" placeholder="Ievadi iemeslu">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atcelt</button>
                <button type="button" class="btn btn-danger" onclick="confirmDelete()">Dzēst</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../functions/admin_script.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize event details functionality
    initializeEventDetails();
    
    // Initial fetch of volunteers
    fetchVolunteers(1);
});
</script>
</body>
</html>