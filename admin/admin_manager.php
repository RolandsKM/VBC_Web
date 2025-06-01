<?php

require_once '../functions/AdminController.php';
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');

    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;
    $role = isset($_GET['role']) ? $_GET['role'] : '';

    try {
        if ($role === 'moderator' || $role === 'admin') {
            $total = getUsersCountByRole($role);
            $users = getPaginatedUsersByRole($role, $perPage, $offset);
            
            echo json_encode([
                'success' => true,
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage,
                'role' => $role
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid role specified']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['name'], $_POST['surname'], $_POST['email'], $_POST['role'])) {
    header('Content-Type: application/json');
    require_once '../functions/AdminController.php';

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = trim($_POST['name']);
    $surname = trim($_POST['surname']);
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $role = $_POST['role'];

    // Validate role
    if (!in_array($role, ['mod', 'admin'])) {
        echo json_encode(['success' => false, 'message' => 'Nederīga loma!']);
        exit;
    }
    // Basic validations
    if (empty($username) || empty($password) || empty($confirm_password) || empty($name) || empty($surname) || !$email) {
        echo json_encode(['success' => false, 'message' => 'Lūdzu, aizpildiet visus laukus pareizi!']);
        exit;
    }
    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'message' => 'Paroles nesakrīt!']);
        exit;
    }
    if (!preg_match("/^[a-zA-Z0-9_]{3,20}$/", $username)) {
        echo json_encode(['success' => false, 'message' => 'Lietotājvārds nav derīgs!']);
        exit;
    }
    if (strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Parolei jābūt vismaz 8 simbolus garai!']);
        exit;
    }

    try {
        global $pdo;
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT ID_user FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Lietotājvārds vai e-pasts jau ir reģistrēts!']);
            exit;
        }

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert = $pdo->prepare("INSERT INTO users (username, password, name, surname, email, profile_pic, location, role) VALUES (?, ?, ?, ?, ?, NULL, NULL, ?)");
        $success = $insert->execute([$username, $hashed_password, $name, $surname, $email, $role]);

        if ($success) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Neizdevās izveidot lietotāju!']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'DB kļūda: ' . $e->getMessage()]);
    }
    exit;
}


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
        .drop-table a{
   color:#fff;
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
    <h1 class="h3 mb-0 text-gray-800">Admiņa pārvaldība Panelis</h1>
</div>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold">Modemoderators</h6>
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
                        <th>Sludinājuma ID</th>
                        <th>Nosaukums</th>
                        <th>Izveidoja</th>
                        <th>Dzēsts</th>
                        <th>Izveidots</th>
                        <th>Darbības</th>
                    </tr>
                </thead>
                <tbody id="mod-body"></tbody>
<div id="mod-pagination" class="pagination-container"></div>
            </table>
           
        </div>
    </div>
</div>
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
        <h6 class="m-0 font-weight-bold">Admins</h6>
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
                        <th>Nr</th>
                        <th>Nosaukums</th>
                        <th>Izveidoja</th>
                        <th>Dzēsts</th>
                        <th>Izveidots</th>
                        <th>Darbības</th>
                    </tr>
                </thead>
<tbody id="admin-body"></tbody>
<div id="admin-pagination" class="pagination-container"></div>
            </table>
            <div id="events-pagination" class="pagination-container"></div>
        </div>
    </div>
</div>
<div class="card shadow mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold">Pievienot jaunu lietotāju (Admin vai Moderators)</h6>
    </div>
    <div class="card-body">
        <form id="create-user-form">
            <div class="row g-3">
                <div class="col-md-4">
                    <label for="new-username" class="form-label">Lietotājvārds</label>
                    <input type="text" class="form-control" id="new-username" name="username" required pattern="^[a-zA-Z0-9_]{3,20}$" title="3-20 burti, cipari vai pasvītras">
                </div>
                <div class="col-md-4">
                    <label for="new-password" class="form-label">Parole</label>
                    <input type="password" class="form-control" id="new-password" name="password" required minlength="8">
                </div>
                <div class="col-md-4">
                    <label for="new-confirm-password" class="form-label">Apstiprini paroli</label>
                    <input type="password" class="form-control" id="new-confirm-password" name="confirm_password" required minlength="8">
                </div>
                <div class="col-md-4">
                    <label for="new-name" class="form-label">Vārds</label>
                    <input type="text" class="form-control" id="new-name" name="name" required>
                </div>
                <div class="col-md-4">
                    <label for="new-surname" class="form-label">Uzvārds</label>
                    <input type="text" class="form-control" id="new-surname" name="surname" required>
                </div>
                <div class="col-md-4">
                    <label for="new-email" class="form-label">E-pasts</label>
                    <input type="email" class="form-control" id="new-email" name="email" required>
                </div>
                <div class="col-md-4">
                    <label for="new-role" class="form-label">Loma</label>
                    <select class="form-select" id="new-role" name="role" required>
                        <option value="">Izvēlieties lomu</option>
                        <option value="mod">Moderators</option>
                        <option value="admin">Admins</option>
                    </select>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" class="btn btn-success">Izveidot lietotāju</button>
            </div>
            <div id="create-user-feedback" class="mt-2"></div>
        </form>
    </div>
</div>

        </div>
    </div>
</div>

<script>
document.getElementById('create-user-form').addEventListener('submit', function(e) {
    e.preventDefault();

    const feedback = document.getElementById('create-user-feedback');
    feedback.innerHTML = '';
    
    const formData = new FormData(this);

    // Basic client-side check for password match
    if (formData.get('password') !== formData.get('confirm_password')) {
        feedback.innerHTML = '<div class="alert alert-danger">Paroles nesakrīt!</div>';
        return;
    }

    fetch('admin_manager.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            feedback.innerHTML = '<div class="alert alert-success">Lietotājs izveidots veiksmīgi!</div>';
            this.reset();
            // Refresh user lists
            fetchUsers('moderator', 1);
            fetchUsers('admin', 1);
        } else {
            feedback.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(err => {
        feedback.innerHTML = `<div class="alert alert-danger">Kļūda: ${err.message}</div>`;
    });
});

const perPage = 5;

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('lv-LV') + ' ' + date.toLocaleTimeString('lv-LV', { hour: '2-digit', minute: '2-digit' });
}

function renderUsers(users, tbodyId) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;

    if (users.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6">Nav lietotāju.</td></tr>';
        return;
    }

    tbody.innerHTML = users.map((user, index) => `
        <tr class="${user.deleted ? 'table-danger' : ''}">
            <td>${user.ID_user}</td>
            <td>${escapeHtml(user.username)}</td>
            <td>${escapeHtml(user.name + ' ' + user.surname)}</td>
            <td>${user.deleted ? 'Jā' : 'Nē'}</td>
            <td>${formatDate(user.created_at)}</td>
            <td>
                <!-- Example action buttons -->
                <button class="btn btn-sm btn-primary" data-userid="${user.ID_user}">Skatīt</button>
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

function fetchUsers(role, page = 1) {
    fetch(`admin_manager.php?ajax=1&role=${role}&page=${page}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('Kļūda: ' + data.message);
                return;
            }
            const totalPages = Math.ceil(data.total / data.perPage);
            if (role === 'moderator') {
                renderUsers(data.users, 'mod-body');
                renderPagination('mod-pagination', data.page, totalPages);
            } else if (role === 'admin') {
                renderUsers(data.users, 'admin-body');
                renderPagination('admin-pagination', data.page, totalPages);
            }
        })
        .catch(err => alert('Kļūda ielādējot datus: ' + err));
}

document.getElementById('mod-pagination').addEventListener('click', (e) => {
    if (e.target.classList.contains('pagination-btn')) {
        const page = parseInt(e.target.getAttribute('data-page'));
        if (!isNaN(page)) fetchUsers('moderator', page);
    }
});

document.getElementById('admin-pagination').addEventListener('click', (e) => {
    if (e.target.classList.contains('pagination-btn')) {
        const page = parseInt(e.target.getAttribute('data-page'));
        if (!isNaN(page)) fetchUsers('admin', page);
    }
});

// Initial load
fetchUsers('moderator', 1);
fetchUsers('admin', 1);
</script>
</body>
</html>