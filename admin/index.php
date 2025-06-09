<?php 
require_once '../functions/AdminController.php';
if (!isModerator()) die('Access denied');

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    $events_per_page = 5;
    $users_per_page = 5;
    $volunteers_per_page = 5;
    $current_events_page = isset($_GET['events_page']) ? (int)$_GET['events_page'] : 1;
    $current_users_page = isset($_GET['users_page']) ? (int)$_GET['users_page'] : 1;
    $current_volunteers_page = isset($_GET['volunteers_page']) ? (int)$_GET['volunteers_page'] : 1;

    // events 
    $recent_events = getPaginatedEvents($events_per_page, ($current_events_page - 1) * $events_per_page, 'created_at', 'DESC');
    $total_today_events = getEventsCountByDay()[0]['count'] ?? 0;
    $total_events_pages = ceil($total_today_events / $events_per_page);

    // users 
    $recent_users = getTodaysUsers($users_per_page, ($current_users_page - 1) * $users_per_page, 'created_at', 'DESC');
    $total_today_users = getTodaysUsersCount();
    $total_users_pages = ceil($total_today_users / $users_per_page);

    //Volunteers 
    $recent_volunteers = getTodaysVolunteers($volunteers_per_page, ($current_volunteers_page - 1) * $volunteers_per_page);
    $total_today_volunteers = getTodaysVolunteersCount();
    $total_volunteers_pages = ceil($total_today_volunteers / $volunteers_per_page);

    // events table
    $events_html = '';
    foreach ($recent_events as $event) {
        $events_html .= '<tr>';
        $events_html .= '<td>' . htmlspecialchars($event['title']) . '</td>';
        $events_html .= '<td>' . htmlspecialchars($event['username']) . '</td>';
        $events_html .= '<td>' . date('H:i', strtotime($event['created_at'])) . '</td>';
        $events_html .= '<td><span class="badge ' . ($event['deleted'] ? 'bg-danger' : 'bg-success') . '">' . 
                       ($event['deleted'] ? 'Dzēsts' : 'Aktīvs') . '</span></td>';
        $events_html .= '</tr>';
    }

    // users table
    $users_html = '';
    foreach ($recent_users as $user) {
        $users_html .= '<tr>';
        $users_html .= '<td>' . htmlspecialchars($user['username']) . '</td>';
        $users_html .= '<td>' . htmlspecialchars($user['email']) . '</td>';
        $users_html .= '<td>' . date('H:i', strtotime($user['created_at'])) . '</td>';
        $users_html .= '<td><span class="badge ' . ($user['banned'] ? 'bg-danger' : 'bg-success') . '">' . 
                      ($user['banned'] ? 'Bloķēts' : 'Aktīvs') . '</span></td>';
        $users_html .= '</tr>';
    }

    // volunteers table
    $volunteers_html = '';
    foreach ($recent_volunteers as $volunteer) {
        $volunteers_html .= '<tr>';
        $volunteers_html .= '<td>' . $volunteer['ID_Volunteers'] . '</td>';
        $volunteers_html .= '<td>' . htmlspecialchars($volunteer['username']) . '</td>';
        $volunteers_html .= '<td>' . htmlspecialchars($volunteer['title']) . '</td>';
        $volunteers_html .= '<td><span class="badge ' . ($volunteer['status'] === 'accepted' ? 'bg-success' : 'bg-warning') . '">' . 
                          ($volunteer['status'] === 'accepted' ? 'Apstiprināts' : 'Gaida') . '</span></td>';
        $volunteers_html .= '<td>' . date('H:i', strtotime($volunteer['created_at'])) . '</td>';
        $volunteers_html .= '</tr>';
    }

    echo json_encode([
        'success' => true,
        'events' => [
            'html' => $events_html,
            'currentPage' => $current_events_page,
            'totalPages' => $total_events_pages
        ],
        'users' => [
            'html' => $users_html,
            'currentPage' => $current_users_page,
            'totalPages' => $total_users_pages
        ],
        'volunteers' => [
            'html' => $volunteers_html,
            'currentPage' => $current_volunteers_page,
            'totalPages' => $total_volunteers_pages
        ]
    ]);
    exit;
}

// stats
$today_users = getTodaysUsersCount();
$today_events = getEventsCountByDay()[0]['count'] ?? 0;
$today_volunteers = getTodaysVolunteersCount();
$today_banned = getTodaysBannedUsersCount();
$total_active = getTotalActiveUsersCount();

