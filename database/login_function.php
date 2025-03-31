<?php
require "con_db.php";
session_start();

if (isset($_POST['ielogoties'])) {
    $email = htmlspecialchars($_POST['epasts']);
    $password = $_POST['parole'];

    $query = $savienojums->prepare("SELECT * FROM users WHERE email = ?");
    $query->bind_param("s", $email);
    $query->execute();
    $result = $query->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['surname'] = $user['surname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['ID_user'] = $user['ID_user'];  // This is important!
        header("Location: ../main/index.php");
    } else {
        $_SESSION['pazinojums'] = "Nepareizs e-pasts vai parole!";
        header("Location: ../main/login.php");
    }

    $query->close();
    $savienojums->close();
}
?>
