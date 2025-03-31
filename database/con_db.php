<?php
$serveris = "localhost";
$lietotajs = "phpmyadmin";
$parle = "qwerty123";
$datubaze = "phpmyadmin";

$savienojums = new mysqli($serveris, $lietotajs, $parle, $datubaze);

// Check if the connection was successful
if ($savienojums->connect_error) {
    die("Connection failed: " . $savienojums->connect_error);
}

// Set UTF-8 encoding
$savienojums->set_charset("utf8");
?>
