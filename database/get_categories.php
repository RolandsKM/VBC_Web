<?php
require_once 'con_db.php';

try {

    $stmt = $pdo->prepare("SELECT Kategorijas_ID, Nosaukums, color FROM VBC_Kategorijas");
    $stmt->execute();

    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Kļūda vaicājumā: " . $e->getMessage());
    $categories = [];
}


if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    foreach ($categories as $category) {
        echo "<option value='" . htmlspecialchars($category['Kategorijas_ID'], ENT_QUOTES, 'UTF-8') . "'>" 
            . htmlspecialchars($category['Nosaukums'], ENT_QUOTES, 'UTF-8') . "</option>";
    }
    exit();
}
?>
