<?php
session_start();
require_once '../config/con_db.php';

$from = $_SESSION['ID_user'] ?? 0;
$to = intval($_GET['with_user'] ?? 0);

if ($from <= 0 || $to <= 0) exit;

$stmt = $pdo->prepare("SELECT * FROM messages 
    WHERE (from_user_id = ? AND to_user_id = ?) 
       OR (from_user_id = ? AND to_user_id = ?)
    ORDER BY sent_at ASC");
$stmt->execute([$from, $to, $to, $from]);

while ($msg = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $class = $msg['from_user_id'] == $from ? 'text-end' : 'text-start';
    echo "<div class='$class mb-2'><span class='p-2 bg-light rounded d-inline-block'>" . htmlspecialchars($msg['message']) . "</span><br><small class='text-muted'>" . $msg['sent_at'] . "</small></div>";
}
?>
