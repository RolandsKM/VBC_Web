<?php
require_once '../config/con_db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');

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
    if ($_POST['action'] === 'resolve_report') {
        $reportId = isset($_POST['report_id']) ? (int)$_POST['report_id'] : 0;
        if ($reportId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Nederīgs ziņojuma ID.']);
            exit;
        }

        $result = markReportAsSolved($reportId);
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Ziņojums ir atzīmēts kā atrisināts.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Neizdevās atjaunināt statusu.']);
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

function getPaginatedEvents($limit, $offset) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT Events.ID_Event, Events.title, Events.deleted, Events.created_at,
               users.ID_user, users.username, users.name, users.surname
        FROM Events
        JOIN users ON Events.user_id = users.ID_user
        ORDER BY Events.created_at DESC
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

function getAllUsers() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ID_user, username, email, banned, created_at FROM users WHERE role = 'user'");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodaysUsers() {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ID_user, username, email, banned, created_at 
        FROM users 
        WHERE role = 'user' AND DATE(created_at) = CURDATE()
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTodaysUsersCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'user' AND DATE(created_at) = CURDATE()");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
// ---------Pagination-----------------
function getPaginatedTodaysUsers($limit, $offset) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ID_user, username, email, created_at, banned 
        FROM users 
        WHERE role = 'user' AND DATE(created_at) = CURDATE()
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
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

function getPaginatedUsersByPeriod($limit, $offset, $period) {
    global $pdo;
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
        case 'all':
        default:
            
            break;
    }
    $query .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUsersCountByPeriodTotal($period) {
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
        case 'all':
        default:
            break;
    }
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
function getNewUsersCountByPeriod($period) {
    global $pdo;

    switch ($period) {
        case 'week':
           
            $stmt = $pdo->prepare("
                SELECT DATE(created_at) AS day, COUNT(*) AS count 
                FROM users 
                WHERE role = 'user' AND YEARWEEK(created_at, 1) = YEARWEEK(CURDATE(), 1)
                GROUP BY day
                ORDER BY day
            ");
            break;
        case 'month':
            
            $stmt = $pdo->prepare("
                SELECT DATE(created_at) AS day, COUNT(*) AS count 
                FROM users 
                WHERE role = 'user' AND YEAR(created_at) = YEAR(CURDATE()) AND MONTH(created_at) = MONTH(CURDATE())
                GROUP BY day
                ORDER BY day
            ");
            break;
        case 'year':
          
            $stmt = $pdo->prepare("
                SELECT MONTH(created_at) AS month, COUNT(*) AS count 
                FROM users 
                WHERE role = 'user' AND YEAR(created_at) = YEAR(CURDATE())
                GROUP BY month
                ORDER BY month
            ");
            break;
        case 'all':
        default:

            $stmt = $pdo->prepare("
                SELECT YEAR(created_at) AS year, COUNT(*) AS count 
                FROM users 
                WHERE role = 'user'
                GROUP BY year
                ORDER BY year
            ");
            break;
    }

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    
    $labels = [];
    $counts = [];

    switch ($period) {
        case 'week':
        case 'month':
       
            $startDate = null;
            $endDate = new DateTime();

            if ($period === 'week') {
                $startDate = (new DateTime())->modify('monday this week');
            } else { 
                $startDate = (new DateTime())->modify('first day of this month');
            }

            $interval = new DateInterval('P1D');
            $periodRange = new DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

            $dataMap = [];
            foreach ($results as $row) {
                $dataMap[$row['day']] = (int)$row['count'];
            }

            foreach ($periodRange as $date) {
                $dateStr = $date->format('Y-m-d');
                $labels[] = $date->format('d M');
                $counts[] = $dataMap[$dateStr] ?? 0;
            }
            break;

        case 'year':
            $dataMap = [];
            foreach ($results as $row) {
                $dataMap[(int)$row['month']] = (int)$row['count'];
            }
          
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = date('M', mktime(0, 0, 0, $m, 10));
                $counts[] = $dataMap[$m] ?? 0;
            }
            break;

        case 'all':
        default:
            $dataMap = [];
            foreach ($results as $row) {
                $dataMap[(int)$row['year']] = (int)$row['count'];
            }
            $years = array_keys($dataMap);
            sort($years);
            foreach ($years as $year) {
                $labels[] = (string)$year;
                $counts[] = $dataMap[$year];
            }
            break;
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



// REPORT
function getAllEventReports() {
    global $pdo;

    $stmt = $pdo->prepare("
        SELECT 
            r.ID_report, r.reason AS report_reason, r.reported_at, r.status,
            u.ID_user, u.username, u.profile_pic AS reporter_pic,
            e.ID_Event, e.title, e.description, e.location, e.city, e.zip, e.date, e.deleted AS event_deleted,
            eu.ID_user AS creator_id, eu.username AS event_creator, eu.profile_pic AS creator_pic,
            eu.banned AS creator_banned
        FROM event_reports r
        JOIN users u ON r.ID_user = u.ID_user
        JOIN Events e ON r.ID_event = e.ID_Event
        JOIN users eu ON e.user_id = eu.ID_user
        WHERE r.status = 'waiting'
        ORDER BY r.reported_at DESC
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getPaginatedReports($limit, $offset) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            r.ID_report, r.reason AS report_reason, r.reported_at, r.status,
            u.ID_user, u.username, u.profile_pic AS reporter_pic,
            e.ID_Event, e.title, e.description, e.location, e.city, e.zip, e.date, e.deleted AS event_deleted,
            eu.ID_user AS creator_id, eu.username AS event_creator, eu.profile_pic AS creator_pic,
            eu.banned AS creator_banned
        FROM event_reports r
        JOIN users u ON r.ID_user = u.ID_user
        JOIN Events e ON r.ID_event = e.ID_Event
        JOIN users eu ON e.user_id = eu.ID_user
        WHERE r.status = 'waiting'
        ORDER BY r.reported_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function countAllReports() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM event_reports WHERE status = 'waiting'");
    return $stmt->fetchColumn();
}

function markReportAsSolved($reportId) {
    global $pdo; 
    $stmt = $pdo->prepare("UPDATE event_reports SET status = 'solved' WHERE ID_report = ?");
    return $stmt->execute([$reportId]);
}
function getTodayReportsCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM event_reports WHERE DATE(reported_at) = CURDATE()");
    $stmt->execute();
    return $stmt->fetchColumn();
}

function getUniqueReporterCount() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(DISTINCT ID_user) FROM event_reports");
    return $stmt->fetchColumn();
}

function getResolvedReportsCount() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM event_reports WHERE status = 'solved'");
    return $stmt->fetchColumn();
}
// For Line Chart (last 7 days)
function getLast7DaysReportCounts() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT DATE(reported_at) AS date, COUNT(*) AS count
        FROM event_reports
        WHERE reported_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
        GROUP BY DATE(reported_at)
        ORDER BY date ASC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

    // Ensure all 7 days are present
    $data = [];
    for ($i = 6; $i >= 0; $i--) {
        $day = date("Y-m-d", strtotime("-$i days"));
        $data[$day] = isset($rows[$day]) ? (int)$rows[$day] : 0;
    }

    return $data;
}

// For Pie Chart (top reasons)
function getTopReportReasons() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT reason, COUNT(*) AS count
        FROM event_reports
        GROUP BY reason
        ORDER BY count DESC
        LIMIT 5
    ");
    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
}

function getLast12MonthsReportCounts() {
    global $pdo;
    $stmt = $pdo->query("
        SELECT DATE_FORMAT(reported_at, '%Y-%m') AS month, COUNT(*) AS count
        FROM event_reports
        WHERE reported_at >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
        GROUP BY month
        ORDER BY month ASC
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

   
    $data = [];
    for ($i = 11; $i >= 0; $i--) {
        $month = date("Y-m", strtotime("-$i months"));
        $data[$month] = isset($rows[$month]) ? (int)$rows[$month] : 0;
    }

    return $data;
}
function getUsersCountByRole($role) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = :role");
    $stmt->execute([':role' => $role]);
    return (int) $stmt->fetchColumn();
}

function getPaginatedUsersByRole($role, $limit, $offset) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT ID_user, username, name, surname, deleted, created_at
        FROM users
        WHERE role = :role
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':role', $role, PDO::PARAM_STR);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
