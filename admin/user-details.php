<?php

require_once '../functions/AdminController.php';
checkAdminAccess();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user_info'])) {
        $userId = $_POST['user_id'];
        $username = trim($_POST['username']);
        $name = trim($_POST['name']);
        $surname = trim($_POST['surname']);
        
        if ($username && $name && $surname) {
            updateUserInfo($userId, $username, $name, $surname);
            header("Location: user-details.php?id=" . $userId);
            exit();
}
    }
    if (isset($_POST['delete_event'])) {
    $eventId = $_POST['event_id'];
    $reason = trim($_POST['delete_reason']);
   $adminId = $_SESSION['ID_user'];

    if ($eventId && $reason) {
        deleteEventWithReason($eventId, $adminId, $reason);
        header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $_GET['id']);
        exit;
        }
    }
}

if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user_info'])) {
        $userId = $_POST['user_id'];
        $username = trim($_POST['username']);
        $name = trim($_POST['name']);
        $surname = trim($_POST['surname']);
        
        if ($username && $name && $surname) {
            $success = updateUserInfo($userId, $username, $name, $surname);
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'User information updated successfully' : 'Failed to update user information',
                'user' => [
                    'username' => $username,
                    'name' => $name,
                    'surname' => $surname
                ]
            ]);
            exit;
        }
    }
    $userId = $_GET['id'] ?? null;
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $perPage = 5;
    $offset = ($page - 1) * $perPage;
    $table = $_GET['table'] ?? '';
    $sortField = $_GET['sort'] ?? 'created_at';
    $sortOrder = $_GET['order'] ?? 'DESC';

    if (!$userId) {
        echo json_encode(['success' => false, 'message' => 'Missing user ID']);
        exit;
    }

    try {
        if ($table === 'created') {
            $events = getPaginatedEventsCreatedByUser($userId, $perPage, $offset, $sortField, $sortOrder);
            $total = getEventsCreatedByUserCount($userId);
        } elseif ($table === 'volunteered') {
            $events = getPaginatedEventsUserVolunteered($userId, $perPage, $offset, $sortField, $sortOrder);
            $total = getEventsUserVolunteeredCount($userId);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid table']);
            exit;
        }

        $totalPages = ceil($total / $perPage);

        echo json_encode([
            'success' => true,
            'events' => $events,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'totalPages' => $totalPages
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBC-Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
        
        .admin-body {
            padding: 1rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            margin: 1rem;
        }

        .container {
            width: 100%;
            padding-right: 15px;
            padding-left: 15px;
            margin-right: auto;
            margin-left: auto;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -15px;
            margin-left: -15px;
        }
        
        .col-md-8 {
            flex: 0 0 100%;
            max-width: 100%;
            padding: 0 15px;
        }

        .d-flex {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .user-info-table {
            flex: 1;
            min-width: 300px;
        }

        .stats-cards {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }

        .stat-card {
            flex: 1;
            min-width: 250px;
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin-bottom: 1rem;
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin: 1rem 0;
        }

        .action-buttons .btn {
            flex: 1;
            min-width: 120px;
            max-width: 200px;
        }

        @media (min-width: 768px) {
            .main-content {
                /* padding: 1.5rem; */
            }

            .admin-body {
                padding: 1.5rem;
                margin: 1.5rem;
            }

            .col-md-8 {
                flex: 0 0 66.666667%;
                max-width: 66.666667%;
            }

            .d-flex {
                flex-wrap: nowrap;
            }

            .stats-cards {
                margin-top: 0;
            }
        }

        @media (max-width: 767px) {
            .table th, .table td {
                padding: 0.5rem;
                font-size: 0.9rem;
            }

            .section-title {
                font-size: 1.2rem;
                margin-bottom: 1rem;
            }

            .stat-card {
                min-width: 100%;
            }

            .action-buttons .btn {
                width: 100%;
                max-width: none;
            }

            .pagination-container {
                flex-wrap: wrap;
            }

            .pagination-btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.9rem;
            }
        }
        
        .card-header-style {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-radius: 8px 8px 0 0 !important;
            padding: 1.2rem 1.5rem;
        }
        
        .table-header-style {
            background-color: var(--primary-color);
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
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
        
        
        .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .btn-primary-style {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary-style:hover {
            background-color: var(--hover-color);
            border-color: var(--hover-color);
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .status-badge {
            padding: 0.5em 1em;
            font-size: 0.8rem;
            font-weight: 600;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active {
            background-color: var(--success-color);
            color: white;
        }
        
        .status-deleted {
            background-color: var(--danger-color);
            color: white;
        }
        
        .status-pending {
            background-color: var(--warning-color);
            color: #212529;
        }
        
        .status-rejected {
            background-color: var(--danger-color);
            color: white;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 0.8rem;
            margin-bottom: 2rem;
            font-size: 1.5rem;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: var(--primary-color);
        }
        
        .user-info-table {
            width: 100%;
            max-width: 600px;
            margin-bottom: 2rem;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
        
        .user-info-table th {
            background-color: var(--primary-color);
            color: white;
            padding: 1rem;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
        
        .user-info-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
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
        
        .modal-content {
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .modal-header {
            border-radius: 12px 12px 0 0;
        }
        
        .form-select {
            border-radius: 6px;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            transition: border-color 0.2s ease;
        }
        
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
        }

        .stat-card:nth-child(1) .stat-icon {
            background: var(--primary-color);
        }

        .stat-card:nth-child(2) .stat-icon {
            background: var(--info-color);
        }

        .stat-card:nth-child(3) .stat-icon {
            background: var(--success-color);
        }

        .user-info-card {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .user-info-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .user-info-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }

        .info-item {
            background: #f8f9fa;
            padding: 1.25rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.05);
            position: relative;
            overflow: hidden;
        }

        .info-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: #4e73df;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .info-item:hover::before {
            opacity: 1;
        }

        .info-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .info-value {
            font-size: 1rem;
            color: #2d3748;
            font-weight: 500;
        }

        .user-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .status-active {
            background: #e6f4ea;
            color: #1e7e34;
        }

        .status-banned {
            background: #fbe9e7;
            color: #d32f2f;
        }

        .status-deleted {
            background: #f5f5f5;
            color: #757575;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        @media (max-width: 992px) {
            .admin-layout {
                padding: 0 !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }

            .container-fluid {
                padding: 1rem !important;
            }
        }

        @media (max-width: 768px) {
            .user-info-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .user-info-content {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                justify-content: center;
            }

            .container-fluid {
                padding: 0.5rem !important;
            }
        }

        @media (max-width: 576px) {
            .user-info-card {
                padding: 1rem;
                margin: 0.5rem;
                border-radius: 10px;
            }

            .info-item {
                padding: 1rem;
            }

            .container-fluid {
                padding: 0.25rem !important;
            }
        }

        .user-info-section {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .user-info-header {
            display: flex;
            align-items: center;
            gap: 2rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }

        .user-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .info-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .info-group:hover {
            background: #f0f2f5;
            transform: translateY(-2px);
        }

        .info-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        .info-value {
            font-size: 1.25rem;
            color: #2d3748;
            font-weight: 600;
        }

        .user-status {
            display: inline-block;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .status-active {
            background: #e6f4ea;
            color: #1e7e34;
        }

        .status-banned {
            background: #fbe9e7;
            color: #d32f2f;
        }

        .status-deleted {
            background: #f5f5f5;
            color: #757575;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-top: 1rem;
        }

        .profile-pic {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .profile-pic:hover {
            transform: scale(1.05);
        }

        @media (max-width: 992px) {
            .admin-layout {
                padding: 0 !important;
            }
            
            .main-content {
                margin-left: 0 !important;
                width: 100% !important;
                padding: 0 !important;
            }

            .container-fluid {
                padding: 1rem !important;
            }
        }

        @media (max-width: 768px) {
            .user-info-header {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .user-info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                justify-content: center;
            }

            .container-fluid {
                padding: 0.5rem !important;
            }
        }

        @media (max-width: 576px) {
            .user-info-section {
                padding: 1rem;
                margin: 0.5rem;
                border-radius: 10px;
            }

            .container-fluid {
                padding: 0.25rem !important;
            }
        }

        .truncate-text {
            max-width: 400px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            cursor: help;
            line-height: 1.4;
            max-height: 2.8em;
        }

        .truncate-text:hover {
            position: relative;
        }

        .truncate-text:hover::after {
            content: attr(data-full-text);
            position: absolute;
            left: 0;
            top: 100%;
            background: #333;
            color: white;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 14px;
            white-space: normal;
            max-width: 400px;
            z-index: 1000;
            line-height: 1.4;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        @media (max-width: 768px) {
            .truncate-text {
                max-width: 300px;
            }
            .truncate-text:hover::after {
                max-width: 300px;
            }
        }

        @media (max-width: 576px) {
            .truncate-text {
                max-width: 200px;
            }
            .truncate-text:hover::after {
                max-width: 200px;
            }
        }

        .deleted-row { background-color: #f0f0f0; }
        .approved-row { background-color: #d4edda; }
        .pending-row { background-color: #fff3cd; }
        .rejected-row { background-color: #f8d7da; }

        .pagination-container {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 5px;
        }
        
        .pagination-btn {
            padding: 0.5rem 1rem;
            border: 1px solid #4CAF50;
            background-color: white;
            color: #4CAF50;
            border-radius: 6px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        
        .pagination-btn:hover:not(:disabled) {
            background-color: #4CAF50;
            color: white;
        }
        
        .pagination-btn:disabled {
            background-color: #f5f5f5;
            color: #999;
            cursor: not-allowed;
        }
        
        .pagination-btn.active {
            background-color: #4CAF50;
            color: white;
        }

        .edit-btn {
            background: none;
            border: none;
            color: #6c757d;
            padding: 0;
            margin-left: 5px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .edit-btn:hover {
            color: #495057;
        }
        
        .action-btn {
            background: none;
            border: none;
            padding: 0;
            margin-left: 3px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .save-btn {
            color: #28a745;
        }
        
        .save-btn:hover {
            color: #218838;
        }
        
        .cancel-btn {
            color: #6c757d;
        }
        
        .cancel-btn:hover {
            color: #495057;
        }
        
        .edit-input {
            display: inline-block;
            width: auto;
            padding: 0.2rem 0.4rem;
            font-size: 0.9rem;
            margin: 0 3px;
        }

        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            border-radius: 4px;
            color: white;
            font-size: 14px;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        .notification.success {
            background-color: #28a745;
        }

        .notification.error {
            background-color: #dc3545;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>
</head>
<body>
<div class="admin-layout">

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <?php include 'header.php'; ?>

        <div class="admin-body">
            <div class="container-fluid py-4">
                <div class="row">
                    <div class="col-12">
                <h4 class="section-title">Lietotāja informācija</h4>
                        <div class="user-info-section">
                            <div class="user-info-header">
                                <img src="<?= $user['profile_pic'] ? '../functions/assets/' . htmlspecialchars($user['profile_pic']) : '../functions/assets/default-profile.png' ?>" 
                                     alt="Profile Picture" class="profile-pic">
                                <div>
                                    <h2 class="mb-2"><?= htmlspecialchars($user['username']) ?></h2>
                                    <p class="text-muted mb-0"><?= htmlspecialchars($user['email']) ?></p>
                                    <div class="user-status <?= $user['deleted'] ? 'status-deleted' : ($user['banned'] ? 'status-banned' : 'status-active') ?>">
                                        <?= $user['deleted'] ? 'Dzēsts' : ($user['banned'] ? 'Bloķēts' : 'Aktīvs') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="user-info-grid">
                                <div class="info-group">
                                    <div class="info-label">ID</div>
                                    <div class="info-value"><?= htmlspecialchars($user['ID_user']) ?></div>
                                    </div>
                                <div class="info-group">
                                    <div class="info-label">Lietotājvārds</div>
                                    <div class="info-value">
                                        <span id="username-display"><?= htmlspecialchars($user['username']) ?></span>
                                        <button class="edit-btn" onclick="toggleEdit('username')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form id="username-form" method="POST" class="d-none d-inline">
                                            <input type="text" name="username" class="edit-input" value="<?= htmlspecialchars($user['username']) ?>">
                                            <input type="hidden" name="user_id" value="<?= $user['ID_user'] ?>">
                                            <input type="hidden" name="name" value="<?= htmlspecialchars($user['name']) ?>">
                                            <input type="hidden" name="surname" value="<?= htmlspecialchars($user['surname']) ?>">
                                            <input type="hidden" name="update_user_info" value="1">
                                            <button type="submit" class="action-btn save-btn">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="action-btn cancel-btn" onclick="toggleEdit('username')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Vārds</div>
                                    <div class="info-value">
                                        <span id="name-display"><?= htmlspecialchars($user['name']) ?></span>
                                        <button class="edit-btn" onclick="toggleEdit('name')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form id="name-form" method="POST" class="d-none d-inline">
                                            <input type="text" name="name" class="edit-input" value="<?= htmlspecialchars($user['name']) ?>">
                                            <input type="hidden" name="user_id" value="<?= $user['ID_user'] ?>">
                                            <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                            <input type="hidden" name="surname" value="<?= htmlspecialchars($user['surname']) ?>">
                                            <input type="hidden" name="update_user_info" value="1">
                                            <button type="submit" class="action-btn save-btn">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="action-btn cancel-btn" onclick="toggleEdit('name')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                    </div>
                                <div class="info-group">
                                    <div class="info-label">Uzvārds</div>
                                    <div class="info-value">
                                        <span id="surname-display"><?= htmlspecialchars($user['surname']) ?></span>
                                        <button class="edit-btn" onclick="toggleEdit('surname')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form id="surname-form" method="POST" class="d-none d-inline">
                                            <input type="text" name="surname" class="edit-input" value="<?= htmlspecialchars($user['surname']) ?>">
                                            <input type="hidden" name="user_id" value="<?= $user['ID_user'] ?>">
                                            <input type="hidden" name="username" value="<?= htmlspecialchars($user['username']) ?>">
                                            <input type="hidden" name="name" value="<?= htmlspecialchars($user['name']) ?>">
                                            <input type="hidden" name="update_user_info" value="1">
                                            <button type="submit" class="action-btn save-btn">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button type="button" class="action-btn cancel-btn" onclick="toggleEdit('surname')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                    </div>
                                <div class="info-group">
                                    <div class="info-label">Loma</div>
                                    <div class="info-value"><?= htmlspecialchars($user['role']) ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Reģistrācijas Datums</div>
                                    <div class="info-value"><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></div>
                                    </div>
                                <div class="info-group">
                                    <div class="info-label">Atrašanās Vieta</div>
                                    <div class="info-value"><?= htmlspecialchars($user['location'] ?? 'Nav norādīta') ?></div>
                                    </div>
                                <div class="info-group">
                                    <div class="info-label">Izveidotie Pasākumi</div>
                                    <div class="info-value"><?= count($eventsCreated) ?></div>
                                </div>
                                <div class="info-group">
                                    <div class="info-label">Pieteikumi</div>
                                    <div class="info-value"><?= count($volunteeredEvents) ?></div>
                            </div>
                        </div>

                            <div class="action-buttons">
                                <?php if (!$user['deleted']): ?>
                                    <?php if ($user['banned']): ?>
                                        <button type="button" onclick="unbanUser(<?= $user['ID_user'] ?>)" class="btn btn-success">
                                            Atbloķēt
                                        </button>
                    <?php else: ?>
                                        <button type="button" onclick="banUser(<?= $user['ID_user'] ?>)" class="btn btn-warning">
                                            Bloķēt
                                        </button>
                    <?php endif; ?>
                                    <button type="button" onclick="deleteUser(<?= $user['ID_user'] ?>)" class="btn btn-danger">
                                        Dzēst
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="btn btn-secondary" disabled>Dzēsts</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <h4 class="section-title">Lietotāja izveidotie pasākumi</h4>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Nosaukums</th>
                                <th>Apraksts</th>
                                <th>Datums</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="created-events-body">
                            <?php foreach ($eventsCreated as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event['title']) ?></td>
                                <td>
                                    <div class="truncate-text" data-full-text="<?= htmlspecialchars($event['description']) ?>">
                                        <?= htmlspecialchars($event['description']) ?>
                                    </div>
                                </td>
                                <td><?= date('d.m.Y H:i', strtotime($event['date'])) ?></td>
                                <td>
                                    <span class="badge <?= $event['deleted'] ? 'bg-danger' : 'bg-success' ?>">
                                        <?= $event['deleted'] ? 'Dzēsts' : 'Aktīvs' ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="created-events-pagination"></div>

                <div class="table-responsive">
                    <h4 class="section-title">Pievienotie pasākumi kā brīvprātīgais</h4>
                    <table class="table">
                        <thead>
                            <tr class="table-header-style">
                                <th class="sortable" data-sort="title">Nosaukums</th>
                                <th class="sortable" data-sort="description">Apraksts</th>
                                <th class="sortable" data-sort="date">Datums</th>
                                <th class="sortable" data-sort="status">Statuss</th>
                            </tr>
                        </thead>
                        <tbody id="volunteered-events-body">
                           
                        </tbody>
                    </table>
                </div>
                <div class="pagination-container" id="volunteered-events-pagination"></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteEventModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" id="deleteEventForm">
            <input type="hidden" name="event_id" id="event_id">
            <div class="modal-content">
                <div class="modal-header card-header-style">
                    <h5 class="modal-title">Dzēst pasākumu</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Aizvērt"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="delete_reason" class="form-label">Iemesls dzēšanai</label>
                        <select class="form-select" name="delete_reason" required>
                            <option value="">-- Izvēlies iemeslu --</option>
                            <option value="Spam">Spams</option>
                            <option value="Nepiemērots saturs">Nepiemērots saturs</option>
                            <option value="Nepareiza kategorija / dublikāts">Nepareiza kategorija / dublikāts</option>
                            <option value="Cits">Cits</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atcelt</button>
                    <button type="submit" name="delete_event" class="btn btn-danger">Dzēst</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', () => {
    const userId = new URLSearchParams(window.location.search).get('id');
    if (!userId) return;

    let currentPage = {
        created: 1,
        volunteered: 1
    };

    let currentSortField = 'created_at';
    let currentSortOrder = 'DESC';

    function fetchEvents(table, page, sortField = currentSortField, sortOrder = currentSortOrder) {
        const tableId = table === 'created' ? 'created-events-body' : 'volunteered-events-body';
        const tbody = document.getElementById(tableId);
        
        if (!tbody) return;

        tbody.innerHTML = '<tr><td colspan="4" class="text-center">Ielādē...</td></tr>';

        
        currentPage[table] = page;

        fetch(`user-details.php?ajax=1&id=${userId}&table=${table}&page=${page}&sort=${sortField}&order=${sortOrder}`)
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Kļūda ielādējot datus</td></tr>';
                    return;
                }

                const paginationId = table === 'created' ? 'created-events-pagination' : 'volunteered-events-pagination';
                
                renderEvents(tableId, data.events, table === 'volunteered');
                renderPagination(paginationId, data.page, data.totalPages, table);
            })
            .catch(error => {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">Kļūda ielādējot datus</td></tr>';
            });
    }

    function renderEvents(tableId, events, isVolunteered = false) {
        const tbody = document.getElementById(tableId);
        if (!tbody) return;

        if (!Array.isArray(events) || events.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="text-center">Nav pasākumu.</td></tr>';
            return;
        }

        const rows = events.map(event => {
            if (!event) return '';

            const statusClass = event.deleted ? 'status-deleted' : 
                (isVolunteered ? 
                    (event.status === 'approved' ? 'status-active' : 
                    (event.status === 'pending' ? 'status-pending' : 
                    (event.status === 'rejected' ? 'status-rejected' : ''))) : 
                    'status-active');
            
            const statusText = event.deleted ? 'Dzēsts' : 
                (isVolunteered ? 
                    ucfirst(event.status) + (event.deleted ? ' / Dzēsts' : '') : 
                    'Aktīvs');

            const rowClass = event.deleted ? 'deleted-row' : 
                (isVolunteered ? 
                    (event.status === 'approved' ? 'approved-row' : 
                    (event.status === 'pending' ? 'pending-row' : 
                    (event.status === 'rejected' ? 'rejected-row' : ''))) : '');

            return `
                <tr data-event-id="${event.ID_Event}" class="${rowClass}">
                    <td>${escapeHtml(event.title || '')}</td>
                    <td>${escapeHtml(event.description || '')}</td>
                    <td>${escapeHtml(event.date || '')}</td>
                    <td class="status-cell">
                        <span class="status-badge ${statusClass}">${statusText}</span>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.innerHTML = rows;
    }

function renderPagination(containerId, currentPage, totalPages, table) {
    const container = document.getElementById(containerId);
    if (!container) return;

    let html = '';
    
       
    html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="1" data-table="${table}" class="pagination-btn btn btn-sm me-1">First</button>`;
    html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}" data-table="${table}" class="pagination-btn btn btn-sm me-1">Prev</button>`;

        
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
        html += `<button ${i === currentPage ? 'disabled' : ''} data-page="${i}" data-table="${table}" class="pagination-btn btn btn-sm me-1">${i}</button>`;
    }

       
    html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}" data-table="${table}" class="pagination-btn btn btn-sm me-1">Next</button>`;
    html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${totalPages}" data-table="${table}" class="pagination-btn btn btn-sm">Last</button>`;

    container.innerHTML = html;
}

    
    fetchEvents('created', 1);
    fetchEvents('volunteered', 1);

    document.body.addEventListener('click', function(e) {
        if (e.target.classList.contains('pagination-btn')) {
            const page = parseInt(e.target.getAttribute('data-page'));
            const table = e.target.getAttribute('data-table');
            if (!isNaN(page)) {
                fetchEvents(table, page, currentSortField, currentSortOrder);
            }
        }
    });


    document.querySelectorAll('.sortable').forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', () => {
            const table = header.closest('table').querySelector('tbody').id === 'created-events-body' ? 'created' : 'volunteered';
            const sortField = header.getAttribute('data-sort');
            const currentOrder = header.getAttribute('data-order') || 'desc';
            const newOrder = currentOrder === 'desc' ? 'asc' : 'desc';
            
            header.setAttribute('data-order', newOrder);
            const headerText = header.textContent.replace(/[↑↓]/, '').trim();
            header.innerHTML = headerText + (newOrder === 'asc' ? ' ↑' : ' ↓');
            
            
            currentSortField = sortField;
            currentSortOrder = newOrder;
            
           
            fetchEvents(table, currentPage[table], sortField, newOrder);
        });
    });
});

function undeleteEvent(eventId) {
    if (!confirm('Vai tiešām vēlaties atjaunot šo sludinājumu?')) {
        return;
    }

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
            action: 'undelete_event',
            event_id: eventId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
           
            const rows = document.querySelectorAll(`tr[data-event-id="${eventId}"]`);
            rows.forEach(row => {
              
                row.classList.remove('deleted-row');
                
              
                const statusBadge = row.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = "status-badge status-active";
                    statusBadge.textContent = "Aktīvs";
                }
                
               
                const actionCell = row.querySelector('.action-cell');
                if (actionCell) {
                    actionCell.innerHTML = `
                        <button class="btn btn-sm btn-danger" onclick="showDeleteModal(${eventId})">Dzēst</button>
            `;
                }
            });
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert("Kļūda: Neizdevās atjaunot sludinājumu");
});
}

 
document.addEventListener('DOMContentLoaded', function() {
    const deleteEventForm = document.getElementById('deleteEventForm');
    if (deleteEventForm) {
        deleteEventForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const eventId = this.querySelector('input[name="event_id"]').value;
            const reason = this.querySelector('select[name="delete_reason"]').value;
            
            if (!reason) {
                alert('Lūdzu, izvēlies iemeslu dzēšanai');
                return;
            }

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
                    action: 'delete_event',
                    event_id: eventId,
                    reason: reason
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
                   
                    const rows = document.querySelectorAll(`tr[data-event-id="${eventId}"]`);
                    rows.forEach(row => {
                      
                        row.classList.add('deleted-row');
                      
                        const statusBadge = row.querySelector('.status-badge');
                        if (statusBadge) {
                            statusBadge.className = "status-badge status-deleted";
                            statusBadge.textContent = "Dzēsts";
                        }
                        
                        const actionCell = row.querySelector('.action-cell');
                        if (actionCell) {
                            actionCell.innerHTML = `
                                <button class="btn btn-sm btn-success" onclick="undeleteEvent(${eventId})">Atjaunot</button>
            `;
                        }
                    });

                 
                    this.reset();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('deleteEventModal'));
                    if (modal) {
                        modal.hide();
                    }
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
            })
            .catch(error => {
                console.error('Error:', error);
                alert("Kļūda: Neizdevās dzēst sludinājumu");
            });
    });
}
});

function banUser(userId) {
    if (!confirm('Vai tiešām vēlaties bloķēt šo lietotāju?')) return;

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
            action: 'ban_user',
            user_id: userId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            
            const banButton = document.querySelector('.action-buttons button.btn-warning');
            if (banButton) {
                banButton.outerHTML = `
                    <button type="button" onclick="unbanUser(${userId})" class="btn btn-success">
                        Atbloķēt
                    </button>
                `;
            }
            
            
            const statusElement = document.querySelector('.user-status');
            if (statusElement) {
                statusElement.className = 'user-status status-banned';
                statusElement.textContent = 'Bloķēts';
            }
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
    });
}

