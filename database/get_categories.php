<?php
require_once 'con_db.php';

$query = "SELECT Kategorijas_ID, Nosaukums, color FROM VBC_Kategorijas";
$result = $savienojums->query($query);

$categories = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

$savienojums->close(); 


if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    foreach ($categories as $category) {
        
        echo "<option value='" . htmlspecialchars($category['Kategorijas_ID']) . "'>" . htmlspecialchars($category['Nosaukums']) . "</option>";
    }
    exit(); 
}

?>
