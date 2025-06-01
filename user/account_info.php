<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../css/templates/header.php'; 
require_once '../config/con_db.php';

$userID = $_SESSION['ID_user'];
$query = $pdo->prepare("SELECT `username`, `name`, `surname`, `email`, `location` FROM `users` WHERE `ID_user` = ?");
$query->execute([$userID]);
$user = $query->fetch();
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs - Settings</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --light-gray: #f8f9fa;
            --dark-gray: #6c757d;
        }
        
        body {
            font-family: 'Quicksand', sans-serif;
            background-color: #f5f7fa;
            padding:0;
            padding-top:8rem;
        }
        
        .settings-container {
            display: flex;
            flex-direction: column;
            min-height: calc(100vh - 120px);
           
        }
        
        @media (min-width: 992px) {
            .settings-container {
                flex-direction: row;
            }
        }
        
        .settings-aside {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 0;
            width: 100%;
        }
        
        @media (min-width: 992px) {
            .settings-aside {
                width: 280px;
                min-height: calc(100vh - 120px);
                border-right: 1px solid #eee;
            }
        }
        
        .settings-aside h3 {
            padding: 20px;
            margin: 0;
            font-weight: 600;
            color: var(--primary-color);
            border-bottom: 1px solid #eee;
        }
        
        .settings-nav {
            display: flex;
            flex-direction: row;
            overflow-x: auto;
        }
        
        @media (min-width: 992px) {
            .settings-nav {
                flex-direction: column;
                overflow-x: visible;
            }
        }
        
        .settings-nav a {
            padding: 12px 20px;
            text-decoration: none;
            color: var(--dark-gray);
            border-bottom: 2px solid transparent;
            white-space: nowrap;
            transition: all 0.3s ease;
        }
        
        .settings-nav a:hover, .settings-nav a.active {
            color: var(--primary-color);
            background-color: var(--light-gray);
            border-bottom-color: var(--secondary-color);
        }
        
        .settings-main {
            flex: 1;
            padding: 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        @media (min-width: 992px) {
            .settings-main {
                padding: 40px;
            }
        }
        
        .form-section {
            max-width: 500px;
            margin-left: 0; /* Align left */
            margin-right: auto;
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
        }
        
        .btn-edit, .btn-save {
            border: none;
            border-radius: 6px;
            padding: 8px 12px;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background-color: var(--light-gray);
            color: var(--dark-gray);
        }
        
        .btn-edit:hover {
            background-color: #e9ecef;
        }
        
        .btn-save {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-save:hover {
            background-color: #2980b9;
        }
        
        .input-group {
            position: relative;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--dark-gray);
        }
    </style>
</head>
<body>
<div class="settings-container">
    <?php include 'settings_aside.php'; ?>
    
    <main class="settings-main">
        <h2 class="mb-4">Konta informācija</h2>
        
        <div class="form-section">
           
            <div class="mb-4">
                <label for="username" class="form-label">Lietotājvārds</label>
                <input type="text" id="username" value="<?= htmlspecialchars($user['username']); ?>" readonly class="form-control">
            </div>

            <div class="mb-4">
                <label for="name" class="form-label">Vārds</label>
                <input type="text" id="name" value="<?= htmlspecialchars($user['name']); ?>" readonly class="form-control">
            </div>

            <div class="mb-4">
                <label for="surname" class="form-label">Uzvārds</label>
                <input type="text" id="surname" value="<?= htmlspecialchars($user['surname']); ?>" readonly class="form-control">
            </div>

            <div class="d-flex justify-content-end mb-4">
                <button id="editMainButton" class="btn-edit me-2">
                    <i class="bi bi-pencil-square me-1"></i> Rediģēt
                </button>
                <button id="saveMainButton" class="btn-save" style="display:none;">
                    <i class="bi bi-check-lg me-1"></i> Saglabāt
                </button>
            </div>

            <div class="mb-4">
                <label for="email" class="form-label">E-pasts</label>
                <input type="email" id="email" value="<?= htmlspecialchars($user['email']); ?>" readonly class="form-control">
            </div>

            <div class="input-group mb-4" id="emailPasswordGroup" style="display:none;">
                <input type="password" id="emailPassword" class="form-control" placeholder="Ievadiet paroli, lai apstiprinātu">
                <span class="password-toggle" id="toggleEmailPassword">
                    <i class="bi bi-eye"></i>
                </span>
            </div>

            <div class="d-flex justify-content-end mb-4">
                <button id="editEmailButton" class="btn-edit me-2">
                    <i class="bi bi-pencil-square me-1"></i> Rediģēt
                </button>
                <button id="saveEmailButton" class="btn-save" style="display:none;">
                    <i class="bi bi-check-lg me-1"></i> Saglabāt
                </button>
            </div>

            <div class="mb-4">
                <label for="location" class="form-label">Atrašanās vieta</label>
                <input type="text" id="location" value="<?= htmlspecialchars($user['location'] ?? ''); ?>" readonly class="form-control">
            </div>

            <div class="d-flex justify-content-end">
                <button id="editLocationButton" class="btn-edit me-2">
                    <i class="bi bi-pencil-square me-1"></i> Rediģēt
                </button>
                <button id="saveLocationButton" class="btn-save" style="display:none;">
                    <i class="bi bi-check-lg me-1"></i> Saglabāt
                </button>
            </div>
        </div>
        <form id="profileForm" method="post" enctype="multipart/form-data">
            <div class="mb-4">
                <label for="profile_pic" class="form-label">Profila bilde</label><br>
                <?php if (!empty($user['profile_pic'])): ?>
                    <img src="../functions/assets/<?= htmlspecialchars($user['profile_pic']) ?>" alt="Profila bilde" width="100" class="mb-2">
                <?php endif; ?>
                <input type="file" name="profile_pic" id="profile_pic" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary">Augšupielādēt attēlu</button>
        </form>

    </main>
</div>

<script>
$(document).ready(function() {
   $('#profileForm').on('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    $.ajax({
        url: '../functions/upload_profile_pic.php',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
            alert("Veiksmīgi Augšupielādēji!");
            location.reload(); 
        },
        error: function(xhr) {
            alert('Kļūda: ' + xhr.responseText);
        }
    });
});

    $('#toggleEmailPassword').click(function() {
        const input = $('#emailPassword');
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('bi-eye').addClass('bi-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('bi-eye-slash').addClass('bi-eye');
        }
    });


    $('#editMainButton').click(function() {
        $('#username, #name, #surname').prop('readonly', false).addClass('bg-light');
        $('#editMainButton').hide();
        $('#saveMainButton').show();
    });

    $('#saveMainButton').click(function() {
        $.post('../database/update_user.php', {
            username: $('#username').val(),
            name: $('#name').val(),
            surname: $('#surname').val()
        }, function(response) {
            alert('Informācija saglabāta!');
            $('#username, #name, #surname').prop('readonly', true).removeClass('bg-light');
            $('#saveMainButton').hide();
            $('#editMainButton').show();
        }).fail(function(xhr) {
            alert('Kļūda: ' + xhr.responseText);
        });
    });

    
    $('#editEmailButton').click(function() {
        $('#email').prop('readonly', false).addClass('bg-light');
        $('#emailPasswordGroup').show();
        $('#editEmailButton').hide();
        $('#saveEmailButton').show();
    });

    $('#saveEmailButton').click(function() {
        const password = $('#emailPassword').val();
        if (!password) {
            alert("Lūdzu ievadiet paroli!");
            return;
        }

        $.post('../database/update_user.php', {
            email: $('#email').val(),
            password: password
        }, function(response) {
            alert(response);
            $('#email').prop('readonly', true).removeClass('bg-light');
            $('#emailPasswordGroup').hide();
            $('#emailPassword').val('');
            $('#saveEmailButton').hide();
            $('#editEmailButton').show();
        }).fail(function(xhr) {
            alert('Kļūda: ' + xhr.responseText);
        });
    });


    $('#editLocationButton').click(function() {
        $('#location').prop('readonly', false).addClass('bg-light');
        $('#editLocationButton').hide();
        $('#saveLocationButton').show();
    });

    $('#saveLocationButton').click(function() {
        $.post('../database/update_user.php', {
            location: $('#location').val()
        }, function(response) {
            alert('Atrašanās vieta atjaunināta!');
            $('#location').prop('readonly', true).removeClass('bg-light');
            $('#saveLocationButton').hide();
            $('#editLocationButton').show();
        }).fail(function(xhr) {
            alert('Kļūda: ' + xhr.responseText);
        });
    });
});
</script>

<?php include '../main/footer.php'; ?>
</body>
</html>