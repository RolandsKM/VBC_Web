<?php

require_once '../functions/AdminController.php';
checkSuperAdminAccess();

if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
    header('Content-Type: application/json');
    $userId = $_POST['user_id'] ?? null;
    if ($userId) {
        try {
            global $pdo;
            $stmt = $pdo->prepare("UPDATE users SET deleted = 1 WHERE ID_user = ? AND role IN ('admin', 'mod')");
            $success = $stmt->execute([$userId]);
            echo json_encode(['success' => $success]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nav norādīts lietotāja ID.']);
    }
    exit;
}

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');

    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;
    $role = isset($_GET['role']) ? $_GET['role'] : '';
    $sortField = $_GET['sort'] ?? 'created_at';
    $sortOrder = $_GET['order'] ?? 'DESC';

    try {
        if ($role === 'mod' || $role === 'admin') {
            $total = getUsersCountByRole($role);
            $users = getPaginatedUsersByRole($role, $perPage, $offset, $sortField, $sortOrder);
            
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


    if (!in_array($role, ['mod', 'admin'])) {
        echo json_encode(['success' => false, 'message' => 'Nederīga loma!']);
        exit;
    }
    
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
    <title>VBC Admin | Admin Pārvalde</title>
    <link rel="stylesheet" href="admin.css" defer>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
    <?php include 'sidebar.php'; ?>
    
    <div class="main-content">
        <?php include 'header.php'; ?>
        
        <div class="container-fluid py-4">
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Admin Pārvalde</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                    <i class="fas fa-plus"></i> Pievienot jaunu admin/mod
                </button>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Moderatori</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item export-csv" href="#" data-table="mod">Eksportēt uz CSV</a></li>
                            <li><a class="dropdown-item print-table" href="#" data-table="mod">Drukāt</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Lietotājvārds</th>
                                    <th>Vārds</th>
                                    <th>Uzvārds</th>
                                    <th>E-pasts</th>
                                    <th>Izveidots</th>
                                    <th>Darbības</th>
                                </tr>
                            </thead>
                            <tbody id="mod-body"></tbody>
                        </table>
                        <div id="mod-pagination" class="pagination-container"></div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Administratori</h6>
                    <div class="dropdown no-arrow">
                        <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                            <li><a class="dropdown-item export-csv" href="#" data-table="admin">Eksportēt uz CSV</a></li>
                            <li><a class="dropdown-item print-table" href="#" data-table="admin">Drukāt</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Lietotājvārds</th>
                                    <th>Vārds</th>
                                    <th>Uzvārds</th>
                                    <th>E-pasts</th>
                                    <th>Izveidots</th>
                                    <th>Darbības</th>
                                </tr>
                            </thead>
                            <tbody id="admin-body"></tbody>
                        </table>
                        <div id="admin-pagination" class="pagination-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create mod/admin -->
<div class="modal fade" id="createUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pievienot jaunu admin/mod</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
            </div>
            <div class="modal-body">
                <form id="create-user-form">
                    <div class="mb-3">
                        <label for="username" class="form-label">Lietotājvārds</label>
                        <input type="text" class="form-control" id="username" name="username" required pattern="^[a-zA-Z0-9_]{3,20}$" title="3-20 burti, cipari vai pasvītras">
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Parole</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Apstiprini paroli</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Vārds</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="surname" class="form-label">Uzvārds</label>
                        <input type="text" class="form-control" id="surname" name="surname" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">E-pasts</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Loma</label>
                        <select class="form-select" id="role" name="role" required>
                            <option value="">Izvēlieties lomu</option>
                            <option value="mod">Moderators</option>
                            <option value="admin">Admins</option>
                        </select>
                    </div>
                    <div id="create-user-feedback" class="alert d-none"></div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atcelt</button>
                        <button type="submit" class="btn btn-primary">Izveidot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../functions/admin_script.js" defer></script>
</body>
</html>