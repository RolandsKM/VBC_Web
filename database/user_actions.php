<?php
require_once 'con_db.php';


function getUserById($id) {
    global $savienojums;
    $stmt = $savienojums->prepare("SELECT ID_user, username, name, surname, email, profile_pic, bio, location, social_links, role, ip_address, banned FROM users WHERE ID_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    return $user;
}


function banUser($id) {
    global $savienojums;
    $stmt = $savienojums->prepare("UPDATE users SET banned = 1 WHERE ID_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}
function unbanUser($id) {
    global $savienojums;

    $stmt = $savienojums->prepare("UPDATE users SET banned = 0 WHERE ID_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

 
    $stmt = $savienojums->prepare("DELETE FROM ip_banned WHERE user_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}



function deleteUser($id) {
    global $savienojums;
    $stmt = $savienojums->prepare("DELETE FROM users WHERE ID_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
}

function banUserIp($id) {
    global $savienojums;

  
    $stmt = $savienojums->prepare("SELECT ID_user, ip_address FROM users WHERE ID_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user) return;


    $stmt = $savienojums->prepare("UPDATE users SET banned = 1 WHERE ID_user = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

 
    $stmt = $savienojums->prepare("INSERT INTO ip_banned (user_id, ip) VALUES (?, ?)");
    $stmt->bind_param("is", $user['ID_user'], $user['ip_address']);
    $stmt->execute();
    $stmt->close();
}

?>
