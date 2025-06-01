<?php

require_once '../database/user_actions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: user_manager.php");
    exit();
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ban'])) {
        banUser($_POST['user_id']);
        header("Location: user-details.php?id=" . $_POST['user_id']);
        exit();
    }
    if (isset($_POST['unban'])) {
        unbanUser($_POST['user_id']);
        header("Location: user-details.php?id=" . $_POST['user_id']);
        exit();
    }
    
    
    if (isset($_POST['delete'])) {
        deleteUser($_POST['user_id']);
        header("Location: user-manager.php");
        exit();
    }
 
    
}
$user = getUserById($id);

if (!$user) {
    echo "<div class='alert alert-danger m-4'>Lietotājs nav atrasts.</div>";
    exit();
}
?>



<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VBC-Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
<div class="admin-layout">

    <?php include 'sidebar.php'; ?>

    <div class="main-content">

        <?php include 'header.php'; ?>
<!-- BODY----------------------------------------------------------------------- -->
        <div class="admin-body ">
                
            <div class="container mt-4">
                <h4>Lietotāja informācija</h4>
                <table class="table table-bordered w-50">
                    <tr><th>Lietotājvārds</th><td><?= htmlspecialchars($user['username']) ?></td></tr>
                    <tr><th>Vārds</th><td><?= htmlspecialchars($user['name']) ?></td></tr>
                    <tr><th>Uzvārds</th><td><?= htmlspecialchars($user['surname']) ?></td></tr>
                    <tr><th>E-pasts</th><td><?= htmlspecialchars($user['email']) ?></td></tr>
                   
                    <tr><th>Lokācija</th><td><?= htmlspecialchars($user['location']) ?></td></tr>
                    <tr><th>Biogrāfija</th><td><?= htmlspecialchars($user['bio']) ?></td></tr>
                    <tr><th>Saite</th><td><?= htmlspecialchars($user['social_links']) ?></td></tr>
                    <tr><th>Loma</th><td><?= htmlspecialchars($user['role']) ?></td></tr>
                    <tr><th>Banned</th><td><?= $user['banned'] ? 'Jā' : 'Nē' ?></td></tr>
                </table>

                <form method="POST" class="d-flex gap-2">
                    <input type="hidden" name="user_id" value="<?= $user['ID_user'] ?>">

                    <?php if (!$user['banned']): ?>
                        <button type="submit" name="ban" class="btn btn-warning">Bloķēt</button>
                      
                    <?php else: ?>
                        <button type="submit" name="unban" class="btn btn-success">Atbloķēt</button>
                    <?php endif; ?>

                    <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Vai tiešām dzēst lietotāju?')">Dzēst</button>
                    <a href="user_manager.php" class="btn btn-secondary">Atpakaļ</a>
                </form>

            </div>



        </div>

    </div>
</div>


</body>
</html>
