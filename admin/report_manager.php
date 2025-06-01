<?php
require_once '../functions/AdminController.php';

$limit = 10; 
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$reports = getPaginatedReports($limit, $offset);
$totalReports = countAllReports();
$totalPages = ceil($totalReports / $limit);
$totalReports = countAllReports();
$todayReports = getTodayReportsCount();
$uniqueReporters = getUniqueReporterCount();
$resolvedReports = getResolvedReportsCount();
$last7DaysCounts = getLast7DaysReportCounts();
$last12MonthsCounts = getLast12MonthsReportCounts(); 

$fixedReasons = [
    'Nepareiza atrašanās vieta',
    'Nepareizs datums/laiks',
    'Aizvainojošs saturs',
    'Mākslīgais pasākums',
    'Citi'
];

$dbReasons = getTopReportReasons(); 

$topReasons = [];
foreach ($fixedReasons as $reason) {
    $topReasons[$reason] = $dbReasons[$reason] ?? 0;
}


?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>VBC Admin Dashboard | Ziņojumi & Moderācija</title>
    <link rel="stylesheet" href="admin.css" defer />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600&display=swap" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet" />
   <style>
            :root {
            --primary-color: #4e73df;
            --secondary-color: #f8f9fc;
            --accent-color: #2e59d9;
            --danger-color: #e74a3b;
            --success-color: #1cc88a;
            --text-color: #5a5c69;
            --border-color: #e3e6f0;
        }
        
   </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'sidebar.php'; ?>

        <div class="main-content">
            <?php include 'header.php'; ?>
            
            <div class="container-fluid px-4">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800">Ziņojumu pārvaldība</h1>
                    <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                        <i class="fas fa-download fa-sm text-white-50"></i> Ģenerēt pārskatu
                    </a>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">Ziņojumu aktivitāte</h6>
                    <select id="timeRangeSelect" class="form-select form-select-sm w-auto">
                        <option value="7days" selected>Pēdējās 7 dienas</option>
                        <option value="12months">Pēdējie 12 mēneši</option>
                    </select>
                </div>

                <div class="row mb-4">
                    <div class="col-lg-8">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold">Ziņojumu aktivitāte (pēdējās 7 dienas)</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="reportsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold ">Top iemesli</h6>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="reasonsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                
                <!-- Reports Table -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold ">Pēdējie ziņojumi</h6>
                        <div class="dropdown no-arrow">
                            <a class="dropdown-toggle btn-darb" href="#" role="button" id="dropdownMenuLink" 
                               data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                            </a>
                            <ul  class="dropdown-menu dropdown-menu-end shadow animated--fade-in" 
                                aria-labelledby="dropdownMenuLink">
                                <li><a class="dropdown-item" href="#" id="export-csv">Eksportēt CSV</a></li>
                                <li><a class="dropdown-item" href="#" id="print-reports">Drukāt</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" id="refresh-data">Atjaunot datus</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="reportsTable" width="100%" cellspacing="0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Ziņotājs</th>
                                        <th>Pasākums</th>
                                        <th>Autors</th>
                                        <th>Iemesls</th>
                                        <th>Datums</th>
                                        <th>Statuss</th>
                                        <th>Darbības</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?= $report['ID_report'] ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?= htmlspecialchars($report['reporter_pic'] ?: '../assets/img/default-user.png') ?>" 
                                                     class="user-avatar me-2" alt="<?= htmlspecialchars($report['username']) ?>">
                                                <?= htmlspecialchars($report['username']) ?>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($report['title']) ?></td>
                                        <td data-creator-id="<?= $report['creator_id'] ?>">
                                            <div class="d-flex align-items-center">
                                                <img src="<?= htmlspecialchars($report['creator_pic'] ?: '../assets/img/default-user.png') ?>" 
                                                    class="user-avatar me-2" alt="<?= htmlspecialchars($report['event_creator']) ?>">
                                                <?= htmlspecialchars($report['event_creator']) ?>
                                                <?php if ($report['creator_banned']): ?>
                                                    <span class="badge bg-danger ms-2">BLOĶĒTS</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>

                                        <td class="report-reason" title="<?= htmlspecialchars($report['report_reason']) ?>">
                                            <?= htmlspecialchars($report['report_reason']) ?>
                                        </td>
                                        <td><?= date('d.m.Y H:i', strtotime($report['reported_at'])) ?></td>
                                        <td>
                                            <?php if ($report['event_deleted']): ?>
                                                <span class="badge bg-danger">Dzēsts</span>
                                            <?php else: ?>
                                                <span class="badge bg-success">Aktīvs</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="action-buttons">
                                            <button class="btn btn-info btn-sm view-report" data-id="<?= $report['ID_report'] ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <div class="pagination-container" id="pagination-controls" ></div>
                            
                        </div>
                        

                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="reportDetailsModal" tabindex="-1" aria-labelledby="reportDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header text-white">
                    <h5 class="modal-title" id="reportDetailsModalLabel">Ziņojuma detaļas</h5>
                    <button type="button" class="btn-close btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Ziņojuma informācija</h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <div class="fw-bold">Ziņotājs:</div>
                                        <div id="reporterInfo" class="d-flex align-items-center mt-1">
                                            <img id="reporterAvatar" src="" class="user-avatar me-2">
                                            <div>
                                                <div id="reporterName"></div>
                                                <small class="text-muted" id="reportDate"></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="fw-bold">Iemesls:</div>
                                        <div id="reportReason" class="mt-1 p-2 bg-light rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card mb-4">
                                <div class="card-header bg-light">
                                    <h5 class="mb-0">Pasākuma autors</h5>
                                </div>
                                <div class="card-body">
                                    <div id="creatorInfo" class="d-flex align-items-center mb-3">
                                        <img id="creatorAvatar" src="" class="user-avatar me-2">
                                        <div>
                                            <div id="creatorName"></div>
                                            <small class="text-muted" id="creatorStatus"></small>
                                        </div>
                                    </div>
                                    <div class="d-flex">
                                        <button class="btn btn-sm btn-outline-danger me-2" id="banCreator">
                                            <i class="fas fa-ban me-1"></i> Bloķēt
                                        </button>
                                        <button class="btn btn-sm btn-outline-warning" id="warnCreator">
                                            <i class="fas fa-exclamation-triangle me-1"></i> Brīdināt
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h5 class="mb-0">Pasākuma informācija</h5>
                        </div>
                        <div class="card-body">
                            <h5 id="eventTitle"></h5>
                            <div class="mb-3">
                                <div class="fw-bold">Apraksts:</div>
                                <div id="eventDescription" class="mt-1 p-2 bg-light rounded"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="fw-bold">Datums un laiks:</div>
                                    <div id="eventDateTime"></div>
                                </div>
                                <div class="col-md-6">
                                    <div class="fw-bold">Vieta:</div>
                                    <div id="eventLocation"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Aizvērt</button>
                        <button type="button" class="btn btn-danger" id="openDeleteModal">
                        <i class="fas fa-trash me-1"></i> Dzēst pasākumu
                    </button>


                    <button type="button" class="btn btn-success" id="confirmResolve">
                        <i class="fas fa-check me-1"></i> Atrisināt ziņojumu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteEventModal" tabindex="-1" aria-labelledby="deleteEventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="deleteEventModalLabel">Apstiprināt pasākuma dzēšanu</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <p>Lūdzu, ievadiet iemeslu pasākuma dzēšanai:</p>
            <textarea id="deleteReason" class="form-control" rows="3" placeholder="Iemesls..." required></textarea>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atcelt</button>
            <button type="button" class="btn btn-danger" id="confirmDelete">
            <i class="fas fa-trash me-1"></i> Apstiprināt dzēšanu
            </button>
        </div>
        </div>
    </div>
    </div>
