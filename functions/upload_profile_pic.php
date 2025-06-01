<?php
session_start();
require_once '../config/con_db.php';

if (!isset($_SESSION['ID_user'])) {
    http_response_code(403);
    exit("Unauthorized");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $userId = $_SESSION['ID_user'];
    $file = $_FILES['profile_pic'];
    $uploadDir = __DIR__ . '/assets/';

    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        exit("Nederīgs faila tips!");
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        exit("Fails ir pārāk liels! Maksimālais izmērs 5MB.");
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = 'profile_' . $userId . '_' . time() . '.' . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE ID_user = ?");
        $stmt->execute([$newFileName, $userId]);

        $_SESSION['profile_pic'] = $newFileName;

        session_write_close();
        // header("Location: ../user/user.php");
        exit();
    } else {
        http_response_code(500);
        exit("Augšupielāde neizdevās.");
    }
} else {
    http_response_code(400);
    exit("Nederīgs pieprasījums!");
}
?>
