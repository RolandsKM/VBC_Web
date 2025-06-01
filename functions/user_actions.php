<?php
require_once '../config/con_db.php';

function getUserById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT ID_user, username, name, surname, email, profile_pic, bio, location, social_links, role, banned FROM users WHERE ID_user = :id");
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
}


function deleteUser($id) {
    global $pdo;
    $stmt = $pdo->prepare("DELETE FROM users WHERE ID_user = :id");
    $stmt->execute([':id' => $id]);
}


?>
