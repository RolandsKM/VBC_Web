<?php
require_once '../config/con_db.php';

$sql = "SELECT vk.Kategorijas_ID, vk.Nosaukums, vk.Datums, vk.color, vk.icon, 
       COUNT(CASE WHEN e.deleted = 0 THEN ec.event_id END) AS amount
FROM VBC_Kategorijas vk
LEFT JOIN Event_Categories ec ON vk.Kategorijas_ID = ec.category_id
LEFT JOIN Events e ON ec.event_id = e.ID_Event
GROUP BY vk.Kategorijas_ID, vk.Nosaukums, vk.Datums, vk.color
ORDER BY vk.Datums ASC, vk.Kategorijas_ID ASC, vk.color ASC, vk.Nosaukums ASC";

$stmt = $pdo->query($sql);  
$categories = $stmt->fetchAll();

if (empty($categories)) {
    echo "Nav atrastas kategorijas.";
}
?>