<!-- Ban Confirmation Modal -->
<div class="modal fade" id="banConfirmModal" tabindex="-1" aria-labelledby="banConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="banConfirmModalLabel">Apstiprināt bloķēšanu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
            </div>
            <div class="modal-body">
                Vai tiešām vēlaties bloķēt šo lietotāju? Šo darbību nevar atsaukt.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atcelt</button>
                <button type="button" class="btn btn-danger" id="confirmBanUserBtn">Jā, bloķēt</button>
            </div>
        </div>
    </div>
</div>


    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    const weeklyLabels = <?= json_encode(array_keys($last7DaysCounts)) ?>;
    const weeklyData = <?= json_encode(array_values($last7DaysCounts)) ?>;

    const monthlyLabels = <?= json_encode(array_keys($last12MonthsCounts)) ?>;
    const monthlyData = <?= json_encode(array_values($last12MonthsCounts)) ?>;

    const reasonLabels = <?= json_encode(array_keys($topReasons)) ?>;
    const reasonData = <?= json_encode(array_values($topReasons)) ?>;
document.addEventListener("DOMContentLoaded", function () {
    const reportsCtx = document.getElementById("reportsChart").getContext("2d");

    // Initial chart with weekly data
    let currentLabels = weeklyLabels;
    let currentData = weeklyData;

    const reportsChart = new Chart(reportsCtx, {
        type: "line",
        data: {
            labels: currentLabels,
            datasets: [{
                label: "Ziņojumi",
                data: currentData,
                borderColor: "#4e73df",
                backgroundColor: "rgba(78, 115, 223, 0.05)",
                tension: 0.4,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: "#4e73df",
                borderWidth: 2,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: "#6c757d" }},
                y: { grid: { color: "#e5e5e5" }, ticks: { color: "#6c757d", beginAtZero: true }}
            }
        }
    });

    // Listen for dropdown changes
    document.getElementById("timeRangeSelect").addEventListener("change", function() {
        if (this.value === "7days") {
            reportsChart.data.labels = weeklyLabels;
            reportsChart.data.datasets[0].data = weeklyData;
            reportsChart.options.scales.x.ticks.callback = null; // reset callback if needed
            reportsChart.update();
        } else if (this.value === "12months") {
            // Format month labels like "2024-01" to "Jan 2024"
            const formattedLabels = monthlyLabels.map(m => {
                const parts = m.split("-");
                const month = new Date(parts[0], parts[1] - 1).toLocaleString('lv-LV', { month: 'short' });
                return `${month} ${parts[0]}`;
            });
            reportsChart.data.labels = formattedLabels;
            reportsChart.data.datasets[0].data = monthlyData;
            reportsChart.update();
        }
    });

    // Reasons Chart (unchanged)
    const reasonsCtx = document.getElementById("reasonsChart").getContext("2d");
    new Chart(reasonsCtx, {
        type: "pie",
        data: {
            labels: reasonLabels,
            datasets: [{
                data: reasonData,
                backgroundColor: ["#4e73df", "#1cc88a", "#f6c23e", "#e74a3b", "#36b9cc"],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        color: "#6c757d",
                        boxWidth: 12,
                        padding: 16
                    }
                }
            }
        }
    });
});

