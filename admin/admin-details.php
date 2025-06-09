<?php
require_once '../functions/AdminController.php';
if (!isModerator()) die('Access denied');
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBC Admin | Admin/Mod Detalizēta Informācija</title>
    <link rel="stylesheet" href="admin.css" defer>
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
            --warning-color: #f6c23e;
            --text-color: #5a5c69;
            --border-color: #e3e6f0;
            --hover-color: #2e59d9;
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
        
        .table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
        
        .table th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
            padding: 1rem;
            border: none;
        }
        
        .table td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid var(--border-color);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(78, 115, 223, 0.05);
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
        
        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
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
        
        .badge {
            padding: 0.5em 1em;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .sortable {
            cursor: pointer;
            position: relative;
            padding-right: 1.5rem;
        }
        
        .sortable:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .sortable::after {
            content: '↕';
            position: absolute;
            right: 0.5rem;
            opacity: 0.5;
        }
        
        .sortable[data-order="asc"]::after {
            content: '↑';
            opacity: 1;
        }
        
        .sortable[data-order="desc"]::after {
            content: '↓';
            opacity: 1;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="container-fluid py-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Admin/Mod Detalizēta Informācija</h1>
                <a href="admin_manager.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Atpakaļ
                </a>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold">Lietotāja Informācija</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>ID:</strong> <?= htmlspecialchars($user['ID_user']) ?></p>
                            <p><strong>Lietotājvārds:</strong> <?= htmlspecialchars($user['username']) ?></p>
                            <p><strong>Vārds:</strong> <?= htmlspecialchars($user['name']) ?></p>
                            <p><strong>Uzvārds:</strong> <?= htmlspecialchars($user['surname']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>E-pasts:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            <p><strong>Loma:</strong> 
                                <?php 
                                $currentUserRole = $_SESSION['role'] ?? '';
                                $canManage = canManageUser($currentUserRole, $user['role']);
                                ?>
                                <!-- Debug info -->
                                <small class="text-muted">
                                    (Current role: <?= htmlspecialchars($currentUserRole) ?>, 
                                    Target role: <?= htmlspecialchars($user['role']) ?>, 
                                    Can manage: <?= $canManage ? 'Yes' : 'No' ?>)
                                </small>
                                <?php if ($canManage): ?>
                                    <form method="POST" class="d-inline">
                                        <select name="new_role" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                            <option value="mod" <?= $user['role'] === 'mod' ? 'selected' : '' ?>>Moderator</option>
                                        </select>
                                        <input type="hidden" name="change_role" value="1">
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-info"><?= htmlspecialchars($user['role']) ?></span>
                                <?php endif; ?>
                            </p>
                            <p><strong>Reģistrācijas datums:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
                            <p><strong>Statuss:</strong> 
                                <?php if ($user['banned']): ?>
                                    <span class="badge bg-danger">Bloķēts</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Aktīvs</span>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                    
                    <?php if (canManageUser($_SESSION['role'] ?? '', $user['role'])): ?>
                    <div class="mt-4">
                        <form method="POST" class="d-inline">
                            <?php if ($user['banned']): ?>
                                <button type="submit" name="unban" class="btn btn-success">
                                    <i class="fas fa-unlock"></i> Atbloķēt
                                </button>
                            <?php else: ?>
                                <button type="submit" name="ban" class="btn btn-warning">
                                    <i class="fas fa-lock"></i> Bloķēt
                                </button>
                            <?php endif; ?>
                            <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Vai tiešām vēlaties dzēst šo lietotāju?')">
                                <i class="fas fa-trash"></i> Dzēst
                            </button>
                        </form>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Veiktās Darbības</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item export-csv" href="#">Eksportēt uz CSV</a></li>
                            <li><a class="dropdown-item print-table" href="#">Drukāt</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="sortable" data-sort="ID">ID</th>
                                    <th class="sortable" data-sort="event_title">Sludinājums</th>
                                    <th class="sortable" data-sort="reason">Iemesls</th>
                                    <th class="sortable" data-sort="deleted_at">Dzēšanas Datums</th>
                                    <th class="sortable" data-sort="undeleted_at">Atjaunošanas Datums</th>
                                </tr>
                            </thead>
                            <tbody id="actions-body">
                                <?php if (empty($actions)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Nav veiktu darbību.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($actions as $action): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($action['ID']) ?></td>
                                            <td><?= htmlspecialchars($action['event_title']) ?></td>
                                            <td><?= htmlspecialchars($action['reason']) ?></td>
                                            <td><?= date('d.m.Y H:i', strtotime($action['deleted_at'])) ?></td>
                                            <td>
                                                <?= $action['undeleted_at'] ? date('d.m.Y H:i', strtotime($action['undeleted_at'])) : '-' ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <div id="actions-pagination" class="pagination-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../functions/admin_script.js" defer></script>
</body>
</html> 