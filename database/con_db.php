<?php
$serveris = "localhost";
$lietotajs = "phpmyadmin";
$parle = "=m8PpG(vnpW[-m]-";
$datubaze = "phpmyadmin";

$savienojums = new mysqli($serveris, $lietotajs, $parle, $datubaze);


if ($savienojums->connect_error) {
    die("Connection failed: " . $savienojums->connect_error);
}

$savienojums->set_charset("utf8");
?>
