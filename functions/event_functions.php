<?php
require_once '../config/con_db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// -------------------------
// FETCH EVENT DATA
// -------------------------
function fetchEventData($eventId) {
    global $pdo;

    $query = "
        SELECT e.title, e.description, e.date, e.location, e.city, e.zip, e.created_at, 
               c.Nosaukums AS category_name,
               u.ID_user AS user_id, 
               u.username, u.email, u.profile_pic
        FROM Events e
        LEFT JOIN Event_Categories ec ON e.ID_Event = ec.event_id
        LEFT JOIN VBC_Kategorijas c ON ec.category_id = c.Kategorijas_ID
        LEFT JOIN users u ON e.user_id = u.ID_user
        WHERE e.ID_Event = ? AND e.deleted = 0
        LIMIT 1
    ";

    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute([$eventId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log("Error fetching event data: " . $e->getMessage());
        return null;
    }
}


// -------------------------
// HANDLE AJAX REQUESTS
// -------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $userId = isset($_SESSION['ID_user']) ? intval($_SESSION['ID_user']) : 0;

    // -------------------
    // JOIN / LEAVE / CHECK
    // -------------------
    if (in_array($action, ['join', 'leave', 'check']) && $userId > 0) {
        $eventId = isset($_POST['event_id']) ? intval($_POST['event_id']) : 0;

        if ($action === 'join') {
            $stmt = $pdo->prepare("SELECT status FROM Volunteers WHERE user_id = :user_id AND event_id = :event_id");
            $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
            $existing = $stmt->fetch();

            if ($existing) {
                $update = $pdo->prepare("UPDATE Volunteers SET status = 'waiting' WHERE user_id = :user_id AND event_id = :event_id");
                $update->execute(['user_id' => $userId, 'event_id' => $eventId]);
            } else {
                $insert = $pdo->prepare("INSERT INTO Volunteers (user_id, event_id, status) VALUES (:user_id, :event_id, 'waiting')");
                $insert->execute(['user_id' => $userId, 'event_id' => $eventId]);
            }

            echo 'joined';
            exit();
        }

        if ($action === 'leave') {
            $stmt = $pdo->prepare("UPDATE Volunteers SET status = 'left' WHERE user_id = :user_id AND event_id = :event_id");
            $stmt->execute(['user_id' => $userId, 'event_id' => $eventId]);
            echo 'left';
            exit();
        }

        if ($action === 'check') {
            $stmt = $pdo->prepare("SELECT status FROM Volunteers WHERE user_id = ? AND event_id = ?");
            $stmt->execute([$userId, $eventId]);
            $row = $stmt->fetch();
            echo $row ? $row['status'] : 'left';
            exit();
        }
    }

    // -------------------
    // CREATE NEW EVENT
    // -------------------
    if ($action === 'create' && $userId > 0) {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $zip = trim($_POST['zip'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $date = trim($_POST['date'] ?? '');

        if (!$title || !$description || !$location || !$city || !$zip || !$category_id || !$date) {
            echo "error: Missing required fields";
            exit();
        }

        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("INSERT INTO Events (user_id, title, description, location, city, zip, date, created_at) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([$userId, $title, $description, $location, $city, $zip, $date]);

            $eventId = $pdo->lastInsertId();

            $catStmt = $pdo->prepare("INSERT INTO Event_Categories (event_id, category_id) VALUES (?, ?)");
            $catStmt->execute([$eventId, $category_id]);

            $pdo->commit();
            echo "success";
        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Error creating event: " . $e->getMessage());
            echo "error: " . $e->getMessage();
        }

        exit();
    }

    // -------------------
    // UPDATE EVENT
    // -------------------
    if ($action === 'update' && $userId > 0) {
        $event_id = intval($_POST['event_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $city = trim($_POST['city'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $zip = trim($_POST['zip'] ?? '');
        $date = trim($_POST['date'] ?? '');

        if (!$event_id || !$title || !$description || !$city || !$location || !$zip || !$date) {
            echo json_encode(["status" => "error", "message" => "Trūkst obligātie lauki."]);
            exit;
        }

        $query = "UPDATE Events 
                  SET title = :title, description = :description, city = :city, 
                      location = :location, zip = :zip, date = :date 
                  WHERE ID_Event = :event_id AND user_id = :user_id";

        $stmt = $pdo->prepare($query);
        $success = $stmt->execute([
            ':title' => $title,
            ':description' => $description,
            ':city' => $city,
            ':location' => $location,
            ':zip' => $zip,
            ':date' => $date,
            ':event_id' => $event_id,
            ':user_id' => $userId
        ]);

        echo json_encode($success
            ? ["status" => "success"]
            : ["status" => "error", "message" => "Neizdevās atjaunināt notikumu."]);
        exit;
    }

    // -------------------
    // DELETE EVENT
    // -------------------
    if ($action === 'delete' && $userId > 0) {
        $event_id = intval($_POST['event_id'] ?? 0);

        if (!$event_id) {
            echo json_encode(["status" => "error", "message" => "Notikuma ID nav norādīts."]);
            exit;
        }

        $query = "UPDATE Events SET deleted = 1 WHERE ID_Event = ? AND user_id = ?";
        $stmt = $pdo->prepare($query);
        $success = $stmt->execute([$event_id, $userId]);

        echo json_encode($success
            ? ["status" => "success", "message" => "Notikums veiksmīgi dzēsts."]
            : ["status" => "error", "message" => "Neizdevās dzēst notikumu."]);
        exit;
    }
    if ($action === 'update_volunteer_status' && $userId > 0) {
        $volunteerId = intval($_POST['volunteer_id'] ?? 0);
        $newStatus = $_POST['status'] ?? '';

        $allowedStatuses = ['waiting', 'accepted', 'denied'];

        if ($volunteerId > 0 && in_array($newStatus, $allowedStatuses)) {
            try {
                $stmt = $pdo->prepare("UPDATE Volunteers SET status = :status WHERE ID_Volunteers = :id");
                $success = $stmt->execute([
                    ':status' => $newStatus,
                    ':id' => $volunteerId
                ]);
                echo $success ? 'success' : 'error_execute';
            } catch (PDOException $e) {
                echo 'db_error';
            }
        } else {
            echo 'invalid_data';
        }

        exit;
    }
if ($action === 'batch_update_status') {
    $ids = $_POST['ids'] ?? [];
    $status = $_POST['status'] ?? '';

    if (empty($ids) || !in_array($status, ['waiting', 'accepted', 'denied'])) {
        echo 'Invalid input';
        exit();
    }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));

    try {
        $stmt = $pdo->prepare("UPDATE Volunteers SET status = ? WHERE ID_Volunteers IN ($placeholders)");
        $stmt->execute(array_merge([$status], $ids));
        echo 'success';
    } catch (PDOException $e) {
        echo 'Kļūda: ' . $e->getMessage();
    }
    exit();
}


    echo 'invalid';
    exit();
}

// -----------------------------
// GET REQUESTS (OWN / JOINED)
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $userId = $_SESSION['ID_user'] ?? 0;

    if (!$userId) {
        echo "Neautorizēta piekļuve!";
        exit();
    }

    if ($action === 'own') {
        try {
            $stmt = $pdo->prepare("SELECT * FROM Events WHERE user_id = ? AND deleted = 0 ORDER BY created_at DESC");
            $stmt->execute([$userId]);
            $output = '';

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
                $description = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
                $event_date = date("d.m.Y", strtotime($row['date']));
                $created_date = date("d.m.Y", strtotime($row['created_at']));
                $event_id = $row['ID_Event'];

                $output .= "
                <a href='user-event.php?id=$event_id' class='event-link'>
                    <div class='event'>
                        <h2>$title</h2>
                        <div class='description'>$description</div>
                        <hr>
                        <div class='dates'>
                            <p class='event-date'>🗓 $event_date</p>
                            <p class='created-date'>Izveidots: $created_date</p>
                        </div>
                    </div>
                </a>";
            }

            echo $output ?: "<p>Nav sludinājuma.</p>";
        } catch (PDOException $e) {
            error_log("Error fetching own events: " . $e->getMessage());
            echo "<p>Kļūda! Mēģiniet vēlāk.</p>";
        }
        exit();
    }

    if ($action === 'joined') {
        try {
            $stmt = $pdo->prepare("
                SELECT e.ID_Event, e.title, e.description, e.date, e.created_at
                FROM Events e
                JOIN Volunteers v ON e.ID_Event = v.event_id
                WHERE v.user_id = :user_id AND (v.status = 'joined' OR v.status = 'waiting') AND e.deleted = 0
                ORDER BY e.date DESC
            ");
            $stmt->execute(['user_id' => $userId]);
            $results = $stmt->fetchAll();

            if ($results) {
                foreach ($results as $row) {
                    $event_id = $row['ID_Event'];
                    $title = htmlspecialchars($row['title']);
                    $description = htmlspecialchars($row['description']);
                    $event_date = date("d.m.Y", strtotime($row['date']));
                    $created_date = date("d.m.Y", strtotime($row['created_at']));

                    echo "
                        <a  class='event-link'>
                            <div class='event'>
                                <h2>$title</h2>
                                <div class='description'>$description</div>
                                <hr>
                                <div class='dates'>
                                    <p class='event-date'>🗓 $event_date</p>
                                    <p class='created-date'>Izveidots: $created_date</p>
                                </div>
                            </div>
                        </a>
                    ";
                }
            } else {
                echo "<p>Pagaidām nav pieteikumu.</p>";
            }
        } catch (PDOException $e) {
            error_log("Error fetching joined events: " . $e->getMessage());
            echo "<p>Kļūda! Mēģiniet vēlāk.</p>";
        }
        exit();
    }
if ($action === 'fetch_joined_users') {
    $event_id = $_GET['id'] ?? null;

    if (!$event_id) {
        echo json_encode(['status' => 'error', 'message' => 'Nav norādīts notikuma ID.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("
            SELECT v.ID_Volunteers, u.username, u.email, v.status 
            FROM Volunteers v 
            JOIN users u ON v.user_id = u.ID_user 
            WHERE v.event_id = :event_id AND v.status IN ('waiting', 'accepted', 'denied')
        ");
        $stmt->execute(['event_id' => $event_id]);
        $rows = $stmt->fetchAll();

        $joinedUsers = array_map(function ($row) {
            return [
                'id_volunteer' => $row['ID_Volunteers'],
                'username' => htmlspecialchars($row['username']),
                'email' => htmlspecialchars($row['email']),
                'status' => htmlspecialchars($row['status']),
            ];
        }, $rows);

        echo json_encode($joinedUsers);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Kļūda: ' . $e->getMessage()]);
    }
    exit();
}

if ($action === 'fetch_event_info') {
    $event_id = $_GET['id'] ?? null;

    if (!$event_id) {
        echo json_encode(['status' => 'error', 'message' => 'Nav norādīts notikuma ID.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as total_joined FROM Volunteers WHERE event_id = ? AND status IN ('waiting', 'accepted')");
        $stmt->execute([$event_id]);
        $data = $stmt->fetch();

        echo json_encode(['total_joined' => $data['total_joined'] ?? 0]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Kļūda: ' . $e->getMessage()]);
    }
    exit();
}

if ($action === 'fetch_event_details') {
    $event_id = $_GET['id'] ?? null;

    if (!$event_id) {
        die("Nav norādīts notikuma ID.");
    }

    try {
        $query = "
            SELECT e.*, c.Nosaukums AS category_name 
            FROM Events e
            LEFT JOIN Event_Categories ec ON e.ID_Event = ec.event_id
            LEFT JOIN VBC_Kategorijas c ON ec.category_id = c.Kategorijas_ID
            WHERE e.ID_Event = ?
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$event_id]);
        $event = $stmt->fetch();

        if (!$event) {
            die("Sludinājums nav atrasts!");
        }

        $title = htmlspecialchars($event['title']);
        $category = htmlspecialchars($event['category_name']);
        $city = htmlspecialchars($event['city']);
        $zip = htmlspecialchars($event['zip']);
        $location = htmlspecialchars($event['location']);
        $description = nl2br(htmlspecialchars($event['description']));
        $date = date("d.m.Y H:i", strtotime($event['date']));

        echo "
            <div class='event-icons'>
                <i class='bi bi-pencil edit-event-btn btn btn-outline-primary'></i>
                <i class='bi bi-trash edit-event-btn btn btn-outline-primary'></i>
            </div>
            <h1 class='title'>$title</h1>
           
            <p class='location'><strong><i class='bi bi-geo-alt'></i> Pilsēta:</strong> $city, $location | Zip: $zip</p>
            <hr>
            <p class='description'>$description</p>
            <hr>
            <p class='date'><strong><i class='bi bi-calendar-check'></i> Datums:</strong> $date</p>
            <div class='edit-actions mt-3' style='display: none;'>
                <button class='btn btn-success save-edit'>Saglabāt</button>
                <button class='btn btn-secondary cancel-edit'>Atcelt</button>
            </div>
        ";
    } catch (PDOException $e) {
        die("Kļūda: " . $e->getMessage());
    }
    exit();
}


//  <p class='category'><strong>🏷️ Kategorija:</strong> $category</p>
    echo "<p>Nederīgs pieprasījums.</p>";
    exit();
}

?>
