<?php 
include '../css/templates/header.php'; 
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$login_error = $_SESSION['login_error'] ?? '';
$login_email = $_SESSION['login_email'] ?? '';
?>


<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="script.js" defer></script> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script>
</head>
<body class="bg-light">

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card p-4 shadow-lg" style="width: 350px;">
            <h3 class="text-center">Pieslēgties</h3>
            <form action="../database/auth_functions.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">E-pasts</label>
                    <input type="email" name="epasts" class="form-control" required value="<?= htmlspecialchars($login_email) ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Parole</label>
                    <input type="password" name="parole" class="form-control" required>
                </div>
                <?php if ($login_error): ?>
                    <div class="alert alert-danger text-center"><?= $login_error ?></div>
                <?php endif; ?>

                <button type="submit" name="ielogoties" class="btn btn-custom w-100">Ielogoties</button>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
            </form>

            <p class="text-center mt-3"><a href="register.php">Reģistrēties</a></p>
        </div>
    </div>

<?php include '../css/templates/footer.php'; ?>

</body>
</html>
<?php
unset($_SESSION['login_error'], $_SESSION['login_email']);
?>
