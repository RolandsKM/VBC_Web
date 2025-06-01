<?php
session_start();


if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}

header("Location: profile.php");
exit();
?> 