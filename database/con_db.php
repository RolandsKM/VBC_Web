<?php
$host = 'localhost';
$db   = 'phpmyadmin';
$user = 'phpmyadmin';
$pass = 'qwerty123';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  // Kļūdas metīs Exception, vieglāk debugot
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        // Saņemsi asociatīvus masīvus
    PDO::ATTR_EMULATE_PREPARES   => false,                   // Lai izmantotu īstus prepared statements (drošāk pret SQL injekcijām)
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
