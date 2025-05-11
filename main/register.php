<?php 
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$form_data = $_SESSION['form_data'] ?? [];
$errors = $_SESSION['form_errors'] ?? [];

include 'header.php'; 
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
        <h3 class="text-center">Reģistrācija</h3>+
        <form action="../database/register_function.php" method="POST">
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-danger"><?= $errors['general'] ?></div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Lietotājvārds</label>
                <input type="text" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($form_data['username'] ?? '') ?>" required>
                <?php if (!empty($errors['username'])): ?>
                    <div class="invalid-feedback"><?= $errors['username'] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Vārds</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($form_data['name'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Uzvārds</label>
                <input type="text" name="surname" class="form-control" value="<?= htmlspecialchars($form_data['surname'] ?? '') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label">E-pasts</label>
                <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" value="<?= htmlspecialchars($form_data['email'] ?? '') ?>" required> 
                <?php if (!empty($errors['email'])): ?>
                    <div class="invalid-feedback"><?= $errors['email'] ?></div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Parole</label>
                <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" required>
                <?php if (!empty($errors['password'])): ?>
                    <div class="invalid-feedback"><?= $errors['password'] ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" name="registracija" class="btn btn-custom w-100">Reģistrēties</button>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        </form>


        <p class="text-center mt-3"><a href="login.php">Atpakaļ uz ielogošanos</a></p>
    </div>
</div>

</body>
</html>
<?php

unset($_SESSION['form_errors'], $_SESSION['form_data']);
?>
