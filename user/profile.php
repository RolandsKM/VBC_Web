<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../css/templates/header.php';
require_once '../config/con_db.php';


$userID = $_SESSION['ID_user'];
$query = $pdo->prepare("SELECT `profile_pic`, `username`, `email` FROM `users` WHERE `ID_user` = ?");
$query->execute([$userID]);
$user = $query->fetch();

// Get total unread messages count
$unreadQuery = $pdo->prepare("
    SELECT COUNT(*) as unread_count 
    FROM messages 
    WHERE to_user_id = ? AND is_read = 0
");
$unreadQuery->execute([$userID]);
$unreadCount = $unreadQuery->fetch()['unread_count'];
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="user-style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../functions/script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        const userId = <?= htmlspecialchars($_SESSION['ID_user'] ?? 'null') ?>;
        const APP_DATA = {
            userId: <?= $_SESSION['ID_user'] ?? 'null' ?>,
            eventUserId: null,
            eventId: null
        };
    </script>
    <style>
        #user {
                
                padding: 5rem 0 0;
            }

        .btn-action {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--primary-color);
            transition: all 0.2s;
        }

        .unread-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            font-weight: 600;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .status-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            font-size: 0;
            transition: all 0.3s ease;
            z-index: 2;
        }

        .event:hover .status-badge {
            width: auto;
            height: auto;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-badge.waiting {
            background-color: #ffd700;
            color: #000;
        }

        .status-badge.accepted {
            background-color: #28a745;
            color: #fff;
        }

        .status-badge.denied {
            background-color: #dc3545;
            color: #fff;
        }

        .event:hover .status-badge::after {
            content: attr(data-status);
        }

        .event {
            position: relative;
            padding-top: 15px;
        }

        .event h2 {
            padding-right: 30px;
        }

        .new-acceptance {
            position: relative;
        }

        .new-acceptance::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 2px solid #28a745;
            border-radius: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
            }
        }

        .notifications-sidebar {
            position: fixed;
            top: 0;
            right: -400px;
            width: 400px;
            height: 100vh;
            background: white;
            box-shadow: -2px 0 5px rgba(0,0,0,0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .notifications-sidebar.active {
            right: 0;
        }

        .notifications-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
            flex-shrink: 0;
        }

        .notifications-header h3 {
            margin: 0;
            font-size: 1.2rem;
            color: #333;
        }

        .close-notifications {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .notification-icon.accepted {
            background-color: #28a745;
            color: white;
        }

        .notification-icon.denied {
            background-color: #dc3545;
            color: white;
        }

        .notification-icon.deleted {
            background-color: #dc3545;
            color: white;
        }

        .notification-icon.undeleted {
            background-color: #28a745;
            color: white;
        }

        .notification-content {
            flex-grow: 1;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .notification-time {
            font-size: 0.85rem;
            color: #666;
        }

        .notification-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background-color: #dc3545;
            color: white;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 6px;
            font-weight: 600;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: none;
            z-index: 999;
        }

        .overlay.active {
            display: block;
        }

        .notification-item.unread {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
        }

        .notification-item.unread .notification-title {
            font-weight: bold;
        }

        #notificationsList {
            flex-grow: 1;
            overflow-y: auto;
            padding-right: 10px;
        }

        #notificationsList::-webkit-scrollbar {
            width: 8px;
        }

        #notificationsList::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        #notificationsList::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        #notificationsList::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 1rem 0;
        }

        .empty-state i {
            font-size: 3rem;
            color: #6c757d;
            margin-bottom: 1rem;
        }

        .empty-state p {
            font-size: 1.2rem;
            color: #495057;
            margin-bottom: 1.5rem;
        }

        .empty-state .btn {
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .empty-state .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body id="user">

<section id="profile-header" class="py-4">
    <div class="container">
        <div class="profile-card">
            <div class="profile-main">
                
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_pic'])): ?>
                        <img src="../functions/assets/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profile Picture" class="rounded-circle">
                    <?php else: ?>
                        <img src="../images/default-profile.png" alt="Default Profile" class="rounded-circle">
                    <?php endif; ?>
                    
                </div>
                <div class="profile-info">
                    <h1><?= htmlspecialchars($user['username']) ?></h1>
                    <p class="text-muted"><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <div class="profile-actions">
                    <a href="messages.php" class="btn-action" title="Ziņas">
                        <i class="bi bi-chat-dots"></i>
                        <?php if ($unreadCount > 0): ?>
                            <span class="unread-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="#" class="btn-action notifications-btn" title="Notifikācijas">
                        <i class="bi bi-bell"></i>
                    </a>
                    <a href="account_info.php" class="btn-action" title="Iestatījumi">
                        <i class="fas fa-cog"></i>
                    </a>
                </div>
            </div>
            <div class="profile-stats">
                <div class="stat-item">
                    <span class="stat-number" id="post-count">0</span>
                    <span class="stat-label">Sludinājumi</span>
                </div>
                <div class="stat-item">
                    <span class="stat-number" id="joined-count">0</span>
                    <span class="stat-label">Pieteikumi</span>
                </div>
            </div>


             
        </div>
    </div>
</section>

<!-- Events Section -->
<section id="event" class="py-3">
    <div class="container">

        <div class="button-box">
            <div class="action-btn">
                <button class="sludinajumi-btn active">Sludinājumi</button>
                <button class="pieteicies-btn">Pieteicies</button>
            </div>

            <div class="create-btn">
                <a href="create.php" class="btn">Izveidot</a>
            </div>
        </div>

        <div class="event-container active">
            <div class="events-grid" id="own-events-grid">
                <div class="empty-state">
                    <i class="fas fa-calendar-plus"></i>
                    <p>Nav sludinājuma</p>
                </div>
            </div>
            <div class="text-center mt-3 load-btn">
                <button id="load-more-own" class="btn">Ielādēt vēl</button>
            </div>
        </div>

        <div class="joined-container">
            <div class="events-grid" id="joined-events-grid">
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <p>Pagaidām nav pieteikumu</p>
                </div>
            </div>
            <div class="text-center mt-3 load-btn">
                <button id="load-more-joined" class="btn">Ielādēt vēl</button>
            </div>


        </div>
    </div>
    
</section>


<style>

</style>

<div class="overlay" id="notificationsOverlay"></div>
<div class="notifications-sidebar" id="notificationsSidebar">
    <div class="notifications-header">
        <h3>Notifikācijas</h3>
        <button class="close-notifications" id="closeNotifications">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div id="notificationsList">
        <!-- Notifications will be loaded here -->
    </div>
</div>

<?php include '../css/templates/footer.php'; ?>
</body>
</html>