function unbanUser(userId) {
    if (!confirm('Vai tiešām vēlaties atbloķēt šo lietotāju?')) return;

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
            action: 'unban_user',
            user_id: userId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            
            const unbanButton = document.querySelector('.action-buttons button.btn-success');
            if (unbanButton) {
                unbanButton.outerHTML = `
                    <button type="button" onclick="banUser(${userId})" class="btn btn-warning">
                        Bloķēt
                    </button>
                `;
            }
            
           
            const statusElement = document.querySelector('.user-status');
            if (statusElement) {
                statusElement.className = 'user-status status-active';
                statusElement.textContent = 'Aktīvs';
            }
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
    });
}

function deleteUser(userId) {
    if (!confirm('Vai tiešām vēlaties dzēst šo lietotāju?')) return;

    fetch('../functions/AdminController.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            ajax: 1,
            action: 'delete_user',
            user_id: userId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            
            const actionButtons = document.querySelector('.action-buttons');
            if (actionButtons) {
                actionButtons.innerHTML = `
                    <button type="button" class="btn btn-secondary" disabled>Dzēsts</button>
                `;
            }
            
            const statusElement = document.querySelector('.user-status');
            if (statusElement) {
                statusElement.className = 'user-status status-deleted';
                statusElement.textContent = 'Dzēsts';
            }

            // Show success message
            alert('Lietotājs veiksmīgi dzēsts!');
            
            
            setTimeout(() => {
            window.location.href = 'user_manager.php';
            }, 10);
        } else {
            alert("Kļūda: " + (data.message || "Nezināma kļūda"));
        }
    });
}

