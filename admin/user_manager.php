<?php
require_once '../functions/AdminController.php';
checkAdminAccess();

if (isset($_GET['chart']) && $_GET['chart'] === 'bannedActive' && isset($_GET['period'])) {
    $period = htmlspecialchars($_GET['period']);
    try {
        $bannedCount = getBannedUsersCountByPeriod($period);
        $activeCount = getUsersCountByPeriod($period); 
        echo json_encode(['success' => true, 'banned' => $bannedCount, 'active' => $activeCount]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if (isset($_GET['chart']) && $_GET['chart'] === 'newUsers' && isset($_GET['period'])) {
    $period = htmlspecialchars($_GET['period']);
    try {
        $newUsersData = getNewUsersCountByPeriod($period);
        echo json_encode(['success' => true, 'data' => $newUsersData]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    if (isset($_GET['count']) && $_GET['count'] === 'banned' && ($_GET['table'] ?? '') === 'all') {
        $period = htmlspecialchars($_GET['period'] ?? 'all');
        $statusFilter = htmlspecialchars($_GET['status'] ?? 'all'); 
        try {
            $bannedCount = getBannedUsersCountByPeriod($period, $statusFilter);
            echo json_encode(['success' => true, 'bannedCount' => $bannedCount]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    $table = $_GET['table'] ?? '';
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;
    $sortField = $_GET['sort'] ?? 'created_at'; 
    $sortOrder = $_GET['order'] ?? 'DESC'; 
    $search = $_GET['search'] ?? '';

    try {
        if ($table === 'todays') {
            $users = getTodaysUsers($perPage, $offset, $sortField, $sortOrder);
            $total = getTodaysUsersCount();
        } elseif ($table === 'all') {
            $users = getAllUsers($perPage, $offset, $sortField, $sortOrder, $search);
            $total = getAllUsersCount($search);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid table']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

try {
    $todaysUsers = getTodaysUsers();
    $todaysUsersCount = count($todaysUsers);
    $todaysBannedCount = getTodaysBannedUsersCount();
    $filterPeriod = $_GET['filter'] ?? 'all';
    $allUsers = getAllUsers();
    $allUsersCount = count($allUsers);
    $allBannedCount = getBannedUsersCountByPeriod($filterPeriod);
} catch (PDOException $e) {
    $todaysUsers = [];
    $todaysUsersCount = 0;
    $todaysBannedCount = 0;
    $allUsers = [];
    $allUsersCount = 0;
    $allBannedCount = 0;
}

?>



<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>VBC Admin Dashboard | Lietotāju Pārvalde</title>
    <link rel="stylesheet" href="admin.css" defer />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
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
    </style>
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    <div class="main-content">
        <?php include 'header.php'; ?>

        <div class="container-fluid py-4">
   
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Lietotāju Pārvalde</h1>
                <div class="d-none d-sm-inline-block">
                </div>
            </div>

     
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Šodien Reģistrēti</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="todays-users-count"><?= $todaysUsersCount ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-plus fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-danger shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                        Šodien Bloķēti</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="todays-banned-count"><?= $todaysBannedCount ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-slash fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Kopā Lietotāji</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="total-users"><?= $allUsersCount ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Bloķēti Lietotāji</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="banned-users"><?= $allBannedCount ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-ban fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <select id="filter-period" class="form-select form-select-sm me-2 filter-control">
                    <option value="all" <?= $filterPeriod === 'all' ? 'selected' : '' ?>>Visi</option>
                    <option value="week" <?= $filterPeriod === 'week' ? 'selected' : '' ?>>Šonedēļ</option>
                    <option value="month" <?= $filterPeriod === 'month' ? 'selected' : '' ?>>Šomēnes</option>
                    <option value="year" <?= $filterPeriod === 'year' ? 'selected' : '' ?>>Šogad</option>
                </select>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom-0 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 fw-semibold text-muted">Lietotāju Statuss & Jauno Lietotāju Dinamika</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <canvas id="bannedActiveChart"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="chart-container">
                                        <canvas id="newUsersChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold ">Šodien Reģistrētie Lietotāji</h6>
                    <div class="dropdown drop-table drop-table no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                            aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="#">Eksportēt uz CSV</a></li>
                            <li><a class="dropdown-item" href="#">Drukāt</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr class="table-header-style">
                                    <th>Lietotājvārds</th>
                                    <th>E-pasts</th>
                                    <th>Reģistrācijas Laiks</th>
                                    <th>Statuss</th>
                                    <th class="text-center">Darbības</th>
                                </tr>
                            </thead>
                            <tbody id="todays-users-body">
                              
                            </tbody>
                        </table>
                        <div class="pagination-container" id="todays-users-pagination"></div>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <div class="input-group" style="width: 300px;">
                    <input type="text" id="searchUsers" class="form-control form-control-sm rounded-pill" placeholder="Meklēt pēc lietotājvārda vai e-pasta...">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill ms-2" type="button" id="clearSearch">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
     
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Visi Lietotāji</h6>
                    <div class="dropdown drop-table drop-table no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                            aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="#">Eksportēt uz CSV</a></li>
                            <li><a class="dropdown-item" href="#">Drukāt</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr class="table-header-style">
                                    <th>Lietotājvārds</th>
                                    <th>E-pasts</th>
                                    <th>Reģistrācijas Datums</th>
                                    <th>Statuss</th>
                                    <th class="text-center">Darbības</th>
                                </tr>
                            </thead>
                            <tbody id="all-users-body">
                               
                            </tbody>
                        </table>
                        <div class="pagination-container" id="all-users-pagination"></div>
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