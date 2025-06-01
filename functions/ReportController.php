<?php
require_once '../config/con_db.php';
session_start();

function checkModeratorAccess() {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['mod', 'admin', 'supper-admin'])) {
        die('Access denied');
    }
}

function getTotalReportsCount() {
    global $pdo;
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM event_reports WHERE status = 'waiting'");
    $stmt->execute();
    return (int)$stmt->fetchColumn();
}


function getPaginatedReports($limit, $offset, $sortBy = 'reported_at', $sortOrder = 'DESC') {
    global $pdo;
 
    $allowedSortColumns = ['title', 'reported_at', 'creator_username'];
    $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'reported_at';
    $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
    
    
    $sortColumnMap = [
        'title' => 'e.title',
        'reported_at' => 'r.reported_at',
        'creator_username' => 'eu.username'
    ];
    
    $sortColumn = $sortColumnMap[$sortBy];
    
    $stmt = $pdo->prepare("
        SELECT 
            r.ID_report, r.reason, r.reported_at, r.status,
            u.ID_user as reporter_id, u.username as reporter_username,
            e.ID_Event, e.title, e.deleted as event_deleted,
            eu.ID_user as creator_id, eu.username as creator_username
        FROM event_reports r
        JOIN users u ON r.ID_user = u.ID_user
        JOIN Events e ON r.ID_event = e.ID_Event
        JOIN users eu ON e.user_id = eu.ID_user
        WHERE r.status = 'waiting'
        ORDER BY {$sortColumn} {$sortOrder}
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


function getReportDetails($reportId) {
    global $pdo;
    $stmt = $pdo->prepare("
        SELECT 
            r.*,
            u.username as reporter_username,
            u.email as reporter_email,
            e.title as event_title,
            e.description as event_description,
            e.location as event_location,
            e.city as event_city,
            e.date as event_date,
            e.deleted as event_deleted,
            eu.username as creator_username,
            eu.email as creator_email,
            eu.ID_user as creator_id,
            eu.banned as creator_banned
        FROM event_reports r
        JOIN users u ON r.ID_user = u.ID_user
        JOIN Events e ON r.ID_event = e.ID_Event
        JOIN users eu ON e.user_id = eu.ID_user
        WHERE r.ID_report = :reportId AND r.status = 'waiting'
    ");
    $stmt->execute([':reportId' => $reportId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function markReportAsSolved($reportId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE event_reports SET status = 'solved' WHERE ID_report = :reportId");
        return $stmt->execute([':reportId' => $reportId]);
    } catch (PDOException $e) {
        error_log("Error marking report as solved: " . $e->getMessage());
        return false;
    }
}

// Dzēst event
function deleteEvent($eventId, $adminId, $reason) {
    global $pdo;
    try {
        $pdo->beginTransaction();

        
        $stmt = $pdo->prepare("UPDATE Events SET deleted = 1 WHERE ID_Event = :eventId");
        $result = $stmt->execute([':eventId' => $eventId]);

        if ($result) {
          
            $stmt = $pdo->prepare("
                INSERT INTO DeletedEventsLog (event_id, admin_id, reason, deleted_at) 
                VALUES (:eventId, :adminId, :reason, NOW())
            ");
            $result = $stmt->execute([
                ':eventId' => $eventId,
                ':adminId' => $adminId,
                ':reason' => $reason
            ]);

            if ($result) {
                $pdo->commit();
                return true;
            }
        }

        $pdo->rollBack();
        return false;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error deleting event: " . $e->getMessage());
        return false;
    }
}


function getDeletionReasons() {
    return [
        'inappropriate_content' => 'Nepiemērots saturs',
        'spam' => 'Spams',
        'duplicate' => 'Dublēts pasākums',
        'fake_event' => 'Viltots pasākums',
        'other' => 'Cits iemesls'
    ];
}

// Ban lietotāju
function banUser($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("UPDATE users SET banned = 1 WHERE ID_user = :userId");
        return $stmt->execute([':userId' => $userId]);
    } catch (PDOException $e) {
        error_log("Error banning user: " . $e->getMessage());
        return false;
    }
}
//Atrinin;at vairākus ziņojumus
function markReportsAsSolved($reportIds) {
    global $pdo;
    try {
        $placeholders = str_repeat('?,', count($reportIds) - 1) . '?';
        $stmt = $pdo->prepare("UPDATE event_reports SET status = 'solved' WHERE ID_report IN ($placeholders)");
        return $stmt->execute($reportIds);
    } catch (PDOException $e) {
        error_log("Error marking reports as solved: " . $e->getMessage());
        return false;
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax'])) {
    header('Content-Type: application/json');
    
    try {
        if (!isset($_POST['action'])) {
            throw new Exception('No action specified');
        }

        switch ($_POST['action']) {
            case 'get_reports':
                $limit = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
                $offset = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
                $sortBy = $_POST['sort_by'] ?? 'reported_at';
                $sortOrder = $_POST['sort_order'] ?? 'DESC';
                
                $reports = getPaginatedReports($limit, $offset, $sortBy, $sortOrder);
                $totalReports = getTotalReportsCount();
                
                echo json_encode([
                    'success' => true, 
                    'reports' => $reports,
                    'total' => $totalReports
                ]);
                break;

            case 'get_report_details':
                $reportId = $_POST['report_id'] ?? 0;
                if ($reportId) {
                    $report = getReportDetails($reportId);
                    if ($report) {
                        echo json_encode(['success' => true, 'report' => $report]);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Report not found']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Missing report ID']);
                }
                break;

            case 'solve_report':
                $reportId = $_POST['report_id'] ?? 0;
                if ($reportId && markReportAsSolved($reportId)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to mark report as solved']);
                }
                break;

            case 'delete_event':
                $eventId = $_POST['event_id'] ?? 0;
                $adminId = $_SESSION['ID_user'] ?? 0;
                $reason = $_POST['reason'] ?? '';
                
                if (!$eventId || !$adminId || !$reason) {
                    echo json_encode(['success' => false, 'message' => 'Missing required data']);
                    break;
                }

                if (deleteEvent($eventId, $adminId, $reason)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to delete event']);
                }
                break;

            case 'get_deletion_reasons':
                echo json_encode(['success' => true, 'reasons' => getDeletionReasons()]);
                break;

            case 'ban_user':
                $userId = $_POST['user_id'] ?? 0;
                if ($userId && banUser($userId)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to ban user']);
                }
                break;

            case 'solve_reports_bulk':
                $reportIds = $_POST['report_ids'] ?? [];
                if (!empty($reportIds) && markReportsAsSolved($reportIds)) {
                    echo json_encode(['success' => true]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to mark reports as solved']);
                }
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        error_log("Error in ReportController: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Internal server error']);
    }
    exit;
} 