</script>

<script>


$(document).ready(function() {
    
  const currentPage = <?= $page ?>;
const totalPages = <?= $totalPages ?>;

function renderPagination(containerId, currentPage, totalPages, table = "") {
    const container = document.getElementById(containerId);
    if (!container) return;

    let html = '';

    // First & Prev buttons
    html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="1" data-table="${table}" class="pagination-btn btn btn-sm me-1">First</button>`;
    html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}" data-table="${table}" class="pagination-btn btn btn-sm me-1">Prev</button>`;

    // Calculate start and end page for dynamic range
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);

    if (endPage - startPage < 4) {
        if (startPage === 1) {
            endPage = Math.min(totalPages, startPage + 4);
        } else if (endPage === totalPages) {
            startPage = Math.max(1, endPage - 4);
        }
    }

    // Page number buttons with active highlight
    for (let i = startPage; i <= endPage; i++) {
        html += `<button ${i === currentPage ? 'disabled' : ''} data-page="${i}" data-table="${table}" class="pagination-btn btn btn-sm me-1">${i}</button>`;

    // Next & Last buttons
    html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}" data-table="${table}" class="pagination-btn btn btn-sm me-1">Next</button>`;
    html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${totalPages}" data-table="${table}" class="pagination-btn btn btn-sm">Last</button>`;

    container.innerHTML = html;
}

}

    renderPagination('pagination-controls', currentPage, totalPages);


        let selectedEventId = null;

        $('#openDeleteModal').on('click', function () {
            selectedEventId = $('#confirmDelete').data('event-id'); // set from view-report
            $('#deleteReason').val('');
            $('#deleteEventModal').modal('show');
        });

        $('#confirmDelete').on('click', function () {
            const reason = $('#deleteReason').val().trim();
            const eventId = $(this).data('event-id');

            if (!reason) {
                alert('Lūdzu, ievadiet dzēšanas iemeslu.');
                return;
            }

            $.post('report_manager.php', {
                ajax: true,
                action: 'delete_event',
                event_id: eventId,
                reason: reason
            }, function (response) {
                if (response.success) {
                    
                    const row = $(`.delete-event[data-id="${eventId}"]`).closest('tr');
                    
                    
                    const statusCell = row.find('td:nth-child(7)');
                    statusCell.html('<span class="badge bg-danger">Dzēsts</span>');

                    row.find('.delete-event').remove();

                    row.find('.resolve-report').prop('disabled', true).addClass('btn-secondary').removeClass('btn-success');

                    $('#openDeleteModal').hide();
                    $('#confirmDelete').hide();
                    $('#deleteReason').prop('disabled', true).val('Pasākums ir dzēsts');
                    $('#deleteEventModal .modal-body').prepend('<div class="alert alert-success">Pasākums ir veiksmīgi dzēsts.</div>');

                } else {
                    alert('Kļūda: ' + response.message);
                }
            }, 'json');
        });


            
            
          
    $('.view-report').on('click', function() {
        var reportId = $(this).data('id');
        var row = $(this).closest('tr');
        
        $('#reporterAvatar').attr('src', row.find('td:nth-child(2) img').attr('src'));
        $('#reporterName').text(row.find('td:nth-child(2)').text().trim());
        $('#reportDate').text(row.find('td:nth-child(6)').text().trim());
        $('#reportReason').text(row.find('td:nth-child(5)').attr('title'));
        
        $('#creatorAvatar').attr('src', row.find('td:nth-child(4) img').attr('src'));
        $('#creatorName').text(row.find('td:nth-child(4)').text().replace('BLOĶĒTS', '').trim());
        $('#creatorStatus').text(row.find('td:nth-child(4)').find('.badge').length ? 'Bloķēts lietotājs' : 'Aktīvs lietotājs');
        
        $('#eventTitle').text(row.find('td:nth-child(3)').text().trim());
        $('#eventDescription').text("Šis ir pasākuma pilnais apraksts, kas tika iegūts no datu bāzes. Pasākums: " + row.find('td:nth-child(3)').text().trim());
        $('#eventDateTime').text("15.12.2023 18:00");
        $('#eventLocation').text("Rīga, Latvija");
        
        $('#confirmDelete').data('event-id', row.find('.delete-event').data('id'));
        $('#confirmResolve').data('report-id', reportId);
        $('#banCreator').data('user-id', row.find('td:nth-child(4)').data('creator-id'));

        
        let isDeleted = row.find('td:nth-child(7)').find('.badge').hasClass('bg-danger');
        if (isDeleted) {
            $('#openDeleteModal').hide();
        } else {
            $('#openDeleteModal').show();
        }

        $('#reportDetailsModal').modal('show');
    });




    let selectedUserIdToBan = null;

    $('#banCreator').on('click', function () {
        selectedUserIdToBan = $(this).data('user-id');
        $('#banConfirmModal').modal('show');
    });


    $('#confirmBanUserBtn').on('click', function () {
        if (!selectedUserIdToBan) return;

        $.post('report_manager.php', {
            ajax: true,
            action: 'ban_user',
            user_id: selectedUserIdToBan
        }, function (response) {
            if (response.success) {
            
                $('#creatorStatus').text('Bloķēts lietotājs');
                $('#creatorInfo .badge').remove();
                $('#creatorInfo').append('<span class="badge bg-danger ms-2">BLOĶĒTS</span>');

                alert('Lietotājs ir bloķēts.');
            } else {
                alert('Kļūda: ' + response.message);
            }
            $('#banConfirmModal').modal('hide');
            selectedUserIdToBan = null;
        }, 'json');
    });
    $('#confirmResolve').on('click', function () {
        const reportId = $(this).data('report-id');
        if (!reportId) {
            alert('Ziņojuma ID nav pieejams.');
            return;
        }

        $.post('report_manager.php', {  
            ajax: true,
            action: 'resolve_report',
            report_id: reportId
        }, function (response) {
            if (response.success) {
                alert(response.message);

                const row = $(`button.resolve-report[data-id="${reportId}"]`).closest('tr');
                row.fadeOut(0, function() {
                    $(this).remove(); 
                });
                $('#reportDetailsModal').modal('hide');

            } else {
                alert('Kļūda: ' + response.message);
            }
        }, 'json');
    });


        document.getElementById('export-csv').addEventListener('click', function(e) {
            e.preventDefault();

            const table = document.getElementById('reportsTable');
            let csvContent = '\uFEFF'; // UTF-8 BOM

            // Get headers
            const headers = table.querySelectorAll('thead th');
            const headerRow = [];
            headers.forEach(th => headerRow.push('"' + th.innerText.trim().replace(/"/g, '""') + '"'));
            csvContent += headerRow.join(',') + '\r\n';

            // Get rows
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const cells = row.querySelectorAll('td');
                const rowData = [];
                cells.forEach(cell => {
                    let text = cell.innerText.trim().replace(/"/g, '""');
                    rowData.push('"' + text + '"');
                });
                csvContent += rowData.join(',') + '\r\n';
            });

            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'reports.csv';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        });
document.getElementById('print-reports').addEventListener('click', function(e) {
    e.preventDefault();

    const table = document.getElementById('reportsTable').outerHTML;
    const style = `
        <style>
            table { width: 100%; border-collapse: collapse; }
            th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
            th { background-color: #f8f9fa; }
            .action-buttons { display: none; }
            img.user-avatar { width: 30px; height: 30px; vertical-align: middle; margin-right: 8px; }
        </style>
    `;

    const printWindow = window.open('', '', 'width=900,height=600');
    printWindow.document.write(`
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Drukāt ziņojumus</title>
            ${style}
        </head>
        <body>
            <h3>Pēdējie ziņojumi</h3>
            ${table}
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.focus();
    printWindow.print();
});

            // Export CSV
            $('#export-csv').on('click', function() {
           
                alert('CSV eksportēšana tiks ģenerēta un lejupielādēta');
            });
        });
    </script>
</body>
</html>