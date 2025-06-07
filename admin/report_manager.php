<?php
require_once '../functions/ReportController.php';
checkModeratorAccess();

?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBC Admin | Ziņojumu Pārvaldība</title>
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
            --text-color: #5a5c69;
            --border-color: #e3e6f0;
            --hover-color: #2e59d9;
            --warning-color: #f6c23e;
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
        
        .badge {
            padding: 0.5em 1em;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
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
        
        .sortable {
            cursor: pointer;
            position: relative;
            padding-right: 1.5rem;
        }
        
        .sortable:hover {
            background-color: rgba(255,255,255,0.1);
        }
        
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        
        .report-details .section {
            margin-bottom: 1.5rem;
        }
        
        .report-details .section-header {
            border-radius: 0.5rem 0.5rem 0 0;
        }
        
        .report-details .section-body {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0 0 0.5rem 0.5rem;
        }
        
        .report-details .section-header h5 {
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .report-details p {
            margin-bottom: 0.75rem;
        }
        
        .report-details strong {
            color: #495057;
        }
        
        .report-details .bg-light {
            background-color: #f8f9fa !important;
        }
        
        .modal-footer .btn {
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .modal-footer .btn i {
            font-size: 0.9rem;
        }
        
        .modal-body::-webkit-scrollbar {
            width: 8px;
        }
        
        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        
        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            height: 100%;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            min-width: 280px;
        }
        
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
        }
        
        .stat-card .text-xs {
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .stat-card .h5 {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0.5rem 0 0;
            white-space: nowrap;
        }
        
        .stat-card .col-auto i {
            font-size: 2rem;
            opacity: 0.8;
            margin-left: 1rem;
        }

        .stat-card .row {
            flex-wrap: nowrap;
        }

        .stat-card .col {
            min-width: 0;
        }

        .stat-card .col-auto {
            flex-shrink: 0;
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
                    <h1 class="h3 mb-0 text-gray-800">Ziņojumu Pārvaldība</h1>
                    <div class="d-flex align-items-center">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" id="searchReports" class="form-control form-control-sm rounded-pill" placeholder="Meklēt pēc pasākuma vai veidotāja...">
                            <button class="btn btn-sm btn-outline-secondary rounded-pill ms-2" type="button" id="clearReportSearch">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold">Ziņojumu Saraksts</h6>
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
                                        <th class="sortable" data-sort="title">
                                            Pasākuma nosaukums
                                            <i class="fas fa-sort"></i>
                                        </th>
                                        <th class="sortable" data-sort="creator_username">
                                            Pasākuma veidotājs
                                            <i class="fas fa-sort"></i>
                                        </th>
                                        <th class="sortable" data-sort="reported_at">
                                            Ziņojuma datums
                                            <i class="fas fa-sort"></i>
                                        </th>
                                        <th>Status</th>
                                        <th>Darbības</th>
                                    </tr>
                                </thead>
                                <tbody id="reports-body">
                                </tbody>
                            </table>
                            <div id="reports-pagination" class="pagination-container"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="reportDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Ziņojuma detaļas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Dzēst pasākumu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="deleteReason" class="form-label">Iemesls:</label>
                        <select class="form-select" id="deleteReason">
                            <option value="">Izvēlieties iemeslu</option>
                        </select>
                    </div>
                    <div class="mb-3" id="customReasonContainer" style="display: none;">
                        <label for="customReason" class="form-label">Cits iemesls:</label>
                        <textarea class="form-control" id="customReason" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atcelt</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="fas fa-trash"></i> Dzēst
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/report_manager.js" defer></script>
</body>
</html>