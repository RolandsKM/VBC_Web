<?php

require_once '../functions/AdminController.php';
checkAdminAccess();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: event_manager.php?error=invalid_id");
    exit;
}

$eventId = (int)$_GET['id'];
$event = getEventByIdWithUser($eventId);

if (!$event) {
    header("Location: event_manager.php?error=not_found");
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $title = trim(htmlspecialchars($_POST['title'] ?? ''));
    $description = trim(htmlspecialchars($_POST['description'] ?? ''));
    $location = trim(htmlspecialchars($_POST['location'] ?? ''));
    $city = trim(htmlspecialchars($_POST['city'] ?? ''));
    $zip = trim(htmlspecialchars($_POST['zip'] ?? ''));
    $date = trim(htmlspecialchars($_POST['date'] ?? ''));

    if (!$title || !$date) {
        $error = 'Title and Date are required.';
    } else {
        try {
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
                header("Location: event_details.php?id=" . urlencode($eventId) . "&updated=1");
                exit;
            } else {
                $error = 'Failed to update event.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred while updating the event.';
           
            error_log("Event update error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>VBC Admin | Edit Event</title>
    <link rel="stylesheet" href="admin.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --danger-color: #e74a3b;
            --success-color: #1cc88a;
            --text-color: #5a5c69;
            --border-color: #e3e6f0;
            --hover-color: #2e59d9;
            --warning-color: #f6c23e;
            --info-color: #36b9cc;
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
        
        .container-fluid {
            padding: 2rem;
        }
        
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin-bottom: 1.5rem;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.2rem 1.5rem;
            border-radius: 12px 12px 0 0 !important;
        }
        
        .card-header h6 {
            color: var(--text-color);
            font-weight: 600;
            margin: 0;
            font-size: 1.1rem;
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
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            border-color: var(--danger-color);
        }
        
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
        
        .form-label {
            color: var(--text-color);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .form-select {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }
        
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        
        .back-button {
            color: var(--text-color);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            transition: color 0.2s ease;
        }
        
        .back-button:hover {
            color: var(--primary-color);
        }
        
        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="container-fluid py-4">
            <a href="event_manager.php" class="back-button">
                <i class="fas fa-arrow-left"></i> Atpakaļ uz Sludinājumiem
            </a>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0 text-gray-800">Rediģēt Sludinājumu</h1>
            </div>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-edit me-2"></i> Sludinājuma Informācija
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="title" class="form-label">Nosaukums</label>
                                    <input type="text" class="form-control" id="title" name="title" 
                                           value="<?= htmlspecialchars($event['title']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="date" class="form-label">Datums un Laiks</label>
                                    <input type="datetime-local" class="form-control" id="date" name="date" 
                                           value="<?= date('Y-m-d\TH:i', strtotime($event['date'])) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Apraksts</label>
                                    <textarea class="form-control" id="description" name="description" 
                                              rows="4" required><?= htmlspecialchars($event['description']) ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Adrese</label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?= htmlspecialchars($event['location']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">Pilsēta</label>
                                    <input type="text" class="form-control" id="city" name="city" 
                                           value="<?= htmlspecialchars($event['city']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="zip" class="form-label">Pasta Indekss</label>
                                    <input type="text" class="form-control" id="zip" name="zip" 
                                           value="<?= htmlspecialchars($event['zip']) ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Statuss</label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="active" <?= !$event['deleted'] ? 'selected' : '' ?>>Aktīvs</option>
                                        <option value="deleted" <?= $event['deleted'] ? 'selected' : '' ?>>Dzēsts</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="event_manager.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-2"></i> Atcelt
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i> Saglabāt Izmaiņas
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>

(function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault()
                event.stopPropagation()
            }
            form.classList.add('was-validated')
        }, false)
    })
})()
</script>
</body>
</html>