$today_events_data = getEventsCountByDay();
$today_users_data = getNewUsersCountByPeriod('day');
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBC Admin | Vadības Panelis</title>
    <link rel="stylesheet" href="admin.css" defer>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* ... existing styles ... */

        .stat-card {
            transition: transform 0.2s;
            height: 100%;
            width: 100%;
            max-width: 300px;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .text-xs {
            font-size: 0.7rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 100%;
        }

        .stat-card .h5 {
            font-size: 1.25rem;
            margin-top: 0.5rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Graph card specific styles */
        .chart-card .card-header h6 {
            white-space: normal;
            overflow: visible;
            text-overflow: clip;
        }

        .chart-card .card-body {
            padding: 1.25rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }

        /* Responsive styles */
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

        @media (max-width: 768px) {
            .stat-card {
                max-width: 100%;
            }
        }

        .card {
            height: 100%;
            margin-bottom: 1.5rem;
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
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
                <h1 class="h3 mb-0 text-gray-800">Vadības Panelis</h1>
            </div>

           
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Jauni Lietotāji (Šodien)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $today_users ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
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
                                        Jauni Sludinājumi (Šodien)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $today_events ?></div>
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
                                        Pieteikušies</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $today_volunteers ?></div>
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
                                        Bloķētie Lietotāji (Šodien)</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $today_banned ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-ban fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

          
            <div class="row">
              
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold">Šodienas Sludinājumu Statistikas</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="eventsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold">Šodienas Lietotāju Statistikas</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="usersChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

          
            <div class="row">
             
                <div class="col-xl-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold">Šodienas Jaunākie Sludinājumi</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Nosaukums</th>
                                            <th>Izveidoja</th>
                                            <th>Laiks</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="events-body">
                                        <?php foreach ($recent_events as $event): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($event['title']) ?></td>
                                            <td><?= htmlspecialchars($event['username']) ?></td>
                                            <td><?= date('H:i', strtotime($event['created_at'])) ?></td>
                                            <td>
                                                <span class="badge <?= $event['deleted'] ? 'bg-danger' : 'bg-success' ?>">
                                                    <?= $event['deleted'] ? 'Dzēsts' : 'Aktīvs' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="events-pagination" class="pagination-container">
                                    <?php if ($total_events_pages > 1): ?>
                                        <button <?= $current_events_page === 1 ? 'disabled' : '' ?> 
                                                data-page="1" 
                                                class="pagination-btn">First</button>
                                        <button <?= $current_events_page === 1 ? 'disabled' : '' ?> 
                                                data-page="<?= $current_events_page - 1 ?>" 
                                                class="pagination-btn">Prev</button>
                                        
                                        <?php
                                        $startPage = max(1, $current_events_page - 2);
                                        $endPage = min($total_events_pages, $current_events_page + 2);
                                        
                                        if ($endPage - $startPage < 4) {
                                            if ($startPage === 1) {
                                                $endPage = min($total_events_pages, $startPage + 4);
                                            } else if ($endPage === $total_events_pages) {
                                                $startPage = max(1, $endPage - 4);
                                            }
                                        }
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <button <?= $i === $current_events_page ? 'disabled' : '' ?> 
                                                    data-page="<?= $i ?>" 
                                                    class="pagination-btn"><?= $i ?></button>
                                        <?php endfor; ?>
                                        
                                        <button <?= $current_events_page === $total_events_pages ? 'disabled' : '' ?> 
                                                data-page="<?= $current_events_page + 1 ?>" 
                                                class="pagination-btn">Next</button>
                                        <button <?= $current_events_page === $total_events_pages ? 'disabled' : '' ?> 
                                                data-page="<?= $total_events_pages ?>" 
                                                class="pagination-btn">Last</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

           
                <div class="col-xl-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold">Šodienas Jaunie Lietotāji</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Lietotājvārds</th>
                                            <th>E-pasts</th>
                                            <th>Laiks</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="users-body">
                                        <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($user['username']) ?></td>
                                            <td><?= htmlspecialchars($user['email']) ?></td>
                                            <td><?= date('H:i', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <span class="badge <?= $user['banned'] ? 'bg-danger' : 'bg-success' ?>">
                                                    <?= $user['banned'] ? 'Bloķēts' : 'Aktīvs' ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="users-pagination" class="pagination-container">
                                    <?php if ($total_users_pages > 1): ?>
                                        <button <?= $current_users_page === 1 ? 'disabled' : '' ?> 
                                                data-page="1" 
                                                class="pagination-btn">First</button>
                                        <button <?= $current_users_page === 1 ? 'disabled' : '' ?> 
                                                data-page="<?= $current_users_page - 1 ?>" 
                                                class="pagination-btn">Prev</button>
                                        
                                        <?php
                                        $startPage = max(1, $current_users_page - 2);
                                        $endPage = min($total_users_pages, $current_users_page + 2);
                                        
                                        if ($endPage - $startPage < 4) {
                                            if ($startPage === 1) {
                                                $endPage = min($total_users_pages, $startPage + 4);
                                            } else if ($endPage === $total_users_pages) {
                                                $startPage = max(1, $endPage - 4);
                                            }
                                        }
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <button <?= $i === $current_users_page ? 'disabled' : '' ?> 
                                                    data-page="<?= $i ?>" 
                                                    class="pagination-btn"><?= $i ?></button>
                                        <?php endfor; ?>
                                        
                                        <button <?= $current_users_page === $total_users_pages ? 'disabled' : '' ?> 
                                                data-page="<?= $current_users_page + 1 ?>" 
                                                class="pagination-btn">Next</button>
                                        <button <?= $current_users_page === $total_users_pages ? 'disabled' : '' ?> 
                                                data-page="<?= $total_users_pages ?>" 
                                                class="pagination-btn">Last</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

               
                <div class="col-xl-12">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold">Šodienas Pieteikumi</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Lietotājs</th>
                                            <th>Pasākums</th>
                                            <th>Status</th>
                                            <th>Laiks</th>
                                        </tr>
                                    </thead>
                                    <tbody id="volunteers-body">
                                        <?php foreach ($recent_volunteers as $volunteer): ?>
                                        <tr>
                                            <td><?= $volunteer['ID_Volunteers'] ?></td>
                                            <td><?= htmlspecialchars($volunteer['username']) ?></td>
                                            <td><?= htmlspecialchars($volunteer['title']) ?></td>
                                            <td>
                                                <span class="badge <?php
                                                    switch($volunteer['status']) {
                                                        case 'accepted':
                                                            echo 'bg-success';
                                                            break;
                                                        case 'denied':
                                                            echo 'bg-danger';
                                                            break;
                                                        case 'waiting':
                                                            echo 'bg-warning';
                                                            break;
                                                        case 'left':
                                                            echo 'bg-secondary';
                                                            break;
                                                        default:
                                                            echo 'bg-warning';
                                                    }
                                                ?>">
                                                    <?php
                                                    switch($volunteer['status']) {
                                                        case 'accepted':
                                                            echo 'Apstiprināts';
                                                            break;
                                                        case 'denied':
                                                            echo 'Noraidīts';
                                                            break;
                                                        case 'waiting':
                                                            echo 'Gaida';
                                                            break;
                                                        case 'left':
                                                            echo 'Izstājies';
                                                            break;
                                                        default:
                                                            echo htmlspecialchars($volunteer['status']);
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td><?= date('H:i', strtotime($volunteer['created_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                                <div id="volunteers-pagination" class="pagination-container">
                                    <?php if ($total_volunteers_pages > 1): ?>
                                        <button <?= $current_volunteers_page === 1 ? 'disabled' : '' ?> 
                                                data-page="1" 
                                                class="pagination-btn">First</button>
                                        <button <?= $current_volunteers_page === 1 ? 'disabled' : '' ?> 
                                                data-page="<?= $current_volunteers_page - 1 ?>" 
                                                class="pagination-btn">Prev</button>
                                        
                                        <?php
                                        $startPage = max(1, $current_volunteers_page - 2);
                                        $endPage = min($total_volunteers_pages, $current_volunteers_page + 2);
                                        
                                        if ($endPage - $startPage < 4) {
                                            if ($startPage === 1) {
                                                $endPage = min($total_volunteers_pages, $startPage + 4);
                                            } else if ($endPage === $total_volunteers_pages) {
                                                $startPage = max(1, $endPage - 4);
                                            }
                                        }
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <button <?= $i === $current_volunteers_page ? 'disabled' : '' ?> 
                                                    data-page="<?= $i ?>" 
                                                    class="pagination-btn"><?= $i ?></button>
                                        <?php endfor; ?>
                                        
                                        <button <?= $current_volunteers_page === $total_volunteers_pages ? 'disabled' : '' ?> 
                                                data-page="<?= $current_volunteers_page + 1 ?>" 
                                                class="pagination-btn">Next</button>
                                        <button <?= $current_volunteers_page === $total_volunteers_pages ? 'disabled' : '' ?> 
                                                data-page="<?= $total_volunteers_pages ?>" 
                                                class="pagination-btn">Last</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pass chart data to JavaScript
    window.eventsChartLabels = <?= json_encode(array_map(function($item) { 
        return date('H:i', strtotime($item['day'])); 
    }, $today_events_data)) ?>;
    window.eventsChartData = <?= json_encode(array_map(function($item) { 
        return $item['count']; 
    }, $today_events_data)) ?>;
    window.usersChartData = [
        <?= $today_users ?>,
        <?= $today_banned ?>,
        <?= $total_active ?>
    ];
</script>
<script src="../functions/admin_index_script.js"></script>
</body>
</html>