function showDeleteModal(eventId) {
    document.getElementById('event_id').value = eventId;
    const modal = new bootstrap.Modal(document.getElementById('deleteEventModal'));
    modal.show();
}

function ucfirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function toggleEdit(field) {
    const display = document.getElementById(field + '-display');
    const form = document.getElementById(field + '-form');
    
    if (display.classList.contains('d-none')) {
        display.classList.remove('d-none');
        form.classList.add('d-none');
    } else {
        display.classList.add('d-none');
        form.classList.remove('d-none');
}
}

function updateUserInfo(field) {
    const form = document.getElementById(field + '-form');
    const formData = new FormData(form);
    formData.append('ajax', '1');
    formData.append('action', 'update_user_info');

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
        .then(response => response.json())
        .then(data => {
        if (data.success) {
           
            const display = document.getElementById(field + '-display');
            display.textContent = data.user[field];
            
          
            document.querySelectorAll('form').forEach(form => {
                if (form.id !== field + '-form') {
                    const hiddenInput = form.querySelector(`input[name="${field}"]`);
                    if (hiddenInput) {
                        hiddenInput.value = data.user[field];
                    }
                }
            });
            
            // Toggle back to display mode
            toggleEdit(field);
            
            // Show success message
            showNotification('success', 'Izmaiņas veiksmīgi saglabātas');
        } else {
            showNotification('error', data.message || 'Neizdevās atjaunināt informāciju');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Radusies kļūda atjauninot informāciju');
        });
}

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}


document.querySelectorAll('form[id$="-form"]').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const field = this.id.replace('-form', '');
        updateUserInfo(field);
    });
    });
</script>

</body>
</html>