<?php
require_once '../config/con_db.php';
session_start();

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        die("Invalid CSRF token");
    }
    return true;
}

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
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit();
        } else {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit();
        }
    }
}

function checkModeratorAccess() {
    if (!isModerator()) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit();
        } else {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit();
        }
    }
}

function checkSuperAdminAccess() {
    if (!isSuperAdmin()) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            exit();
        } else {
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'index.php'));
            exit();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

    if ($_POST['action'] === 'delete_user') {
        $userId = $_POST['user_id'] ?? null;
        if ($userId) {
            try {
                global $pdo;
                $stmt = $pdo->prepare("UPDATE users SET deleted = 1 WHERE ID_user = ?");
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

    if ($_POST['action'] === 'update_user_info') {
        $userId = $_POST['user_id'] ?? null;
        $username = trim($_POST['username'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $surname = trim($_POST['surname'] ?? '');
        
        if ($userId && $username && $name && $surname) {
            try {
                $success = updateUserInfo($userId, $username, $name, $surname);
                echo json_encode([
                    'success' => $success,
                    'message' => $success ? 'Lietotāja informācija veiksmīgi atjaunināta' : 'Neizdevās atjaunināt lietotāja informāciju',
                    'user' => [
                        'username' => $username,
                        'name' => $name,
                        'surname' => $surname
                    ]
                ]);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Trūkst obligāto lauku']);
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

function updateUserInfo($userId, $username, $name, $surname) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET username = :username, name = :name, surname = :surname WHERE ID_user = :id");
        return $stmt->execute([
            ':username' => $username,
            ':name' => $name,
            ':surname' => $surname,
            ':id' => $userId
        ]);
    } catch (PDOException $e) {
        error_log("Error updating user info: " . $e->getMessage());
        return false;
    }
}

// ----------------- EVENT FUNCTIONS ------------------
function getEventsCount($search = '') {
    global $pdo;
    $query = "SELECT COUNT(*) FROM Events";
    
    if (!empty($search)) {
        $query .= " JOIN users ON Events.user_id = users.ID_user";
        $query .= " WHERE Events.title LIKE :search1 OR users.username LIKE :search2";
    }
    
    $stmt = $pdo->prepare($query);
    
    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $stmt->bindValue(':search1', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':search2', $searchTerm, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getPaginatedEvents($limit, $offset, $sortField = 'created_at', $sortOrder = 'DESC', $search = '') {
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
    
    $query = "
        SELECT Events.ID_Event, Events.title, Events.deleted, Events.created_at,
               users.ID_user, users.username, users.name, users.surname
        FROM Events
        JOIN users ON Events.user_id = users.ID_user
    ";
    
    $params = [];
    
    if (!empty($search)) {
        $query .= " WHERE Events.title LIKE :search1 OR users.username LIKE :search2";
        $searchTerm = '%' . $search . '%';
        $params[':search1'] = $searchTerm;
        $params[':search2'] = $searchTerm;
    }
    
    $query .= " ORDER BY {$sortField} {$sortOrder} LIMIT :limit OFFSET :offset";
    $params[':limit'] = (int)$limit;
    $params[':offset'] = (int)$offset;
    
    try {
        $stmt = $pdo->prepare($query);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }
        
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $result ?: [];
    } catch (PDOException $e) {
        error_log("Database error in getPaginatedEvents: " . $e->getMessage());
        return [];
    }
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

function getEventsCountByDay($period = 'all') {
    global $pdo;
    $query = "
        SELECT DATE(created_at) as day, COUNT(*) as count
        FROM Events
        WHERE 1=1
    ";
    
    switch($period) {
        case 'week':
            $query .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $query .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
        case 'year':
            $query .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
            break;
    }
    
    $query .= " GROUP BY day ORDER BY day DESC LIMIT 30";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventsCountByWeek($period = 'all') {
    global $pdo;
    $query = "
        SELECT YEAR(created_at) as year, WEEK(created_at) as week, COUNT(*) as count
        FROM Events
        WHERE 1=1
    ";
    
    switch($period) {
        case 'week':
            $query .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $query .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
        case 'year':
            $query .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
            break;
    }
    
    $query .= " GROUP BY year, week ORDER BY year DESC, week DESC LIMIT 12";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEventsCountByMonth($period = 'all') {
    global $pdo;
    $query = "
        SELECT YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count
        FROM Events
        WHERE 1=1
    ";
    
    switch($period) {
        case 'week':
            $query .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $query .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
        case 'year':
            $query .= " AND created_at >= DATE_SUB(CURDATE(), INTERVAL 365 DAY)";
            break;
    }
    
    $query .= " GROUP BY year, month ORDER BY year DESC, month DESC LIMIT 12";
    
    $stmt = $pdo->prepare($query);
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
        SELECT e.*, u.name, u.surname, u.username, u.email, u.profile_pic, u.ID_user as user_id
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
        SELECT u.name, u.surname, u.username, u.email, u.profile_pic,
               v.created_at, v.status, v.ID_Volunteers
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
        SELECT ID_user, username, email, banned, created_at, profile_pic 
        FROM users 
        WHERE role = 'user' AND DATE(created_at) = CURDATE() AND deleted = 0
        ORDER BY {$sortField} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllUsers($limit = 5, $offset = 0, $sortField = 'created_at', $sortOrder = 'DESC', $search = '') {
    global $pdo;
    
    $validSortFields = [
        'username' => 'username',
        'email' => 'email',
        'created_at' => 'created_at',
        'banned' => 'banned'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'created_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    $query = "SELECT ID_user, username, email, banned, created_at, profile_pic 
              FROM users 
              WHERE role = 'user' AND deleted = 0";
    
    if (!empty($search)) {
        $query .= " AND (username LIKE :search1 OR email LIKE :search2)";
    }
    
    $query .= " ORDER BY {$sortField} {$sortOrder} LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    
    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $stmt->bindValue(':search1', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':search2', $searchTerm, PDO::PARAM_STR);
    }
    
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodaysUsersCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND DATE(created_at) = CURDATE() AND deleted = 0");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getTotalActiveUsersCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 0 AND deleted = 0");
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
              WHERE role = 'user' AND DATE(created_at) = CURDATE() AND deleted = 0";
    
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

function getAllUsersCount($search = '') {
    global $pdo;
    $query = "SELECT COUNT(*) FROM users WHERE role = 'user' AND deleted = 0";
    
    if (!empty($search)) {
        $query .= " AND (username LIKE :search1 OR email LIKE :search2)";
    }
    
    $stmt = $pdo->prepare($query);
    
    if (!empty($search)) {
        $searchTerm = '%' . $search . '%';
        $stmt->bindValue(':search1', $searchTerm, PDO::PARAM_STR);
        $stmt->bindValue(':search2', $searchTerm, PDO::PARAM_STR);
    }
    
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getPaginatedAllUsers($limit, $offset) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ID_user, username, email, banned, created_at 
        FROM users 
        WHERE role = 'user' AND deleted = 0
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
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 1 AND deleted = 0");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getAllBannedUsersCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 1 AND deleted = 0");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

function getUsersCountByPeriod($period) {
    global $pdo;
    $query = "SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 0 AND deleted = 0";
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
    $query = "SELECT COUNT(*) FROM users WHERE role = 'user' AND banned = 1 AND deleted = 0";
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
    
    $query = "SELECT ID_user, username, email, banned, created_at FROM users WHERE role = 'user' AND deleted = 0 ";
    
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
    $query = "SELECT COUNT(*) FROM users WHERE role = 'user' AND deleted = 0 ";
    
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
    try {
        $pdo->beginTransaction();
        
    $stmt = $pdo->prepare("UPDATE Events SET deleted = 0 WHERE ID_Event = :eventId");
    $stmt->execute([':eventId' => $eventId]);
        
    $stmt = $pdo->prepare("UPDATE DeletedEventsLog SET undeleted_at = NOW(), seen = 0 WHERE event_id = :eventId ORDER BY deleted_at DESC LIMIT 1");
    $stmt->execute([':eventId' => $eventId]);
        
        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error undeleting event: " . $e->getMessage());
        return false;
    }
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
    if ($currentUserRole === 'supper-admin') {
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

function changeUserRole($userId, $newRole) {
    global $pdo;
    if (!in_array($newRole, ['admin', 'mod'])) {
        return false;
    }
    $stmt = $pdo->prepare("UPDATE users SET role = :role WHERE ID_user = :id AND role IN ('admin', 'mod')");
    return $stmt->execute([':role' => $newRole, ':id' => $userId]);
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

function getTodaysEvents($limit = 5, $offset = 0, $sortField = 'created_at', $sortOrder = 'DESC') {
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
        WHERE DATE(Events.created_at) = CURDATE()
        ORDER BY {$sortField} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodaysEventsCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Events WHERE DATE(created_at) = CURDATE()");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_volunteers') {
    header('Content-Type: application/json');
    
    $eventId = $_GET['event_id'] ?? null;
    $page = (int)($_GET['page'] ?? 1);
    $perPage = (int)($_GET['per_page'] ?? 10);
    $sortField = $_GET['sort_field'] ?? 'created_at';
    $sortOrder = strtoupper($_GET['sort_order'] ?? 'DESC');
    
    // Validate sort field
    $validSortFields = [
        'name' => 'u.name',
        'username' => 'u.username',
        'created_at' => 'v.created_at',
        'status' => 'v.status'
    ];
    
    $sortField = $validSortFields[$sortField] ?? 'v.created_at';
    $sortOrder = $sortOrder === 'ASC' ? 'ASC' : 'DESC';
    
    if (!$eventId) {
        echo json_encode(['success' => false, 'message' => 'Missing event ID']);
        exit;
    }
    
    try {
        // Get total count
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Volunteers WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $total = (int)$stmt->fetchColumn();
        
        // Get paginated volunteers with sorting
        $offset = ($page - 1) * $perPage;
        $stmt = $pdo->prepare("
            SELECT u.name, u.surname, u.username, u.email, u.profile_pic,
                   v.created_at, v.status, v.ID_Volunteers
            FROM Volunteers v
            JOIN users u ON v.user_id = u.ID_user
            WHERE v.event_id = ?
            ORDER BY {$sortField} {$sortOrder}
            LIMIT ? OFFSET ?
        ");
        $stmt->execute([$eventId, $perPage, $offset]);
        $volunteers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'volunteers' => $volunteers,
            'total' => $total
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

function createAdminMod($username, $password, $name, $surname, $email, $role) {
    global $pdo;
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'Lietotājvārds jau eksistē!'];
        }

        // Check if email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            return ['success' => false, 'message' => 'E-pasts jau eksistē!'];
        }

        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert new admin/mod
        $stmt = $pdo->prepare("
            INSERT INTO users (username, password, name, surname, email, role, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $success = $stmt->execute([$username, $hashedPassword, $name, $surname, $email, $role]);
        
        if ($success) {
            return ['success' => true, 'message' => 'Lietotājs veiksmīgi izveidots!'];
        } else {
            return ['success' => false, 'message' => 'Neizdevās izveidot lietotāju!'];
        }
    } catch (PDOException $e) {
        error_log("Error creating admin/mod: " . $e->getMessage());
        return ['success' => false, 'message' => 'Datubāzes kļūda!'];
    }
}

function deleteAdminModUser($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET deleted = 1 WHERE ID_user = ? AND role IN ('admin', 'mod')");
        $success = $stmt->execute([$userId]);
        return ['success' => $success];
    } catch (PDOException $e) {
        error_log("Error deleting admin/mod: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function getAdminModUsers($role, $page, $perPage, $sortField, $sortOrder) {
    global $pdo;
    try {
        $offset = ($page - 1) * $perPage;
        $total = getUsersCountByRole($role);
        $users = getPaginatedUsersByRole($role, $perPage, $offset, $sortField, $sortOrder);
        
        
        foreach ($users as &$user) {
            $user['is_blocked'] = isUserBlocked($user['ID_user']);
        }
        
        return [
            'success' => true,
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'role' => $role
        ];
    } catch (PDOException $e) {
        error_log("Error fetching admin/mod users: " . $e->getMessage());
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

function isUserBlocked($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT banned FROM users WHERE ID_user = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result && $result['banned'] == 1;
    } catch (PDOException $e) {
        error_log("Error checking user block status: " . $e->getMessage());
        return false;
    }
}

function getAdminModTableData($role) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("
            SELECT ID_user, username, name, surname, email, created_at, banned
            FROM users 
            WHERE role = ? AND deleted = 0
            ORDER BY created_at DESC
        ");
        $stmt->execute([$role]);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        
        foreach ($users as &$user) {
            $user['is_blocked'] = isUserBlocked($user['ID_user']);
        }
        
        return $users;
    } catch (PDOException $e) {
        error_log("Error fetching admin/mod table data: " . $e->getMessage());
        return [];
    }
}

function getAdminModCount($role) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ? AND deleted = 0");
        $stmt->execute([$role]);
        return (int)$stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("Error getting admin/mod count: " . $e->getMessage());
        return 0;
    }
}

// ----------------- ADMIN MANAGER LOGIC ------------------
if (basename($_SERVER['PHP_SELF']) === 'admin_manager.php') {
    

    
    $modUsers = getAdminModTableData('mod');
    $adminUsers = getAdminModTableData('admin');
    $modCount = getAdminModCount('mod');
    $adminCount = getAdminModCount('admin');

    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        header('Content-Type: application/json');
        $userId = $_POST['user_id'] ?? null;
        if ($userId) {
            $result = deleteAdminModUser($userId);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nav norādīts lietotāja ID.']);
        }
        exit;
    }

    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        header('Content-Type: application/json');

        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $role = isset($_GET['role']) ? $_GET['role'] : '';
        $sortField = $_GET['sort'] ?? 'created_at';
        $sortOrder = $_GET['order'] ?? 'DESC';

        if ($role === 'mod' || $role === 'admin') {
            $result = getAdminModUsers($role, $page, $perPage, $sortField, $sortOrder);
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid role specified']);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['name'], $_POST['surname'], $_POST['email'], $_POST['role'])) {
        header('Content-Type: application/json');

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

        $result = createAdminMod($username, $password, $name, $surname, $email, $role);
        echo json_encode($result);
        exit;
    }
}

// ----------------- ADMIN DETAILS LOGIC ------------------
if (basename($_SERVER['PHP_SELF']) === 'admin-details.php') {
    checkSuperAdminAccess();
    session_start();

    if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
        header('Content-Type: application/json');
        
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
            exit;
        }

        $id = (int)$_GET['id'];
        $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = 5;
        $offset = ($page - 1) * $perPage;
        $sortField = $_GET['sort'] ?? 'deleted_at';
        $sortOrder = $_GET['order'] ?? 'DESC';

        try {
            $actions = getAdminModActions($id, $perPage, $offset, $sortField, $sortOrder);
            $total = getAdminModActionsCount($id);
            
            echo json_encode([
                'success' => true,
                'actions' => $actions,
                'total' => $total,
                'page' => $page,
                'perPage' => $perPage
            ]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        exit;
    }

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        header("Location: admin_manager.php");
        exit();
    }

    $id = (int)$_GET['id'];
    $user = getAdminModDetails($id);

    if (!$user) {
        echo "<div class='alert alert-danger m-4'>Lietotājs nav atrasts.</div>";
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $currentUserRole = $_SESSION['role'] ?? '';
        $targetUserRole = $user['role'];
        
        if (canManageUser($currentUserRole, $targetUserRole)) {
            if (isset($_POST['ban'])) {
                banAdminMod($id);
                header("Location: admin-details.php?id=" . $id);
                exit();
            }
            if (isset($_POST['unban'])) {
                unbanAdminMod($id);
                header("Location: admin-details.php?id=" . $id);
                exit();
            }
            if (isset($_POST['delete'])) {
                deleteAdminMod($id);
                header("Location: admin_manager.php");
                exit();
            }
            if (isset($_POST['change_role'])) {
                $newRole = $_POST['new_role'];
                if (in_array($newRole, ['admin', 'mod'])) {
                    changeUserRole($id, $newRole);
                    header("Location: admin-details.php?id=" . $id);
                    exit();
                }
            }
        } else {
            echo "<div class='alert alert-danger m-4'>Jums nav tiesību veikt šo darbību.</div>";
        }
    }

    $actions = getAdminModActions($id, 5, 0);
    $totalActions = getAdminModActionsCount($id);
    $totalPages = ceil($totalActions / 5);
}