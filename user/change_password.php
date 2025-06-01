<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../css/templates/header.php'; 
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vietējais Brīvprātīgais Centrs - Mainīt paroli</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Quicksand:wght@300;400;500;600;700&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Same styles as in account_info.php */
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
            margin-top:8rem;
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
        

        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--dark-gray);
        }
        
        .password-strength {
            height: 4px;
            background: #eee;
            margin-top: 5px;
            border-radius: 2px;
            overflow: hidden;
        }
        
        .password-strength-bar {
            height: 100%;
            width: 0;
            transition: width 0.3s ease;
        }
    </style>
</head>
<body>
<div class="settings-container">
    <?php include 'settings_aside.php'; ?>
    
    <main class="settings-main">
        <h2 class="mb-4">Mainīt paroli</h2>
        
        <div class="form-section">
            <div id="password-dummies">
                <div class="mb-4">
                    <label class="form-label">Pašreizējā parole</label>
                    <input type="password" class="form-control" value="••••••••" readonly>
                </div>
            </div>

            <div id="password-inputs" style="display: none;">
                <div class="mb-4">
                    <label for="current_password" class="form-label">Pašreizējā parole</label>
                    <input type="password" id="current_password" class="form-control">
                   
                </div>
                
                <div class="mb-4">
                    <label for="new_password" class="form-label">Jaunā parole</label>
                    <input type="password" id="new_password" class="form-control">
                   
                    <div class="password-strength">
                        <div class="password-strength-bar" id="passwordStrengthBar"></div>
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Apstipriniet jauno paroli</label>
                    <input type="password" id="confirm_password" class="form-control">
                    
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button id="editPasswordButton" class="btn-edit me-2">
                    <i class="bi bi-pencil-square me-1"></i> Mainīt paroli
                </button>
                <button id="savePasswordButton" class="btn-save" style="display:none;">
                    <i class="bi bi-check-lg me-1"></i> Saglabāt
                </button>
            </div>
        </div>
    </main>
</div>

<script>
$(document).ready(function() {
    // Password toggle functionality
    function setupPasswordToggle(buttonId, inputId) {
        $(buttonId).click(function() {
            const input = $(inputId);
            const icon = $(this).find('i');
            if (input.attr('type') === 'password') {
                input.attr('type', 'text');
                icon.removeClass('bi-eye').addClass('bi-eye-slash');
            } else {
                input.attr('type', 'password');
                icon.removeClass('bi-eye-slash').addClass('bi-eye');
            }
        });
    }
    
    setupPasswordToggle('#toggleCurrentPassword', '#current_password');
    setupPasswordToggle('#toggleNewPassword', '#new_password');
    setupPasswordToggle('#toggleConfirmPassword', '#confirm_password');
    
    // Password strength indicator
    $('#new_password').on('input', function() {
        const password = $(this).val();
        let strength = 0;
        
        if (password.length > 0) strength += 20;
        if (password.length >= 8) strength += 20;
        if (/[A-Z]/.test(password)) strength += 20;
        if (/[0-9]/.test(password)) strength += 20;
        if (/[^A-Za-z0-9]/.test(password)) strength += 20;
        
        $('#passwordStrengthBar').css('width', strength + '%');
        
        if (strength < 40) {
            $('#passwordStrengthBar').css('background', '#e74c3c');
        } else if (strength < 80) {
            $('#passwordStrengthBar').css('background', '#f39c12');
        } else {
            $('#passwordStrengthBar').css('background', '#2ecc71');
        }
    });
    
    // Edit/save password
    $('#editPasswordButton').click(function() {
        $('#password-dummies').hide();
        $('#password-inputs').show();
        $('#current_password').focus();
        $('#editPasswordButton').hide();
        $('#savePasswordButton').show();
    });
    
    $('#savePasswordButton').click(function() {
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
        
        if (newPass.length < 8) {
            alert("Parolei jābūt vismaz 8 simbolus garai!");
            return;
        }
        
        $.post('../functions/auth_functions.php', {
            action: "change_password",
            current_password: current,
            new_password: newPass
        }, function(response) {
            alert(response);
            $('#password-inputs').hide();
            $('#password-dummies').show();
            $('#current_password, #new_password, #confirm_password').val('');
            $('#editPasswordButton').show();
            $('#savePasswordButton').hide();
        }).fail(function(xhr) {
            alert("Kļūda: " + xhr.responseText);
        });
    });
});
</script>

<?php include '../main/footer.php'; ?>
</body>
</html>