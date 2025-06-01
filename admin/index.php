<?php 
require_once '../functions/AdminController.php';
checkAdminAccess();


if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    $events_per_page = 5;
    $users_per_page = 5;
    $volunteers_per_page = 5;
    $current_events_page = isset($_GET['events_page']) ? (int)$_GET['events_page'] : 1;
    $current_users_page = isset($_GET['users_page']) ? (int)$_GET['users_page'] : 1;
    $current_volunteers_page = isset($_GET['volunteers_page']) ? (int)$_GET['volunteers_page'] : 1;

    // events data
    $recent_events = getPaginatedEvents($events_per_page, ($current_events_page - 1) * $events_per_page, 'created_at', 'DESC');
    $total_today_events = getEventsCountByDay()[0]['count'] ?? 0;
    $total_events_pages = ceil($total_today_events / $events_per_page);

    // users data
    $recent_users = getTodaysUsers($users_per_page, ($current_users_page - 1) * $users_per_page, 'created_at', 'DESC');
    $total_today_users = getTodaysUsersCount();
    $total_users_pages = ceil($total_today_users / $users_per_page);

    //Volunteers data
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


$events_per_page = 5;
$users_per_page = 5;
$volunteers_per_page = 5;
$current_events_page = isset($_GET['events_page']) ? (int)$_GET['events_page'] : 1;
$current_users_page = isset($_GET['users_page']) ? (int)$_GET['users_page'] : 1;
$current_volunteers_page = isset($_GET['volunteers_page']) ? (int)$_GET['volunteers_page'] : 1;

$recent_events = getPaginatedEvents($events_per_page, ($current_events_page - 1) * $events_per_page, 'created_at', 'DESC');
$recent_users = getTodaysUsers($users_per_page, ($current_users_page - 1) * $users_per_page, 'created_at', 'DESC');
$recent_volunteers = getTodaysVolunteers($volunteers_per_page, ($current_volunteers_page - 1) * $volunteers_per_page);


$total_today_events = getEventsCountByDay()[0]['count'] ?? 0;
$total_today_users = getTodaysUsersCount();
$total_today_volunteers = getTodaysVolunteersCount();


$total_events_pages = ceil($total_today_events / $events_per_page);
$total_users_pages = ceil($total_today_users / $users_per_page);
$total_volunteers_pages = ceil($total_today_volunteers / $volunteers_per_page);

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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
<script>
document.addEventListener('DOMContentLoaded', function() {
 
    const eventsCtx = document.getElementById('eventsChart').getContext('2d');
    const usersCtx = document.getElementById('usersChart').getContext('2d');

    
    const eventsChart = new Chart(eventsCtx, {
        type: 'line',
    data: {
            labels: <?= json_encode(array_map(function($item) { 
                return date('H:i', strtotime($item['day'])); 
            }, $today_events_data)) ?>,
        datasets: [{
                label: 'Sludinājumi pa stundām',
                data: <?= json_encode(array_map(function($item) { 
                    return $item['count']; 
                }, $today_events_data)) ?>,
                borderColor: 'rgba(78, 115, 223, 1)',
                backgroundColor: 'rgba(78, 115, 223, 0.2)',
                fill: true,
                tension: 0.3
        }]
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

    
    const usersChart = new Chart(usersCtx, {
        type: 'doughnut',
        data: {
            labels: ['Jauni', 'Bloķēti', 'Aktīvi'],
            datasets: [{
                data: [
                    <?= $today_users ?>,
                    <?= $today_banned ?>,
                    <?= $today_users - $today_banned ?>
                ],
                backgroundColor: [
                    'rgba(28, 200, 138, 0.8)',
                    'rgba(231, 74, 59, 0.8)',
                    'rgba(78, 115, 223, 0.8)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    function fetchData(type, page) {
        const url = new URL(window.location.href);
        url.searchParams.set('ajax', '1');
        url.searchParams.set(type + '_page', page);
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    document.getElementById(type + '-body').innerHTML = data[type].html;
                  
                    updatePagination(type, data[type].currentPage, data[type].totalPages);
                }
            })
            .catch(error => console.error('Error:', error));
    }
    // Pagination
    function updatePagination(type, currentPage, totalPages) {
        const paginationContainer = document.getElementById(type + '-pagination');
        if (!paginationContainer) return;

        let html = '';

       
        html += `<button class="pagination-btn" data-page="1" ${currentPage === 1 ? 'disabled' : ''}>First</button>`;
        html += `<button class="pagination-btn" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}>Prev</button>`;

        
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

       
        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="pagination-btn" data-page="${i}" ${i === currentPage ? 'disabled' : ''}>${i}</button>`;
        }

      
        html += `<button class="pagination-btn" data-page="${currentPage + 1}" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
        html += `<button class="pagination-btn" data-page="${totalPages}" ${currentPage === totalPages ? 'disabled' : ''}>Last</button>`;

        paginationContainer.innerHTML = html;

        
        paginationContainer.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', function() {
                if (!this.disabled) {
                    const page = parseInt(this.dataset.page);
                    fetchData(type, page);
                }
            });
        });
    }

    
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('pagination-btn')) {
            const type = e.target.closest('.pagination-container').id.replace('-pagination', '');
            const page = parseInt(e.target.dataset.page);
            if (!isNaN(page)) {
                fetchData(type, page);
            }
        }
    });

   
    if (document.getElementById('events-body')) {
        fetchData('events', 1);
    }
    if (document.getElementById('users-body')) {
        fetchData('users', 1);
    }
    if (document.getElementById('volunteers-body')) {
        fetchData('volunteers', 1);
    }
});
</script>
</body>
</html>
