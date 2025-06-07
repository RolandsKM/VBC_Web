<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../functions/AdminController.php';
checkAdminAccess();

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

                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Populārākais Pasākums</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $popularEvent ? $popularEvent['title'] : 'Nav' ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-star fa-2x text-gray-300"></i>
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

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const filterPeriodSelect = document.getElementById('filter-period');
                    const eventsByDayCtx = document.getElementById('eventsByDayChart');
                    
                    if (eventsByDayCtx) {
                        const eventsByDayChart = new Chart(eventsByDayCtx, {
                            type: 'line',
                            data: {
                                labels: [],
                                datasets: [
                                    {
                                        label: 'Pasākumi dienā',
                                        data: [],
                                        borderColor: 'rgba(78, 115, 223, 1)',
                                        backgroundColor: 'rgba(78, 115, 223, 0.2)',
                                        fill: true,
                                        tension: 0.3,
                                        yAxisID: 'y'
                                    },
                                    {
                                        label: 'Pasākumi nedēļā',
                                        data: [],
                                        borderColor: 'rgba(28, 200, 138, 1)',
                                        backgroundColor: 'rgba(28, 200, 138, 0.2)',
                                        fill: true,
                                        tension: 0.3,
                                        yAxisID: 'y'
                                    },
                                    {
                                        label: 'Pasākumi mēnesī',
                                        data: [],
                                        borderColor: 'rgba(231, 74, 59, 1)',
                                        backgroundColor: 'rgba(231, 74, 59, 0.2)',
                                        fill: true,
                                        tension: 0.3,
                                        yAxisID: 'y'
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                    }
                                }
                            }
                        });

                        function updateEventStatistics(period = 'all') {
                            console.log('Updating statistics for period:', period);
                            
                            // Daily statistics
                            fetch(`event_manager.php?ajax=1&stats=daily&period=${period}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    console.log('Daily stats response:', data);
                                    if (data.success && Array.isArray(data.data)) {
                                        const dailyData = data.data;
                                        eventsByDayChart.data.labels = dailyData.map(item => item.day);
                                        eventsByDayChart.data.datasets[0].data = dailyData.map(item => item.count);
                                        eventsByDayChart.update();
                                    } else {
                                        console.error('Invalid daily stats data format:', data);
                                    }
                                })
                                .catch(err => console.error('Error fetching daily stats:', err));

                            // Weekly statistics
                            fetch(`event_manager.php?ajax=1&stats=weekly&period=${period}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    console.log('Weekly stats response:', data);
                                    if (data.success && Array.isArray(data.data)) {
                                        const weeklyData = data.data;
                                        eventsByDayChart.data.datasets[1].data = weeklyData.map(item => item.count);
                                        eventsByDayChart.update();
                                    } else {
                                        console.error('Invalid weekly stats data format:', data);
                                    }
                                })
                                .catch(err => console.error('Error fetching weekly stats:', err));

                            // Monthly statistics
                            fetch(`event_manager.php?ajax=1&stats=monthly&period=${period}`)
                                .then(response => {
                                    if (!response.ok) {
                                        throw new Error(`HTTP error! status: ${response.status}`);
                                    }
                                    return response.json();
                                })
                                .then(data => {
                                    console.log('Monthly stats response:', data);
                                    if (data.success && Array.isArray(data.data)) {
                                        const monthlyData = data.data;
                                        eventsByDayChart.data.datasets[2].data = monthlyData.map(item => item.count);
                                        eventsByDayChart.update();
                                    } else {
                                        console.error('Invalid monthly stats data format:', data);
                                    }
                                })
                                .catch(err => console.error('Error fetching monthly stats:', err));
                        }

                        // Initial update
                        updateEventStatistics(filterPeriodSelect.value);

                        // Update when filter changes
                        filterPeriodSelect.addEventListener('change', function() {
                            console.log('Filter period changed to:', this.value); // Debug log
                            updateEventStatistics(this.value);
                        });

                        // Update every 5 minutes
                        setInterval(() => {
                            updateEventStatistics(filterPeriodSelect.value);
                        }, 300000);
                    }
                });
            </script>

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
                        <div class="pagination-container" id="todays-events-pagination"></div>
                    </div>
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
                        <div class="pagination-container" id="events-pagination"></div>
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