<?php
require_once 'con_db.php';

$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$city = isset($_GET['city']) ? trim($_GET['city']) : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 2; 
$offset = ($page - 1) * $limit;

$query = "
    SELECT e.ID_Event, e.title, e.description, e.date, e.created_at, e.city
    FROM Events e
    INNER JOIN Event_Categories ec ON e.ID_Event = ec.event_id
    WHERE ec.category_id = ? AND e.deleted = 0
";

// Build conditions dynamically
$params = [$category_id];
$types = 'i';

if (!empty($city)) {
    $query .= " AND e.city = ?";
    $params[] = $city;
    $types .= 's';
}

if (!empty($date_from)) {
    $query .= " AND e.date >= ?";
    $params[] = $date_from;
    $types .= 's';
}

if (!empty($date_to)) {
    $query .= " AND e.date <= ?";
    $params[] = $date_to;
    $types .= 's';
}

$query .= " ORDER BY e.date ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';

$stmt = $savienojums->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$output = '';
while ($row = $result->fetch_assoc()) {
    $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
    $event_date = date("d.m.Y", strtotime($row['date']));
    $created_date = date("d.m.Y", strtotime($row['created_at']));
    $event_id = $row['ID_Event'];

    // Limit description to 100 characters
    $short_description = (strlen($description) > 100) ? substr($description, 0, 180) . '...' : $description;

    $output .= "
    <a href='post-event.php?id=$event_id' class='event-link text-decoration-none text-dark'>
        <div class='event border p-3 mb-3 shadow-sm bg-white'>
            <div class='event-header'>
                <h2>$title</h2>
                <p class='event-date'>🗓 $event_date</p>
            </div>
            <div class='description'>$short_description</div>
            <div class='dates mt-2'>
                <p class='created-date text-muted'>Izveidots: $created_date</p>
            </div>
        </div>
    </a>";
}

$total_query = "
    SELECT COUNT(*) AS total
    FROM Events e
    INNER JOIN Event_Categories ec ON e.ID_Event = ec.event_id
    WHERE ec.category_id = ? AND e.deleted = 0
";

$total_params = [$category_id];
$total_types = 'i';

if (!empty($city)) {
    $total_query .= " AND e.city = ?";
    $total_params[] = $city;
    $total_types .= 's';
}

if (!empty($date_from)) {
    $total_query .= " AND e.date >= ?";
    $total_params[] = $date_from;
    $total_types .= 's';
}

if (!empty($date_to)) {
    $total_query .= " AND e.date <= ?";
    $total_params[] = $date_to;
    $total_types .= 's';
}

$total_stmt = $savienojums->prepare($total_query);
$total_stmt->bind_param($total_types, ...$total_params);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_events = $total_row['total'];

// Calculate total pages
$total_pages = ceil($total_events / $limit);

// Generate pagination links
$pagination = '';
if ($total_pages > 1) {
    $pagination .= '<div class="pagination">';
    for ($i = 1; $i <= $total_pages; $i++) {
        // Adding a JavaScript click handler to prevent page reload
        $pagination .= "<a href='javascript:void(0);' class='page-link' data-page='$i'>$i</a> ";
    }
    $pagination .= '</div>';
}


echo $output ?: "<p>Nav atrastu pasākumu šiem filtriem.</p>";
echo $pagination;

$stmt->close();
$total_stmt->close();
$savienojums->close();
