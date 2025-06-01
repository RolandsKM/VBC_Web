<?php
require_once '../config/con_db.php';

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$city = isset($_GET['city']) ? trim($_GET['city']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 12;
$offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

try {
    $query = "
       SELECT 
    e.ID_Event, e.title, e.description, e.date, e.created_at, e.city,
    u.username, u.profile_pic
FROM Events e
INNER JOIN Event_Categories ec ON e.ID_Event = ec.event_id
LEFT JOIN users u ON e.user_id = u.ID_user
WHERE e.deleted = 0 
AND DATE(e.date) >= CURDATE()

    ";

    $params = [];

    if ($category_id > 0) {
        $query .= " AND ec.category_id = :category_id";
        $params[':category_id'] = $category_id;
    }
    if (!empty($city)) {
        $query .= " AND e.city = :city";
        $params[':city'] = $city;
    }
    if (!empty($date_from)) {
        $query .= " AND e.date >= :date_from";
        $params[':date_from'] = $date_from;
    }
    if (!empty($date_to)) {
        $query .= " AND e.date <= :date_to";
        $params[':date_to'] = $date_to;
    }
if ($search !== '') {
    $query .= " AND (e.title LIKE :search1 OR e.description LIKE :search2)";
    $params[':search1'] = "%$search%";
    $params[':search2'] = "%$search%";
}


    $query .= " ORDER BY e.date ASC LIMIT :limit OFFSET :offset";

    $stmt = $pdo->prepare($query);
    
    // Bind all parameters
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    
    // Bind pagination parameters
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();

    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $output = '';
    foreach ($events as $row) {
        $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
        $description = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
        $city = htmlspecialchars($row['city'], ENT_QUOTES, 'UTF-8');
        $event_date = date("d.m.Y", strtotime($row['date']));
        $created_date = date("d.m.Y", strtotime($row['created_at']));
        $event_id = $row['ID_Event'];

        $short_description = (strlen($description) > 100) ? mb_substr($description, 0, 180) . '...' : $description;

        $username = htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8');
        $profilePic = !empty($row['profile_pic']) ? htmlspecialchars($row['profile_pic'], ENT_QUOTES, 'UTF-8') : 'default.jpg'; // fallback image

 $output .= "
    <a href='post-event.php?id=$event_id' class='event-link text-decoration-none text-dark'>
        <div class='event border p-3 mb-3 shadow-sm'>

            <h2 class='h5'>$title</h2>

            <div class='description mb-2'>$short_description</div>

            <hr>

            <div class='dates'>
                <p class='event-date'>🗓 $event_date</p>
                <p class='created-date'>Izveidots: $created_date</p>
            </div>

        </div>
    </a>";


    }

    echo $output ?: "<p>Nav atrastu pasākumu šiem filtriem.</p>";

} catch (PDOException $e) {
    error_log("Kļūda: " . $e->getMessage());
    echo "<p>Radās kļūda datu ielādē.</p>".$e->getMessage();
}
?>
