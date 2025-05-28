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
        /* Custom Styles */
        .card-header-custom {
            background-color: #45a049;
            color: #fff;
            font-weight: 600;
            padding: 1rem 1.25rem;
            border-bottom: none;
        }
        
        .btn-custom-primary {
            background-color: #45a049;
            border-color: #45a049;
            color: white;
        }
        
        .btn-custom-primary:hover {
            background-color: #3d8b40;
            border-color: #3d8b40;
        }
        
        .btn-custom-danger {
            background-color: #d9534f;
            border-color: #d9534f;
        }
        
        .btn-custom-danger:hover {
            background-color: #c9302c;
            border-color: #c9302c;
        }
        
        .badge-custom {
            padding: 0.35em 0.65em;
            font-weight: 500;
        }
        
        body {
            background-color: #f8f9fa;
        }
        
        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        
        .list-group-item {
            border-left: none;
            border-right: none;
            padding: 1rem 1.25rem;
        }
        
        .list-group-item:first-child {
            border-top: none;
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
                <i class="fas fa-arrow-left me-2"></i>Back to Events
            </a>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="mb-0"><?= htmlspecialchars($event['title']) ?></h1>
                <div class="d-flex gap-2">
                    <a href="event_edit.php?id=<?= $event['ID_Event'] ?>" class="btn btn-custom-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    
                    <form method="POST" onsubmit="return confirm('Are you sure you want to delete this event?');">
                        <button type="submit" name="delete" class="btn btn-custom-danger">
                            <i class="fas fa-trash me-2"></i>Delete
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
                        <div class="card-header card-header-custom">
                            <i class="fas fa-info-circle me-2"></i>Event Details
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <h5 class="text-muted mb-2">Description</h5>
                                <p class="mb-4"><?= nl2br(htmlspecialchars($event['description'])) ?></p>
                                
                                <h5 class="text-muted mb-2">Location</h5>
                                <p class="mb-4">
                                    <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                    <?= htmlspecialchars($event['location']) ?>, <?= htmlspecialchars($event['city']) ?>, <?= htmlspecialchars($event['zip']) ?>
                                </p>
                                
                                <h5 class="text-muted mb-2">Date & Time</h5>
                                <p class="mb-4">
                                    <i class="far fa-calendar-alt text-muted me-2"></i>
                                    <?= date('F j, Y, g:i a', strtotime($event['date'])) ?>
                                </p>
                                
                                <h5 class="text-muted mb-2">Status</h5>
                                <p class="mb-0">
                                    <?= $event['deleted'] ? 
                                        '<span class="badge bg-danger badge-custom">Deleted</span>' : 
                                        '<span class="badge bg-success badge-custom">Active</span>' ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-6">
                    <div class="card h-100">
                        <div class="card-header card-header-custom">
                            <i class="fas fa-user me-2"></i>Created By
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-4">
                                <div class="flex-shrink-0">
                                    <img src="<?= htmlspecialchars($event['profile_pic'] ?? '../images/default-profile.png') ?>" 
                                         class="rounded-circle" width="80" height="80" alt="Profile">
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="mb-1"><?= htmlspecialchars($event['name'] . ' ' . $event['surname']) ?></h5>
                                    <p class="text-muted mb-0">@<?= htmlspecialchars($event['username']) ?></p>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h5 class="text-muted mb-2">Contact</h5>
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
                        <div class="card-header card-header-custom">
                            <i class="fas fa-users me-2"></i>Volunteers (<?= count($volunteers) ?>)
                        </div>
                        <div class="card-body">
                            <?php if (empty($volunteers)): ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">No volunteers have joined this event yet</h5>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Username</th>
                                                <th>Joined At</th>
                                                <th>Status</th>
                                                
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($volunteers as $vol): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <img src="<?= htmlspecialchars($vol['profile_pic'] ?? '../images/default-profile.png') ?>" 
                                                                 class="rounded-circle me-3" width="40" height="40" alt="Profile">
                                                            <div>
                                                                <p class="mb-0"><?= htmlspecialchars($vol['name'] . ' ' . $vol['surname']) ?></p>
                                                                <small class="text-muted"><?= htmlspecialchars($vol['email']) ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>@<?= htmlspecialchars($vol['username']) ?></td>
                                                    <td><?= date('M j, Y g:i a', strtotime($vol['created_at'])) ?></td>
                                                    <td>
                                                        <span class="badge <?= $vol['status'] === 'approved' ? 'bg-success' : 'bg-warning' ?> badge-custom">
                                                            <?= ucfirst(htmlspecialchars($vol['status'])) ?>
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