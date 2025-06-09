<?php


require_once '../functions/AdminController.php';
if (!isModerator()) die('Access denied');

if (isset($_GET['ajax'])) {
    header('Content-Type: application/json');
    
    try {
        if (isset($_GET['stats'])) {
            $stats = $_GET['stats'];
            $period = $_GET['period'] ?? 'all';
            $data = [];
            
            switch($stats) {
                case 'daily':
                    $data = getEventsCountByDay($period);
                    break;
                case 'weekly':
                    $data = getEventsCountByWeek($period);
                    break;
                case 'monthly':
                    $data = getEventsCountByMonth($period);
                    break;
                default:
                    throw new Exception("Invalid stats type");
            }
            
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
            exit;
        }

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;
        $sortField = $_GET['sort'] ?? 'created_at';
        $sortOrder = $_GET['order'] ?? 'DESC';
        $table = $_GET['table'] ?? 'all';
        $search = $_GET['search'] ?? '';

        if ($table === 'todays') {
            $events = getTodaysEvents($perPage, $offset, $sortField, $sortOrder);
            $total = getTodaysEventsCount();
        } else {
            $events = getPaginatedEvents($perPage, $offset, $sortField, $sortOrder, $search);
            $total = getEventsCount($search);
        }

        if ($events === false) {
            throw new Exception("Failed to fetch events");
        }

        echo json_encode([
            'success' => true,
            'events' => $events,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage
        ]);
    } catch (Exception $e) {
        error_log("Error in event_manager.php: " . $e->getMessage());
        echo json_encode([
            'success' => false, 
            'message' => 'Server error: ' . $e->getMessage()
        ]);
    }
    exit;
}

$filterPeriod = $_GET['filter'] ?? 'all';
$events = getAllEventsWithUser();
$eventsByDay = getEventsCountByDay($filterPeriod);
$eventsByWeek = getEventsCountByWeek($filterPeriod);
$eventsByMonth = getEventsCountByMonth($filterPeriod);
$deletedCount = getDeletedEventsCount();
$popularEvent = getMostPopularEvent();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>VBC Admin Dashboard | Pasākumu Pārvalde</title>
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

        .row {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 0 -15px;
        }

        .col-xl-3, .col-md-6 {
            padding: 0 15px;
            margin-bottom: 1.5rem;
            width: 100%;
            display: flex;
            justify-content: center;
        }

        @media (min-width: 768px) {
            .col-md-6 {
                width: 50%;
            }
        }

        @media (min-width: 1200px) {
            .col-xl-3 {
                width: 25%;
            }
        }

        .pagination-container {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1rem;
            padding: 1rem;
            background-color: #f8f9fc;
            border-top: 1px solid #e3e6f0;
            flex-wrap: wrap;
        }
        
        .pagination-btn {
            padding: 0.375rem 0.75rem;
            border: 1px solid #e3e6f0;
            background: #fff;
            cursor: pointer;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            color: #4CAF50;
            transition: all 0.2s ease-in-out;
            margin: 0.25rem;
        }
        
        @media (max-width: 576px) {
            .pagination-btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }

            .pagination-container {
                padding: 0.5rem;
                gap: 0.25rem;
            }
        }
        
        .pagination-btn:hover:not(:disabled) {
            background: #4CAF50;
            color: #fff;
            border-color: #4CAF50;
        }
        
        .pagination-btn:disabled {
            background: #f8f9fc;
            color: #858796;
            cursor: not-allowed;
            border-color: #e3e6f0;
        }

        .pagination-btn.active {
            background: #1cc88a;
            color: #fff;
            border-color: #1cc88a;
        }

        .card {
            height: 100%;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
            width: 100%;
        }

        .card-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 1.25rem;
        }

        .card-header h6 {
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .card-body {
            padding: 1.25rem;
        }

        .table td {
            max-width: 200px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        @media (max-width: 768px) {
            .table td {
                max-width: 150px;
            }
            .stat-card {
                max-width: 100%;
            }
        }

        .badge {
            white-space: nowrap;
        }

        .stat-card {
            width: 100%;
            max-width: 300px;
            transition: transform 0.2s;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .text-xs {
            font-size: 0.7rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .stat-card .h5 {
            font-size: 1.25rem;
            margin-top: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .stat-card .card-body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 1rem;
        }

        .stat-card .row {
            margin: 0;
            width: 100%;
        }

        .stat-card .col {
            padding: 0;
        }

        .stat-card .col-auto {
            padding-left: 1rem;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
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
                <h1 class="h3 mb-0 text-gray-800">Pasākumu Pārvalde</h1>
            </div>

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Šodien Izveidoti</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count(getTodaysEvents()) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-plus fa-2x text-gray-300"></i>
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
                                        Dzēsti Pasākumi</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $deletedCount ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-trash fa-2x text-gray-300"></i>
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
                                        Kopā Pasākumi</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($events) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

               
            </div>



            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white border-bottom-0 d-flex align-items-center justify-content-between">
                            <h6 class="m-0 fw-semibold text-muted">Pasākumu Statistikas Grafiks</h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="chart-container" style="position: relative; height:400px;">
                                <canvas id="eventsByDayChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Šodien Izveidotie Pasākumi</h6>
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
                                    <th>Nr.</th>
                                    <th>Nosaukums</th>
                                    <th>Izveidoja</th>
                                    <th>Dzēsts</th>
                                    <th>Izveidots</th>
                                    <th class="text-center">Darbības</th>
                                </tr>
                            </thead>
                            <tbody id="todays-events-body">
                            </tbody>
                        </table>
                        
                    </div>
                    <div class="pagination-container" id="todays-events-pagination"></div>
                </div>
            </div>

            <div class="d-flex justify-content-end mb-3">
                <div class="input-group" style="width: 300px;">
                    <input type="text" id="searchEvents" class="form-control form-control-sm rounded-pill" placeholder="Meklēt pēc nosaukuma...">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill ms-2" type="button" id="clearEventSearch">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Visi Pasākumi</h6>
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
                                    <th>Nr.</th>
                                    <th>Nosaukums</th>
                                    <th>Izveidoja</th>
                                    <th>Dzēsts</th>
                                    <th>Izveidots</th>
                                    <th class="text-center">Darbības</th>
                                </tr>
                            </thead>
                            <tbody id="events-body">
                            </tbody>
                        </table>
                       
                    </div>
                    <div class="pagination-container" id="events-pagination"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../functions/admin_script.js"></script>
<script>
    
    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('eventsByDayChart')) {
            initializeEventManager();
        }
    });
</script>
</body>
</html>