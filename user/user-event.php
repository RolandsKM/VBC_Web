<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../css/templates/header.php'; 
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notikums</title>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../functions/script.js"></script>
</head>
<body id="bg">

<section id="my-event">
    <div class="container p-0 m-0">
        <a href="javascript:history.back()" class="btn mb-3">⬅ Atpakaļ</a>
        <input type="hidden" id="edit-event-id" value="<?= htmlspecialchars($_GET['id']) ?>">

        <div class="card shadow p-4" id="event-details">
            <!-- Event details will be loaded here -->
        </div>
    </div>
</section>

<section id="my-event-info">
    <div class="container">
        

        <!-- User Tables -->
        <div class="card mt-4">
            <div class="card-header bg-white">
                <ul class="nav nav-tabs card-header-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#waiting">
                            Gaida <span class="badge bg-warning" id="count-waiting-badge">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#accepted">
                            Apstiprināti <span class="badge bg-success" id="count-accepted-badge">0</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#denied">
                            Noraidīti <span class="badge bg-danger" id="count-denied-badge">0</span>
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content">
                    <!-- Waiting Tab -->
                    <div class="tab-pane fade show active" id="waiting">
                        <div class="table-responsive">
                            <table class="table table-hover" id="waiting-table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="select-all" data-table="waiting"></th>
                                        <th>#</th>
                                        <th>Lietotājs</th>
                                        <th>E-pasts</th>
                                        <th class="status-header">
                                            Pieteicies
                                            <i class="bi bi-chevron-down status-indicator"></i>
                                        </th>
                                        <th>Darbības</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div id="waiting-pagination" class="mt-3"></div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <select class="form-select batch-status" data-table="waiting">
                                    <option value="accepted">Apstiprināt</option>
                                    <option value="denied">Noraidīt</option>
                                </select>
                            </div>
                            <button class="btn btn-primary batch-update-btn" data-table="waiting">Atjaunināt izvēlētos</button>
                        </div>
                    </div>

                    <!-- Accepted Tab -->
                    <div class="tab-pane fade" id="accepted">
                        <div class="table-responsive">
                            <table class="table table-hover" id="accepted-table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="select-all" data-table="accepted"></th>
                                        <th>#</th>
                                        <th>Lietotājs</th>
                                        <th>E-pasts</th>
                                        <th class="status-header">
                                            Pieteicies
                                            <i class="bi bi-chevron-down status-indicator"></i>
                                        </th>
                                        <th>Darbības</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div id="accepted-pagination" class="mt-3"></div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <select class="form-select batch-status" data-table="accepted">
                                    <option value="denied">Noraidīt</option>
                                </select>
                            </div>
                            <button class="btn btn-primary batch-update-btn" data-table="accepted">Atjaunināt izvēlētos</button>
                        </div>
                    </div>

                    <!-- Denied Tab -->
                    <div class="tab-pane fade" id="denied">
                        <div class="table-responsive">
                            <table class="table table-hover" id="denied-table">
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" class="select-all" data-table="denied"></th>
                                        <th>#</th>
                                        <th>Lietotājs</th>
                                        <th>E-pasts</th>
                                        <th class="status-header">
                                            Pieteicies
                                            <i class="bi bi-chevron-down status-indicator"></i>
                                        </th>
                                        <th>Darbības</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div id="denied-pagination" class="mt-3"></div>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <div>
                                <select class="form-select batch-status" data-table="denied">
                                    <option value="accepted">Apstiprināt</option>
                                </select>
                            </div>
                            <button class="btn btn-primary batch-update-btn" data-table="denied">Atjaunināt izvēlētos</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- User Details Modal -->
<div class="modal fade" id="userDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Lietotāja informācija</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 text-center">
                        <img id="userProfilePic" src="../assets/default-profile.png" class="rounded-circle mb-3" style="width: 150px; height: 150px; object-fit: cover;">
                        <h4 id="userName" class="mb-2"></h4>
                        <p id="userEmail" class="text-muted"></p>
                    </div>
                    <div class="col-md-8">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title">Statistika</h5>
                                <div class="row text-center">
                                    <div class="col-6">
                                        <h6>Izveidotie pasākumi</h6>
                                        <p id="createdEvents" class="fs-4">0</p>
                                    </div>
                                    <div class="col-6">
                                        <h6>Pabeigtie pasākumi</h6>
                                        <p id="completedEvents" class="fs-4">0</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Papildu informācija</h5>
                                <p><strong>Atrašanās vieta:</strong> <span id="userLocation">Nav norādīts</span></p>
                                <p><strong>Reģistrējies:</strong> <span id="userCreatedAt"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

.table {
    border-collapse: separate;
    border-spacing: 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
    width: 100%;
    margin-bottom: 1rem;
    background-color: #fff;
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
    background-color: rgba(76, 175, 80, 0.05);
    transition: background-color 0.2s ease;
}

.table tbody tr:last-child td {
    border-bottom: none;
}

.view-user {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.2s ease;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    color: white;
}

.view-user:hover {
    background-color: var(--hover-color);
    border-color: var(--hover-color);
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.form-select {
    border-radius: 6px;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    transition: border-color 0.2s ease;
}

.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}

.pagination-controls {
    margin-top: 1.5rem;
    display: flex;
    justify-content: center;
    gap: 0.5rem;
}

.pagination-controls button {
    padding: 0.5rem 1rem;
    border: 1px solid var(--border-color);
    background-color: white;
    color: var(--text-color);
    border-radius: 6px;
    transition: all 0.2s ease;
}

.pagination-controls button:hover:not(:disabled) {
    background-color: var(--primary-color);
    color: white;
    border-color: var(--primary-color);
}

.pagination-controls button:disabled {
    background-color: #f5f5f5;
    color: #999;
    cursor: not-allowed;
}

.modal-body img {
    border: 3px solid #dee2e6;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-5px);
}

.status-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background: #fff url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e") no-repeat right 8px center;
    background-size: 16px;
    padding: 8px 32px 8px 12px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 14px;
    color: #495057;
    cursor: pointer;
    transition: all 0.2s ease;
}

.status-select:hover {
    border-color: #adb5bd;
}

.status-select:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.status-select option {
    padding: 8px;
}

.status-select option[value="waiting"] {
    color: #ffc107;
}

.status-select option[value="accepted"] {
    color: #28a745;
}

.status-select option[value="denied"] {
    color: #dc3545;
}

.status-select option[value="left"] {
    color: #6c757d;
}
</style>

<?php include '../main/footer.php'; ?>

<script>
$(document).ready(function() {
    const eventId = $('#edit-event-id').val();
    
    $.get(`../functions/event_functions.php?action=fetch_event_details&id=${eventId}`, function (data) {
        $('#event-details').html(data);
    });

    $.getJSON(`../functions/event_functions.php?action=fetch_event_info&id=${eventId}`, function (data) {
        $('#joined-count').text(data.total_joined);
    });
    
    loadJoinedUsers();
});
</script>

</body>
</html>
