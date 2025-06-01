<?php
require_once '../config/con_db.php';
session_start();


function isAdmin() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'supper-admin');
}

function isModerator() {
    return isset($_SESSION['role']) && ($_SESSION['role'] === 'mod' || $_SESSION['role'] === 'admin' || $_SESSION['role'] === 'supper-admin');
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'supper-admin';
}

function checkAdminAccess() {
    if (!isAdmin()) {
        echo '<script>
            
            window.location.href = "' . ($_SERVER['HTTP_REFERER'] ?? 'index.php') . '";
        </script>';
        exit();
    }
}

function checkModeratorAccess() {
    if (!isModerator()) {
        echo '<script>
       
            window.location.href = "' . ($_SERVER['HTTP_REFERER'] ?? 'index.php') . '";
        </script>';
        exit();
    }
}

function checkSuperAdminAccess() {
    if (!isSuperAdmin()) {
        echo '<script>
           
            window.location.href = "' . ($_SERVER['HTTP_REFERER'] ?? 'index.php') . '";
        </script>';
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'delete_user') {
        $userId = $_POST['user_id'] ?? null;
        if ($userId) {
            try {
                global $pdo;
                $stmt = $pdo->prepare("DELETE FROM users WHERE ID_user = ? AND role IN ('admin', 'mod')");
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

    if ($_POST['action'] === 'delete_event') {
        $eventId = $_POST['event_id'];
        $reason = trim($_POST['reason']);
        $adminId = $_SESSION['ID_user'] ?? null;

        if (!$adminId || !$eventId || !$reason) {
            echo json_encode(['success' => false, 'message' => 'Missing data']);
            exit;
        }

        deleteEventWithReason($eventId, $adminId, $reason);
        echo json_encode(['success' => true]);
        exit;
    }
    
    if ($_POST['action'] === 'ban_user') {
        $userId = $_POST['user_id'] ?? null;
        if ($userId) {
            banUser($userId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nav norādīts lietotāja ID.']);
        }
        exit;
    }

    if ($_POST['action'] === 'unban_user') {
        $userId = $_POST['user_id'] ?? null;
        if ($userId) {
            unbanUser($userId);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nav norādīts lietotāja ID.']);
        }
        exit;
    }

    if ($_POST['action'] === 'undelete_event') {
        $eventId = $_POST['event_id'];
        undeleteEvent($eventId);
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Invalid action']);
    exit;
}

// ----------------- USER FUNCTIONS ------------------

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ID_user, username, name, surname, email, profile_pic, location, role, banned, deleted, created_at FROM users WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function banUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET banned = 1 WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);
}

function unbanUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET banned = 0 WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);
}

function deleteUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET deleted = 1 WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);
}

