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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="user.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../functions/script.js"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://unpkg.com/vue@3/dist/vue.global.js"></script> 
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body id="body">
<div class="d-flex">
    <aside>
        <h3>Setting</h3>
        <nav class="d-flex flex-column">
            <a href="account_info.php" class=" py-2 px-3 text-decoration-none border-bottom">Account Information</a>
            <a href="change_password.php" class=" py-2 px-3 text-decoration-none border-bottom">Password</a>
            <a href="history.php" class=" py-2 px-3 text-decoration-none border-bottom">History</a>
            <a href="../main/logout.php" class=" py-2 px-3 text-decoration-none border-bottom">Logout</a>
        </nav>
    </aside>
    <section id="password-change" class="container">
    <h2 class="ps-3">Mainīt paroli</h2>

    <div class="form-section" style="max-width: 20rem;">
       
        <div id="password-dummies">
            <div class="mb-3">
                <label class="form-label">Pašreizējā parole</label>
                <input type="password" class="form-control" value="********" readonly>
            </div>
        </div>

        <div id="password-inputs" style="display: none;">
            <div class="mb-3">
                <label for="current_password" class="form-label">Pašreizējā parole</label>
                <input type="password" id="current_password" class="form-control">
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Jaunā parole</label>
                <input type="password" id="new_password" class="form-control">
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Apstiprini jauno paroli</label>
                <input type="password" id="confirm_password" class="form-control">
            </div>
        </div>

        <div class="d-flex justify-content-end">
            <button id="editPasswordButton" class="btn-edit me-2" title="Mainīt paroli">
                <i class="bi bi-pencil-square"></i>
            </button>
            <button id="savePasswordButton" class="btn-save" style="display:none;" title="Saglabāt">
                <i class="bi bi-check-lg"></i>
            </button>
        </div>
    </div>
</section>



<script>
    $('#editPasswordButton').click(function () {
       
        $('#password-dummies').hide();
        $('#password-inputs').show();

        $('#current_password, #new_password, #confirm_password').val('');
        $('#current_password').focus();

        $('#editPasswordButton').hide();
        $('#savePasswordButton').show();
    });

    $('#savePasswordButton').click(function () {
        const current = $('#current_password').val();
        const newPass = $('#new_password').val();
        const confirm = $('#confirm_password').val();

        if (!current || !newPass || !confirm) {
            alert("Lūdzu, aizpildiet visus laukus!");
            return;
        }

        if (newPass !== confirm) {
            alert("Jaunās paroles nesakrīt!");
            return;
        }

        $.post('../functions/auth_functions.php', {
            action: "change_password", // new flag
            current_password: current,
            new_password: newPass
        }, function(response) {

            alert(response);

            // Reset UI state
            $('#password-inputs').hide();
            $('#password-dummies').show();
            $('#editPasswordButton').show();
            $('#savePasswordButton').hide();
        }).fail(function(xhr) {
            alert("Kļūda: " + xhr.responseText);
        });
    });
</script>



</div>
<?php include '../main/footer.php'; ?>

</body>
</html>