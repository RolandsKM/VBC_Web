<?php
require_once '../functions/AdminController.php';
checkSuperAdminAccess();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBC Admin | Admin/Mod Pārvaldība</title>
    <link rel="stylesheet" href="admin.css" defer>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .table-header-style {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }

        .table-header-style th {
            background-color: var(--primary-color);
            transition: background-color 0.2s ease;
        }

        .table-header-style th:hover {
            background-color: var(--hover-color);
            cursor: pointer;
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

        .pagination-container {
            margin-top: 1.5rem;
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            flex-wrap: wrap;
            padding: 0 1rem;
        }
        
        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 1px solid var(--border-color);
            background-color: white;
            color: var(--text-color);
            border-radius: 6px;
            transition: all 0.2s ease;
            min-width: 40px;
            text-align: center;
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

        /* Responsive styles */
        @media (max-width: 768px) {
            .pagination-container {
                gap: 0.25rem;
                padding: 0 0.5rem;
            }
            
            .pagination-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.9rem;
            }

            .table td, .table th {
                padding: 0.75rem;
            }
        }

        @media (max-width: 576px) {
            .pagination-container {
                gap: 0.2rem;
            }
            
            .pagination-btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.85rem;
                min-width: 35px;
            }

            .table td, .table th {
                padding: 0.5rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 400px) {
            .pagination-btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
                min-width: 30px;
            }
        }

        .blocked-user {
            background-color: #fff3cd !important;
        }
        
        .blocked-user:hover {
            background-color: #ffe7b3 !important;
        }
        
        .blocked-badge {
            background-color: #ffc107;
            color: #000;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            margin-left: 0.5rem;
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
                <h1 class="h3 mb-0 text-gray-800">Admin/Mod Pārvaldība</h1>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="fas fa-plus"></i> Pievienot jaunu admin/mod
                </button>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Moderatori (<?= $modCount ?>)</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item export-csv" href="#" data-table="mod">Eksportēt uz CSV</a></li>
                            <li><a class="dropdown-item print-table" href="#" data-table="mod">Drukāt</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="table-header-style">
                                <tr>
                                    <th>ID</th>
                                    <th>Lietotājvārds</th>
                                    <th>Vārds</th>
                                    <th>Uzvārds</th>
                                    <th>E-pasts</th>
                                    <th>Izveidots</th>
                                    <th>Darbības</th>
                                </tr>
                            </thead>
                            <tbody id="mod-body">
                                <?php foreach ($modUsers as $user): ?>
                                <tr class="<?= $user['is_blocked'] ? 'blocked-user' : '' ?>">
                                    <td><?= htmlspecialchars($user['ID_user']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($user['username']) ?>
                                        <?php if ($user['is_blocked']): ?>
                                            <span class="blocked-badge">Bloķēts</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['surname']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <a href="admin-details.php?id=<?= $user['ID_user'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="deleteUser(<?= $user['ID_user'] ?>)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div id="mod-pagination" class="pagination-container"></div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Administratori (<?= $adminCount ?>)</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item export-csv" href="#" data-table="admin">Eksportēt uz CSV</a></li>
                            <li><a class="dropdown-item print-table" href="#" data-table="admin">Drukāt</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead class="table-header-style">
                                <tr>
                                    <th>ID</th>
                                    <th>Lietotājvārds</th>
                                    <th>Vārds</th>
                                    <th>Uzvārds</th>
                                    <th>E-pasts</th>
                                    <th>Izveidots</th>
                                    <th>Darbības</th>
                                </tr>
                            </thead>
                            <tbody id="admin-body">
                                <?php foreach ($adminUsers as $user): ?>
                                <tr class="<?= $user['is_blocked'] ? 'blocked-user' : '' ?>">
                                    <td><?= htmlspecialchars($user['ID_user']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($user['username']) ?>
                                        <?php if ($user['is_blocked']): ?>
                                            <span class="blocked-badge">Bloķēts</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($user['name']) ?></td>
                                    <td><?= htmlspecialchars($user['surname']) ?></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></td>
                                    <td>
                                        <a href="admin-details.php?id=<?= $user['ID_user'] ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="deleteUser(<?= $user['ID_user'] ?>)" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div id="admin-pagination" class="pagination-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create mod/admin -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pievienot jaunu admin/mod</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
            </div>
            <div class="modal-body">
                <form id="create-user-form">
                    <div class="mb-3">
                        <label for="username" class="form-label">Lietotājvārds</label>
                        <input type="text" class="form-control" id="username" name="username" required pattern="^[a-zA-Z0-9_]{3,20}$" title="3-20 burti, cipari vai pasvītras">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Parole</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Apstiprini paroli</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Vārds</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="surname" class="form-label">Uzvārds</label>
                        <input type="text" class="form-control" id="surname" name="surname" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-pasts</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Loma</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Izvēlieties lomu</option>
                            <option value="mod">Moderators</option>
                            <option value="admin">Admins</option>
                        </select>
                    </div>
                    <div id="create-user-feedback" class="alert d-none"></div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atcelt</button>
                        <button type="submit" class="btn btn-primary">Izveidot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../functions/admin_script.js" defer></script>
</body>
</html>