// ----------------- EVENT FUNCTIONS ------------------
function getEventsCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Events");
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function getPaginatedEvents($limit, $offset, $sortField = 'created_at', $sortOrder = 'DESC') {
    global $pdo;
    
    $validSortFields = [
        'ID_Event' => 'Events.ID_Event',
        'title' => 'Events.title',
        'username' => 'users.username',
        'deleted' => 'Events.deleted',
        'created_at' => 'Events.created_at'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'Events.created_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $stmt = $pdo->prepare("
        SELECT Events.ID_Event, Events.title, Events.deleted, Events.created_at,
               users.ID_user, users.username, users.name, users.surname
        FROM Events
        JOIN users ON Events.user_id = users.ID_user
        ORDER BY {$sortField} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventsCreatedByUser($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM Events WHERE user_id = :userId");
    $stmt->execute([':userId' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventsUserVolunteered($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT Events.*, Volunteers.status 
        FROM Volunteers 
        JOIN Events ON Volunteers.event_id = Events.ID_Event 
        WHERE Volunteers.user_id = :userId
    ");
    $stmt->execute([':userId' => $userId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllEventsWithUser() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT Events.ID_Event, Events.title, Events.deleted, Events.created_at,
               users.ID_user, users.username, users.name, users.surname
        FROM Events
        JOIN users ON Events.user_id = users.ID_user
        ORDER BY Events.created_at DESC
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventsCountByDay() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT DATE(created_at) as day, COUNT(*) as count
        FROM Events
        GROUP BY day
        ORDER BY day DESC
        LIMIT 30
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventsCountByWeek() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT YEAR(created_at) as year, WEEK(created_at) as week, COUNT(*) as count
        FROM Events
        GROUP BY year, week
        ORDER BY year DESC, week DESC
        LIMIT 12
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventsCountByMonth() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count
        FROM Events
        GROUP BY year, month
        ORDER BY year DESC, month DESC
        LIMIT 12
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDeletedEventsCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Events WHERE deleted = 1");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function getMostPopularEvent() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT Events.ID_Event, Events.title, COUNT(Volunteers.ID_Volunteers) as volunteer_count
        FROM Events
        LEFT JOIN Volunteers ON Events.ID_Event = Volunteers.event_id
        GROUP BY Events.ID_Event
        ORDER BY volunteer_count DESC
        LIMIT 1
    ");
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getEventByIdWithUser(int $eventId): ?array {
    global $pdo; 
    $stmt = $pdo->prepare("
        SELECT e.*, u.name, u.surname, u.username, u.email 
        FROM Events e
        JOIN users u ON e.user_id = u.ID_user
        WHERE e.ID_Event = :id
        LIMIT 1
    ");
    $stmt->execute(['id' => $eventId]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);
    return $event ?: null;
}

function getVolunteersByEventId(int $eventId): array {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT u.name, u.surname, u.username, v.created_at 
        FROM Volunteers v
        JOIN users u ON v.user_id = u.ID_user
        WHERE v.event_id = :event_id
        ORDER BY v.created_at DESC
    ");
    $stmt->execute(['event_id' => $eventId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function deleteEventById(int $eventId): bool {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE Events SET deleted = 1 WHERE ID_Event = :id");
    return $stmt->execute(['id' => $eventId]);
}

// ----------------- USER DETAILS LOGIC ------------------

if (basename($_SERVER['PHP_SELF']) === 'user-details.php') {
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: user_manager.php");
        exit();
    }

    $id = (int)$_GET['id'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['ban'])) {
            banUser($_POST['user_id']);
            header("Location: user-details.php?id=" . $_POST['user_id']);
            exit();
        }
        if (isset($_POST['unban'])) {
            unbanUser($_POST['user_id']);
            header("Location: user-details.php?id=" . $_POST['user_id']);
            exit();
        }
        if (isset($_POST['delete'])) {
            deleteUser($_POST['user_id']);
            header("Location: user_manager.php");
            exit();
        }
    }

    $user = getUserById($id);

    if (!$user) {
        echo "<div class='alert alert-danger m-4'>Lietotājs nav atrasts.</div>";
        exit();
    }

    $eventsCreated = getEventsCreatedByUser($id);
    $volunteeredEvents = getEventsUserVolunteered($id);
}

// ----------------- USER LISTING LOGIC ------------------

function getTodaysUsers($limit = 5, $offset = 0, $sortField = 'created_at', $sortOrder = 'DESC') {
    global $pdo;
    
    $validSortFields = [
        'username' => 'username',
        'email' => 'email',
        'created_at' => 'created_at',
        'banned' => 'banned'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'created_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $stmt = $pdo->prepare("
        SELECT ID_user, username, email, banned, created_at 
        FROM users 
        WHERE role = 'user' AND DATE(created_at) = CURDATE()
        ORDER BY {$sortField} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllUsers($limit = 5, $offset = 0, $sortField = 'created_at', $sortOrder = 'DESC') {
    global $pdo;
    
    $validSortFields = [
        'username' => 'username',
        'email' => 'email',
        'created_at' => 'created_at',
        'banned' => 'banned'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'created_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $stmt = $pdo->prepare("
        SELECT ID_user, username, email, banned, created_at 
        FROM users 
        WHERE role = 'user'
        ORDER BY {$sortField} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodaysUsersCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND DATE(created_at) = CURDATE()");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getPaginatedTodaysUsers($limit, $offset, $statusFilter = 'all', $sortField = 'created_at', $sortOrder = 'DESC') {
    global $pdo;
    
    $validSortFields = [
        'username' => 'username',
        'email' => 'email',
        'created_at' => 'created_at',
        'banned' => 'banned'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'created_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $query = "SELECT ID_user, username, email, created_at, banned 
              FROM users 
              WHERE role = 'user' AND DATE(created_at) = CURDATE()";
    
    if ($statusFilter === 'banned') {
        $query .= " AND banned = 1";
    } elseif ($statusFilter === 'active') {
        $query .= " AND banned = 0";
    }
    
    $query .= " ORDER BY {$sortField} {$sortOrder} LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllUsersCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user'");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getPaginatedAllUsers($limit, $offset) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ID_user, username, email, banned, created_at 
        FROM users 
        WHERE role = 'user'
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodaysBannedUsersCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 1 AND DATE(created_at) = CURDATE()");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getAllBannedUsersCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 1");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getUsersCountByPeriod($period) {
    global $pdo;
    $query = "SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 0";
    switch ($period) {
        case 'week':
            $query .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $query .= " AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
            break;
        case 'year':
            $query .= " AND YEAR(created_at) = YEAR(CURDATE())";
            break;
        case 'all':
        default:
            break;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getBannedUsersCountByPeriod($period) {
    global $pdo;
    $query = "SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 1";
    switch ($period) {
        case 'week':
            $query .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $query .= " AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
            break;
        case 'year':
            $query .= " AND YEAR(created_at) = YEAR(CURDATE())";
            break;
        case 'all':
        default:
            break;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getPaginatedUsersByPeriod($limit, $offset, $period, $statusFilter = 'all', $sortField = 'created_at', $sortOrder = 'DESC') {
    global $pdo;
    
    $validSortFields = [
        'username' => 'username',
        'email' => 'email',
        'created_at' => 'created_at',
        'banned' => 'banned'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'created_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $query = "SELECT ID_user, username, email, banned, created_at FROM users WHERE role = 'user' ";
    
    switch ($period) {
        case 'week':
            $query .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1) ";
            break;
        case 'month':
            $query .= " AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE()) ";
            break;
        case 'year':
            $query .= " AND YEAR(created_at) = YEAR(CURDATE()) ";
            break;
    }
    
    if ($statusFilter === 'banned') {
        $query .= " AND banned = 1 ";
    } elseif ($statusFilter === 'active') {
        $query .= " AND banned = 0 ";
    }
    
    $query .= " ORDER BY {$sortField} {$sortOrder} LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsersCountByPeriodTotal($period, $statusFilter = 'all') {
    global $pdo;
    $query = "SELECT COUNT(*) FROM users WHERE role = 'user' ";
    
    switch ($period) {
        case 'week':
            $query .= " AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $query .= " AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())";
            break;
        case 'year':
            $query .= " AND YEAR(created_at) = YEAR(CURDATE())";
            break;
    }
    
    if ($statusFilter === 'banned') {
        $query .= " AND banned = 1";
    } elseif ($statusFilter === 'active') {
        $query .= " AND banned = 0";
    }
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getNewUsersCountByPeriod($period) {
    global $pdo;
    $endDate = new DateTime();
    $startDate = new DateTime();

    switch ($period) {
        case 'week':
            $startDate->modify('-7 days');
            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
            
            $stmt = $pdo->prepare("
                SELECT DATE(created_at) AS day, COUNT(*) AS count 
                FROM users 
                WHERE role = 'user' AND created_at >= :start_date
                GROUP BY day
                ORDER BY day
            ");
            $stmt->execute([':start_date' => $startDate->format('Y-m-d')]);
            break;

        case 'month':
            $startDate->modify('-30 days');
            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
            
            $stmt = $pdo->prepare("
                SELECT DATE(created_at) AS day, COUNT(*) AS count 
                FROM users 
                WHERE role = 'user' AND created_at >= :start_date
                GROUP BY day
                ORDER BY day
            ");
            $stmt->execute([':start_date' => $startDate->format('Y-m-d')]);
            break;

        case 'year':
            $startDate->modify('-365 days');
            $interval = new DateInterval('P1D');
            $dateRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));
            
            $stmt = $pdo->prepare("
                SELECT DATE(created_at) AS day, COUNT(*) AS count 
                FROM users 
                WHERE role = 'user' AND created_at >= :start_date
                GROUP BY day
                ORDER BY day
            ");
            $stmt->execute([':start_date' => $startDate->format('Y-m-d')]);
            break;

        case 'all':
        default:
            $stmt = $pdo->prepare("
                SELECT DATE(created_at) AS day, COUNT(*) AS count 
                FROM users 
                WHERE role = 'user'
                GROUP BY day
                ORDER BY day
            ");
            $stmt->execute();
            break;
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $dataMap = [];
    foreach ($results as $row) {
        $dataMap[$row['day']] = (int)$row['count'];
    }

    $labels = [];
    $counts = [];

    if (isset($dateRange)) {
        foreach ($dateRange as $date) {
            $dateStr = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $counts[] = $dataMap[$dateStr] ?? 0;
        }
    } else {
        foreach ($results as $row) {
            $date = new DateTime($row['day']);
            $labels[] = $date->format('d M');
            $counts[] = (int)$row['count'];
        }
    }

    return ['labels' => $labels, 'counts' => $counts];
}

function deleteEventWithReason($eventId, $adminId, $reason) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE Events SET deleted = 1 WHERE ID_Event = :eventId");
    $stmt->execute([':eventId' => $eventId]);

    $stmtLog = $pdo->prepare("INSERT INTO DeletedEventsLog (event_id, admin_id, reason) VALUES (:eventId, :adminId, :reason)");
    $stmtLog->execute([
        ':eventId' => $eventId,
        ':adminId' => $adminId,
        ':reason' => $reason
    ]);
}

function undeleteEvent($eventId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE Events SET deleted = 0 WHERE ID_Event = :eventId");
    $stmt->execute([':eventId' => $eventId]);
    $stmt = $pdo->prepare("UPDATE DeletedEventsLog SET undeleted_at = NOW() WHERE event_id = :eventId ORDER BY deleted_at DESC LIMIT 1");
    $stmt->execute([':eventId' => $eventId]);
}

function getUsersCountByRole($role) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = :role AND deleted = 0");
    $stmt->execute([':role' => $role]);
    return (int) $stmt->fetchColumn();
}

function getPaginatedUsersByRole($role, $limit, $offset, $sortField = 'created_at', $sortOrder = 'DESC') {
    global $pdo;
    
    $validSortFields = [
        'ID_user' => 'ID_user',
        'username' => 'username',
        'name' => 'name',
        'surname' => 'surname',
        'email' => 'email',
        'created_at' => 'created_at'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'created_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $stmt = $pdo->prepare("
        SELECT ID_user, username, name, surname, email, deleted, created_at
        FROM users
        WHERE role = :role AND deleted = 0
        ORDER BY {$sortField} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':role', $role, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPaginatedEventsCreatedByUser($userId, $limit, $offset, $sortField = 'created_at', $sortOrder = 'DESC') {
    global $pdo;
    
    $validSortFields = [
        'title' => 'Events.title',
        'description' => 'Events.description',
        'date' => 'Events.date',
        'deleted' => 'Events.deleted',
        'created_at' => 'Events.created_at'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'Events.created_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $stmt = $pdo->prepare("
        SELECT * FROM Events 
        WHERE user_id = :userId 
        ORDER BY {$sortField} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventsCreatedByUserCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Events WHERE user_id = :userId");
    $stmt->execute([':userId' => $userId]);
    return (int)$stmt->fetchColumn();
}

function getPaginatedEventsUserVolunteered($userId, $limit, $offset, $sortField = 'created_at', $sortOrder = 'DESC') {
    global $pdo;
    
    $validSortFields = [
        'title' => 'Events.title',
        'description' => 'Events.description',
        'date' => 'Events.date',
        'status' => 'Volunteers.status',
        'created_at' => 'Events.created_at'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'Events.created_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $stmt = $pdo->prepare("
        SELECT Events.*, Volunteers.status 
        FROM Volunteers 
        JOIN Events ON Volunteers.event_id = Events.ID_Event 
        WHERE Volunteers.user_id = :userId
        ORDER BY {$sortField} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventsUserVolunteeredCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM Volunteers 
        WHERE user_id = :userId
    ");
    $stmt->execute([':userId' => $userId]);
    return (int)$stmt->fetchColumn();
}

function getAdminModDetails($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ID_user, username, name, surname, email, role, banned, created_at
        FROM users 
        WHERE ID_user = :id AND role IN ('admin', 'mod')
    ");
    $stmt->execute([':id' => $userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getAdminModActions($userId, $limit = 5, $offset = 0, $sortField = 'deleted_at', $sortOrder = 'DESC') {
    global $pdo;
    
    $validSortFields = [
        'ID' => 'del.ID',
        'event_title' => 'e.title',
        'reason' => 'del.reason',
        'deleted_at' => 'del.deleted_at',
        'undeleted_at' => 'del.undeleted_at'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'del.deleted_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $stmt = $pdo->prepare("
        SELECT 
            del.ID,
            del.event_id,
            del.admin_id,
            del.reason,
            del.deleted_at,
            del.undeleted_at,
            e.title as event_title
        FROM DeletedEventsLog del
        JOIN Events e ON del.event_id = e.ID_Event
        WHERE del.admin_id = :userId
        ORDER BY {$sortField} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':userId', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAdminModActionsCount($userId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM DeletedEventsLog 
        WHERE admin_id = :userId
    ");
    $stmt->execute([':userId' => $userId]);
    return (int)$stmt->fetchColumn();
}
// -------- check who can manage ---------------
function canManageUser($currentUserRole, $targetUserRole) {
    if ($currentUserRole === 'super-admin') {
        return true; 
    }
    if ($currentUserRole === 'admin' && $targetUserRole === 'mod') {
        return true; 
    }
    return false;
}

function banAdminMod($userId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET banned = 1 WHERE ID_user = :id AND role IN ('admin', 'mod')");
    $stmt->execute([':id' => $userId]);
}

function unbanAdminMod($userId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET banned = 0 WHERE ID_user = :id AND role IN ('admin', 'mod')");
    $stmt->execute([':id' => $userId]);
}

function deleteAdminMod($userId) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET deleted = 1 WHERE ID_user = :id AND role IN ('admin', 'mod')");
    $stmt->execute([':id' => $userId]);
}

function getTodaysVolunteersCount() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM Volunteers 
        WHERE DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getTodaysVolunteers($limit = 5, $offset = 0) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT v.ID_Volunteers, v.status, v.created_at, 
               u.username, e.title
        FROM Volunteers v
        JOIN users u ON v.user_id = u.ID_user
        JOIN Events e ON v.event_id = e.ID_Event
        WHERE DATE(v.created_at) = CURDATE()
        ORDER BY v.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
