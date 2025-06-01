<?php
require_once '../functions/AdminController.php';
checkAdminAccess();


if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid event ID.');
}

$eventId = (int)$_GET['id'];


$event = getEventByIdWithUser($eventId);
if (!$event) {
    die('Event not found.');
}


$volunteers = getVolunteersByEventId($eventId);


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $deleted = deleteEventById($eventId);
    if ($deleted) {
        header('Location: event_manager.php?deleted=1');
        exit;
    } else {
        $error = 'Failed to delete event.';
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Event Details - <?= htmlspecialchars($event['title']) ?></title>
    <link rel="stylesheet" href="admin.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
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
                    <a href="event_edit.php?id=<?= htmlspecialchars($event['ID_Event']) ?>" class="btn btn-primary-style">
                        <i class="fas fa-edit me-2"></i>Rediģēt
                    </a>
                    
                    <form method="POST" onsubmit="return confirm('Vai tiešām vēlaties dzēst šo sludinājumu?');">
                        <button type="submit" name="delete" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Dzēst
                        </button>
                    </form>
                </div>
            </div>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

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
                                <div class="flex-shrink-0">
                                    <img src="<?= htmlspecialchars($event['profile_pic'] ?? '../images/default-profile.png') ?>" 
                                         class="rounded-circle" width="80" height="80" alt="Profils">
                                </div>
                                <div class="flex-grow-1 ms-3">
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
                        <div class="card-header card-header-style">
                            <i class="fas fa-users me-2"></i>Brīvprātīgie (<?= count($volunteers) ?>)
                        </div>
                        <div class="card-body">
                            <?php if (empty($volunteers)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Vēl neviens brīvprātīgais nav pievienojies šim pasākumam</h5>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr class="table-header-style">
                                                <th>Vārds</th>
                                                <th>Lietotājvārds</th>
                                                <th>Pievienojās</th>
                                                <th>Statuss</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($volunteers as $vol): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?= htmlspecialchars($vol['profile_pic'] ?? '../images/default-profile.png') ?>" 
                                                                 class="rounded-circle me-3" width="40" height="40" alt="Profils">
                                                            <div>
                                                                <p class="mb-0"><?= htmlspecialchars($vol['name'] . ' ' . $vol['surname']) ?></p>
                                                                <small class="text-muted"><?= htmlspecialchars($vol['email']) ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>@<?= htmlspecialchars($vol['username']) ?></td>
                                                    <td><?= date('M j, Y g:i a', strtotime($vol['created_at'])) ?></td>
                                                    <td>
                                                        <span class="badge <?= $vol['status'] === 'approved' ? 'bg-success' : 'bg-warning' ?> status-badge">
                                                            <?= $vol['status'] === 'approved' ? 'Apstiprināts' : 'Gaida' ?>
                                                        </span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>