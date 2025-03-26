<?php include 'header.php'; ?>

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
            <form action="../database/login_function.php" method="POST">
    <div class="mb-3">
        <label class="form-label">E-pasts</label>
        <input type="email" name="epasts" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Parole</label>
        <input type="password" name="parole" class="form-control" required>
    </div>
    <button type="submit" name="ielogoties" class="btn btn-custom w-100">Ielogoties</button>

</form>

            <p class="text-center mt-3"><a href="register.php">Reģistrēties</a></p>
        </div>
    </div>
    <!-- <script src="https://accounts.google.com/gsi/client" async defer></script>

<div id="g_id_onload"
     data-client_id="661811357054-b2p41vqn7o2u99t48ujfu68anrkcg7sj.apps.googleusercontent.com"
     data-login_uri="https://kristovskis.lv/3pt2/makarovs/Local_V_Center/main/google_callback.php"
     data-auto_prompt="false">
</div>

<div class="g_id_signin"
     data-type="standard"
     data-size="large"
     data-theme="outline"
     data-text="sign_in_with"
     data-shape="rectangular"
     data-logo_alignment="left">
</div> -->

</body>
</html>