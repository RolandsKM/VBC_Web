<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../css/templates/header.php';  ?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs - Settings</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../functions/script.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
   
 
</head>
<body id="body">
    
<aside>
    <h3>Setting</h3>
    <nav class="d-flex flex-column">
        <a href="account_info.php" class=" py-2 px-3 text-decoration-none border-bottom">Account Information</a>
        <a href="change_password.php" class=" py-2 px-3 text-decoration-none border-bottom">Password</a>
        <a href="history.php" class=" py-2 px-3 text-decoration-none border-bottom">History</a>
        <a href="../main/logout.php" class=" py-2 px-3 text-decoration-none border-bottom">Logout</a>
    </nav>
</aside>
<?php include '../main/footer.php'; ?>

</body>
</html>