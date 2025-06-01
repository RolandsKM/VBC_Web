<?php


require_once '../database/con_db.php';
$users = [];

try {
    $query = $pdo->prepare("SELECT ID_user, username, email, banned FROM users WHERE role = 'user'");
    $query->execute();
    $users = $query->fetchAll();

} catch (PDOException $e) {
    echo "Kļūda, neizdevās ielādēt lietotājus: " . $e->getMessage();
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
        <h4 class="mb-3">Lietotāji</h4>
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>Lietotājvārds</th>
                    <th>E-pasts</th>
                    <th>Darbība</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr class="<?= $user['banned'] ? 'table-danger' : '' ?>">
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td>
                            <a href="user-details.php?id=<?= $user['ID_user'] ?>" class="btn btn-sm btn-primary">Atvērt</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>


        </div>

    </div>
</div>


</body>
</html>
