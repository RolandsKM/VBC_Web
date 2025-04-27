<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../main/header.php'; 
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notikums</title>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../database/script.js"></script>
</head>
<body>

<section id="my-event">
    <div class="container p-0 m-0">
        <a href="javascript:history.back()" class="btn mb-3">⬅ Atpakaļ</a>
        <input type="hidden" id="edit-event-id" value="<?= htmlspecialchars($_GET['id']) ?>">

        <div class="card shadow p-4" id="event-details">
            <!-- Event content will be loaded here via JS -->
        </div>
    </div>
</section>

<section id="my-event-info">
    <div class="group">
        <div class="amount-joined shadow p-4">
            <p id="joined-count">0</p>
            <i class="fa-solid fa-people-group"></i>
        </div>
    </div>
</section>
<section id="table">
<table class="table table-striped">
    <thead>
        <tr>
            <th>Nr.</th>
            <th>Lietotājvārds</th>
            <th>E-pasts</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody id="joined-users-table">
        <!-- Rows will be inserted dynamically -->
    </tbody>
</table>

</section>
</body>
</html>
