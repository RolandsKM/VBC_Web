<?php
$serveris = "localhost";
$lietotajs = "phpmyadmin";
$parle = "qwerty123";
$datubaze = "phpmyadmin";
$savienojums = mysqli_connect($serveris, $lietotajs, $parle, $datubaze);

if (!$savienojums) {
    die("Connection failed: " . mysqli_connect_error());
}

?>
