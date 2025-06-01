<?php

require_once '../functions/AdminController.php';
checkAdminAccess();

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    
    if (isset($_GET['stats'])) {
        try {
            switch ($_GET['stats']) {
                case 'daily':
                    $data = getEventsCountByDay();
                    echo json_encode(['success' => true, 'data' => $data]);
                    break;
                case 'weekly':
                    $data = getEventsCountByWeek();
                    echo json_encode(['success' => true, 'data' => $data]);
                    break;
                case 'monthly':
                    $data = getEventsCountByMonth();
                    echo json_encode(['success' => true, 'data' => $data]);
                    break;
                default:
                    echo json_encode(['success' => false, 'message' => 'Invalid statistics type']);
            }
            exit;
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            exit;
        }
    }
    
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;
    $sortField = $_GET['sort'] ?? 'created_at';
    $sortOrder = $_GET['order'] ?? 'DESC';

    try {
        $total = getEventsCount();
        $events = getPaginatedEvents($perPage, $offset, $sortField, $sortOrder);

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

$events = getAllEventsWithUser();
$eventsByDay = getEventsCountByDay();
$eventsByWeek = getEventsCountByWeek();
$eventsByMonth = getEventsCountByMonth();
$deletedCount = getDeletedEventsCount();
$popularEvent = getMostPopularEvent();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBC Admin | Events Dashboard</title>
    <link rel="stylesheet" href="admin.css" defer>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="container-fluid py-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Sludinājumu Panelis</h1>
            </div>

            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Kopā Sludinājumi</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= count($events) ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
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
                                        Izdzēstie Sludinājumi</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $deletedCount ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-trash-alt fa-2x text-gray-300"></i>
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
                                        Pieteikušies</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $popularEvent['volunteer_count'] ?? 0 ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Visi Sludinājumi</h6>
                    <div class="dropdown drop-table no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <li><a id="export-csv" class="dropdown-item" href="#">Eksportēt uz CSV</a></li>
                            <li><a id="print-table" class="dropdown-item" href="#">Drukāt</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Nr.</th>
                                    <th>Nosaukums</th>
                                    <th>Izveidoja</th>
                                    <th>Dzēsts</th>
                                    <th>Izveidots</th>
                                    <th>Darbības</th>
                                </tr>
                            </thead>
                            <tbody id="events-body">
                            </tbody>
                        </table>
                        <div id="events-pagination" class="pagination-container"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold">Sludinājumu Statistikas</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="eventsByDayChart"></canvas>
                            </div>
                        </div>
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