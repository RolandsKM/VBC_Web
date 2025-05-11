<?php
require_once 'con_db.php';

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ID_user, username, name, surname, email, profile_pic, bio, location, social_links, role, ip_address, banned FROM users WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    return $user;
}

function banUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("UPDATE users SET banned = 1 WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);
}

function unbanUser($id) {
    global $pdo;
    
    $stmt = $pdo->prepare("UPDATE users SET banned = 0 WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);

    
    $stmt = $pdo->prepare("DELETE FROM ip_banned WHERE user_id = :id");
    $stmt->execute([':id' => $id]);
}

function deleteUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);
}

function banUserIp($id) {
    global $pdo;

   
    $stmt = $pdo->prepare("SELECT ID_user, ip_address FROM users WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) return;

  
    $stmt = $pdo->prepare("UPDATE users SET banned = 1 WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);

    
    $stmt = $pdo->prepare("INSERT INTO ip_banned (user_id, ip) VALUES (:user_id, :ip)");
    $stmt->execute([':user_id' => $user['ID_user'], ':ip' => $user['ip_address']]);
}
?>
