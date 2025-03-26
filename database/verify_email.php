<?php
require "con_db.php";

if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = $savienojums->prepare("SELECT * FROM users WHERE verification_token = ?");
    $query->bind_param("s", $token);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user) {
        $update = $savienojums->prepare("UPDATE users SET is_verified = 1, verification_token = NULL WHERE email = ?");
        $update->bind_param("s", $user['email']);
        $update->execute();
        echo "Jūsu e-pasts ir verificēts! Tagad varat <a href='login.php'>pieteikties</a>.";
    } else {
        echo "Nederīgs verifikācijas kods!";
    }

    $query->close();
    $savienojums->close();
}
?>
