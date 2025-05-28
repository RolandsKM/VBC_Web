<?php

require_once '../functions/AdminController.php';
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;

    try {
        $total = getEventsCount();
        $events = getPaginatedEvents($perPage, $offset);

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
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --danger-color: #e74a3b;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --text-color: #5a5c69;
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
                <h1 class="h3 mb-0 text-gray-800">Events Dashboard</h1>

            </div>
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Total Events</div>
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
                                        Deleted Events</div>
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
                                        Volunteers </div>
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
                    <h6 class="m-0 font-weight-bold ">All Events</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                            aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item" href="#">Export to CSV</a></li>
                            <li><a class="dropdown-item" href="#">Print</a></li>
                            <li><a class="dropdown-item" href="#">Filter by Status</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                   <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Event ID</th>
                                    <th>Title</th>
                                    <th>Created By</th>
                                    <th>Deleted</th>
                                    <th>Created At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="events-body">
                                <?php foreach($events as $event): ?>
                                <tr class="<?= $event['deleted'] ? 'table-danger' : '' ?>">
                                    <td><?= htmlspecialchars($event['ID_Event']) ?></td>
                                    <td><?= htmlspecialchars($event['title']) ?></td>
                                    <td><?= htmlspecialchars($event['name'] . ' ' . $event['surname'] . ' (' . $event['username'] . ')') ?></td>
                                  
                                    <td>
                                        <span class="status-badge <?= $event['deleted'] ? 'status-deleted' : 'status-active' ?>">
                                            <?= $event['deleted'] ? 'Yes' : 'No' ?>
                                        </span>
                                    </td>
                                    <td><?= date('Y-m-d H:i', strtotime($event['created_at'])) ?></td>
                                    <td>
                                        <a href="event_details.php?id=<?= $event['ID_Event'] ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
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
                            <h6 class="m-0 font-weight-bold">Event Creation Trends</h6>

                        </div>
                        <div class="card-body">
                            <div class="chart-container" style="height: 400px;">
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
<script>
document.addEventListener('DOMContentLoaded', () => {
    const perPage = 5;

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('lv-LV') + ' ' + date.toLocaleTimeString('lv-LV', {hour: '2-digit', minute:'2-digit'});
    }

    function renderEvents(events) {
        const tbody = document.getElementById('events-body');
        if (!tbody) return;

        if (events.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6">Nav notikumu.</td></tr>';
            return;
        }

        tbody.innerHTML = events.map(event => `
            <tr class="${event.deleted ? 'table-danger' : ''}">
                <td>${escapeHtml(event.ID_Event)}</td>
                <td>${escapeHtml(event.title)}</td>
                <td>${escapeHtml(event.name + ' ' + event.surname + ' (' + event.username + ')')}</td>
                <td>
                    <span class="status-badge ${event.deleted ? 'status-deleted' : 'status-active'}">
                        ${event.deleted ? 'Yes' : 'No'}
                    </span>
                </td>
                <td>${formatDate(event.created_at)}</td>
                <td>
                    <a href="event_details.php?id=${event.ID_Event}" class="btn btn-sm btn-primary">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
        `).join('');
    }

    function renderPagination(containerId, currentPage, totalPages) {
        const container = document.getElementById(containerId);
        if (!container) return;

        let html = '';

        html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="1" class="pagination-btn btn btn-sm me-1">First</button>`;
        html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}" class="pagination-btn btn btn-sm me-1">Prev</button>`;

        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);

        if (endPage - startPage < 4) {
            if (startPage === 1) {
                endPage = Math.min(totalPages, startPage + 4);
            } else if (endPage === totalPages) {
                startPage = Math.max(1, endPage - 4);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            html += `<button ${i === currentPage ? 'disabled' : ''} data-page="${i}" class="pagination-btn btn btn-sm me-1">${i}</button>`;
        }

        html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}" class="pagination-btn btn btn-sm me-1">Next</button>`;
        html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${totalPages}" class="pagination-btn btn btn-sm">Last</button>`;

        container.innerHTML = html;
    }

    function fetchEvents(page = 1) {
        fetch(`event_manager.php?ajax=1&page=${page}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    alert('Kļūda: ' + data.message);
                    return;
                }
                const totalPages = Math.ceil(data.total / data.perPage);
                renderEvents(data.events);
                renderPagination('events-pagination', data.page, totalPages);
            })
            .catch(err => alert('Kļūda ielādējot datus: ' + err));
    }

    document.getElementById('events-pagination').addEventListener('click', (e) => {
        if (e.target.classList.contains('pagination-btn')) {
            const page = parseInt(e.target.getAttribute('data-page'));
            if (!isNaN(page)) {
                fetchEvents(page);
            }
        }
    });

    fetchEvents(1);
});
</script>

<script>
const combinedLabels = [
   
    ...<?= json_encode(array_column($eventsByDay, 'day')) ?> 
];


const combinedData = {
    labels: combinedLabels,
    datasets: [
        {
            label: 'Events per Day',
            data: <?= json_encode(array_column($eventsByDay, 'count')) ?>,
            borderColor: 'rgba(78, 115, 223, 1)',
            backgroundColor: 'rgba(78, 115, 223, 0.2)', 
            fill: true,
            tension: 0.3,
            yAxisID: 'y'
        },
        {
            label: 'Events per Week',
            data: <?= json_encode(array_column($eventsByWeek, 'count')) ?>,
            borderColor: 'rgba(28, 200, 138, 1)',
            backgroundColor: 'rgba(28, 200, 138, 0.2)',
            fill: true,
            tension: 0.3,
            yAxisID: 'y'
        },
        {
            label: 'Events per Month',
            data: <?= json_encode(array_column($eventsByMonth, 'count')) ?>,
            borderColor: 'rgba(231, 74, 59, 1)',
            backgroundColor: 'rgba(231, 74, 59, 0.2)', 
            fill: true,
            tension: 0.3,
            yAxisID: 'y'
        }
    ]
};

const combinedChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0
            }
        },
        x: {
      
            type: 'category',
            ticks: {
                maxRotation: 45,
                minRotation: 45,
                autoSkip: true,
                maxTicksLimit: 20
            }
        }
    },
    plugins: {
        legend: {
            position: 'top',
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            callbacks: {
                label: function(context) {
                    return `${context.dataset.label}: ${context.parsed.y}`;
                }
            }
        }
    },
    interaction: {
        mode: 'nearest',
        axis: 'x',
        intersect: false
    }
};

new Chart(document.getElementById('eventsByDayChart'), {
    type: 'line',
    data: combinedData,
    options: combinedChartOptions
});

</script>
</body>
</html>