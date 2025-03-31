<?php
include 'con_db.php'; 

$sql = "SELECT vk.Kategorijas_ID, vk.Nosaukums, vk.Datums, vk.color, 
               COUNT(ec.event_id) AS amount
        FROM VBC_Kategorijas vk
        LEFT JOIN Event_Categories ec ON vk.Kategorijas_ID = ec.category_id
        GROUP BY vk.Kategorijas_ID, vk.Nosaukums, vk.Datums, vk.color
        ORDER BY vk.Datums ASC, vk.Kategorijas_ID ASC, vk.color ASC, vk.Nosaukums ASC";

$result = $savienojums->query($sql);

$categories = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
} else {
    echo "Nav atrastas kategorijas.";
}

$savienojums->close();
?>
