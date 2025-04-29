<?php
require_once 'con_db.php';

// Set default values for each parameter
$category_id = isset($_GET['category_id']) && !empty($_GET['category_id']) ? intval($_GET['category_id']) : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';  // Add search parameter

$city = isset($_GET['city']) && !empty($_GET['city']) ? trim($_GET['city']) : '';
$date_from = isset($_GET['date_from']) && !empty($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) && !empty($_GET['date_to']) ? $_GET['date_to'] : '';

// Start the SQL query
$query = "
    SELECT e.ID_Event, e.title, e.description, e.date, e.created_at, e.city
    FROM Events e
    INNER JOIN Event_Categories ec ON e.ID_Event = ec.event_id
    WHERE e.deleted = 0 
    AND DATE(e.date) >= CURDATE()"; // Only future events

$params = [];
$types = '';

// Add the category filter if provided
if ($category_id > 0) {
    $query .= " AND ec.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

// Add the city filter if provided
if (!empty($city)) {
    $query .= " AND e.city = ?";
    $params[] = $city;
    $types .= 's';
}

// Add the date_from filter if provided
if (!empty($date_from)) {
    $query .= " AND e.date >= ?";
    $params[] = $date_from;
    $types .= 's';
}

// Add the date_to filter if provided
if (!empty($date_to)) {
    $query .= " AND e.date <= ?";
    $params[] = $date_to;
    $types .= 's';
}

// Add the search filter if provided
if (!empty($search)) {
    $query .= " AND (e.title LIKE ? OR e.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= 'ss';  // Two string parameters for LIKE search
}

// Order the events by date
$query .= " ORDER BY e.date ASC";

// Prepare the statement
$stmt = $savienojums->prepare($query);

// Bind the parameters dynamically if any
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

// Execute the query
$stmt->execute();
$result = $stmt->get_result();

// Prepare the output for events
$output = '';
while ($row = $result->fetch_assoc()) {
    $title = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
    $description = htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8');
    $city = htmlspecialchars($row['city'], ENT_QUOTES, 'UTF-8');
    $event_date = date("d.m.Y", strtotime($row['date']));
    $created_date = date("d.m.Y", strtotime($row['created_at']));
    $event_id = $row['ID_Event'];

    $short_description = (strlen($description) > 100) ? substr($description, 0, 180) . '...' : $description;

    $output .= "
    <a href='post-event.php?id=$event_id' class='event-link text-decoration-none text-dark'>
        <div class='event border p-3 mb-3 shadow-sm'>
            <div class='event-header'>
                <h2>$title</h2>
                <p class='event-date'>🗓 $event_date</p>
            </div>
            <p><strong>📍 Pilsēta:</strong> $city</p>
            <hr>
            <div class='description'>$short_description</div>
            <div class='dates mt-2'>
                <p class='created-date text-muted'>Izveidots: $created_date</p>
            </div>
        </div>
    </a>";
}

echo $output ?: "<p>Nav atrastu pasākumu šiem filtriem.</p>";


$stmt->close();
$savienojums->close();
?>
