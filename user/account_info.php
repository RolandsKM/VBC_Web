<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../main/header.php'; 

// Fetch user information from the database
require_once '../database/con_db.php';

$userID = $_SESSION['ID_user'];
$query = $savienojums->prepare("SELECT `username`, `name`, `surname`, `email`, `location` FROM `users` WHERE `ID_user` = ?");

$query->bind_param("i", $userID);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs - Settings</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

    <link rel="stylesheet" href="user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../database/script.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

</head>
<body id="body">
<div class="d-flex">
    <aside>
        <h3>Settings</h3>
        <nav class="d-flex flex-column">
            <a href="account_info.php" class=" py-2 px-3 text-decoration-none border-bottom">Account Information</a>
            <a href="change_password.php" class=" py-2 px-3 text-decoration-none border-bottom">Password</a>
            <a href="history.php" class=" py-2 px-3 text-decoration-none border-bottom">History</a>
            <a href="../main/logout.php" class=" py-2 px-3 text-decoration-none border-bottom">Logout</a>
        </nav>
    </aside>
        <section id="account-info" class="container">
            <h2 class="ps-3">Konta informācija</h2>

            <!-- Group container -->
            <div class="form-section" style="max-width: 20rem;">
                <!-- User Information Display -->
                <div class="mb-3">
                    <label for="username" class="form-label">Lietotājvārds</label>
                    <input type="text" id="username" value="<?= $user['username']; ?>" readonly class="form-control">
                </div>

                <div class="mb-3">
                    <label for="name" class="form-label">Vārds</label>
                    <input type="text" id="name" value="<?= $user['name']; ?>" readonly class="form-control">
                </div>

                <div class="mb-3">
                    <label for="surname" class="form-label">Uzvārds</label>
                    <input type="text" id="surname" value="<?= $user['surname']; ?>" readonly class="form-control">
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <button id="editMainButton" class="btn-edit me-2" title="Edit Info">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button id="saveMainButton" class="btn-save" style="display:none;" title="Save Info">
                        <i class="bi bi-check-lg"></i>
                    </button>

                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">E-pasts</label>
                    <input type="text" id="email" value="<?= $user['email']; ?>" readonly class="form-control">
                </div>

                <div class="d-flex justify-content-end mb-2">
                    <input type="password" id="emailPassword" class="form-control me-2" placeholder="Enter password to confirm" style="display:none; max-width: 200px;">
                </div>

                <div class="d-flex justify-content-end mb-4">
                    <button id="editEmailButton" class="btn-edit me-2" title="Edit Info">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button id="saveEmailButton" class="btn-save" style="display:none;" title="Save Info">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </div>


                <div class="mb-3">
                    <label for="location" class="form-label">Atrašanās vieta</label>
                    <input type="text" id="location" value="<?= $user['location'] ?? ''; ?>" readonly class="form-control">
                </div>

                <div class="d-flex justify-content-end">
                    <button id="editLocationButton" class="btn-edit me-2" title="Edit Info">
                    <i class="bi bi-pencil-square"></i></button>
                    <button id="saveLocationButton"  class="btn-save" style="display:none;" title="Save Info">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </div>
            </div>
        </section>


</div>

<script>
$(document).ready(function() {
    // Main edit/save buttons for username, name, surname
    $('#editMainButton').click(function() {
        $('#username, #name, #surname').prop('readonly', false);
        $('#editMainButton').hide();
        $('#saveMainButton').show();
    });

    $('#saveMainButton').click(function() {
        var username = $('#username').val();
        var name = $('#name').val();
        var surname = $('#surname').val();
        
        $.post('../database/update_user.php', {
            username: username,
            name: name,
            surname: surname
        }, function(response) {
            alert('Info saved!');
            $('#username, #name, #surname').prop('readonly', true);
            $('#saveMainButton').hide();
            $('#editMainButton').show();
        }).fail(function(xhr, status, error) {
            alert('Error: ' + error);
        });
    });

    // Separate edit/save buttons for email
    $('#editEmailButton').click(function() {
    $('#email').prop('readonly', false);
    $('#editEmailButton').hide();
    $('#saveEmailButton').show();
    $('#emailPassword').show();
});

$('#saveEmailButton').click(function() {
    var email = $('#email').val();
    var password = $('#emailPassword').val();

    if (!password) {
        alert("Please enter your password to confirm email change.");
        return;
    }

    $.post('../database/update_user.php', {
        email: email,
        password: password
    }, function(response) {
        alert(response);
        $('#email').prop('readonly', true);
        $('#saveEmailButton').hide();
        $('#editEmailButton').show();
        $('#emailPassword').hide().val('');
    }).fail(function(xhr, status, error) {
        alert('Error: ' + error);
    });
});

    // Separate edit/save buttons for location
$('#editLocationButton').click(function() {
    $('#location').prop('readonly', false);
    $('#editLocationButton').hide();
    $('#saveLocationButton').show();
});

$('#saveLocationButton').click(function() {
    var location = $('#location').val();

    $.post('../database/update_user.php', {
        location: location
    }, function(response) {
        alert('Location updated!');
        $('#location').prop('readonly', true);
        $('#saveLocationButton').hide();
        $('#editLocationButton').show();
    }).fail(function(xhr, status, error) {
        alert('Error: ' + error);
    });
});

});

</script>
</body>
</html>
