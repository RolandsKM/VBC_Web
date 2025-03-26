<?php
session_start();
if(!isset($_SESSION['lietotajvardsPAB'])){
    header("Location: login.php");
    exit();
}
?>
