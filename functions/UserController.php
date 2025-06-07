<?php

require_once '../config/con_db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['ID_user'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$userId = (int) $_SESSION['ID_user'];

// Handle GET requests
if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'get_stats':
            getUserStats();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
            exit();
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_main':
                updateMainInfo();
                break;
            case 'update_email':
                updateEmail();
                break;
            case 'update_location':
                updateLocation();
                break;
            case 'change_password':
                changePassword();
                break;
            default:
                http_response_code(400);
                echo json_encode(['error' => 'Invalid action']);
                exit();
        }
    }
}

function getUserStats() {
    global $pdo, $userId;

    try {
        $stmtEvents = $pdo->prepare(
            "SELECT COUNT(*) AS cnt FROM Events WHERE user_id = ? AND deleted = 0"
        );
        $stmtEvents->execute([$userId]);
        $events = (int) $stmtEvents->fetchColumn();

        $stmtVols = $pdo->prepare(
            "SELECT COUNT(*) AS cnt
            FROM Volunteers v
            JOIN Events e ON v.event_id = e.ID_Event
            WHERE v.user_id = ? 
            AND v.status IN ('waiting', 'accepted')
            AND e.deleted = 0"
        );
        $stmtVols->execute([$userId]);
        $volunteers = (int) $stmtVols->fetchColumn();

        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Cache-Control: post-check=0, pre-check=0', false);
        header('Pragma: no-cache');

        echo json_encode([
            'events' => $events,
            'volunteers' => $volunteers,
            'timestamp' => time() 
        ]);
        exit();

    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        error_log('Stats error: ' . $e->getMessage());
        exit();
    }
}

function updateMainInfo() {
    global $pdo, $userId;

    if (!isset($_POST['username'], $_POST['name'], $_POST['surname'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET username = :username, name = :name, surname = :surname WHERE ID_user = :userID");
        $stmt->execute([
            ':username' => $_POST['username'],
            ':name' => $_POST['name'],
            ':surname' => $_POST['surname'],
            ':userID' => $userId
        ]);
        echo json_encode(['success' => true, 'message' => 'Main info updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        error_log('Update main info error: ' . $e->getMessage());
    }
}

function updateEmail() {
    global $pdo, $userId;

    if (!isset($_POST['email'], $_POST['password'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit();
    }

    try {
        // Verify password first
        $stmt = $pdo->prepare("SELECT password FROM users WHERE ID_user = :userID");
        $stmt->execute([':userID' => $userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($_POST['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Incorrect password']);
            exit();
        }

        // Update email
        $stmt = $pdo->prepare("UPDATE users SET email = :email WHERE ID_user = :userID");
        $stmt->execute([
            ':email' => $_POST['email'],
            ':userID' => $userId
        ]);
        echo json_encode(['success' => true, 'message' => 'Email updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        error_log('Update email error: ' . $e->getMessage());
    }
}

function updateLocation() {
    global $pdo, $userId;

    if (!isset($_POST['location'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing location field']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE users SET location = :location WHERE ID_user = :userID");
        $stmt->execute([
            ':location' => $_POST['location'],
            ':userID' => $userId
        ]);
        echo json_encode(['success' => true, 'message' => 'Location updated successfully']);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
        error_log('Update location error: ' . $e->getMessage());
    }
}

function changePassword() {
    global $pdo, $userId;

    if (!isset($_POST['current_password'], $_POST['new_password'])) {
        echo json_encode(['success' => false, 'error' => 'Trūkst obligātie lauki']);
        exit;
    }

    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];

    if (empty($currentPassword) || empty($newPassword)) {
        echo json_encode(['success' => false, 'error' => 'Trūkst obligātie lauki']);
        exit;
    }

    try {
        // Verify current password
        $stmt = $pdo->prepare("SELECT password FROM users WHERE ID_user = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password'])) {
            echo json_encode(['success' => false, 'error' => 'Nepareiza pašreizējā parole']);
            exit;
        }

        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update password
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE ID_user = ?");
        $success = $updateStmt->execute([$hashedPassword, $userId]);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Parole veiksmīgi nomainīta']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Neizdevās atjaunināt paroli']);
        }
    } catch (PDOException $e) {
        error_log("Password change error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Kļūda datubāzē']);
    }
    exit;
}
?>