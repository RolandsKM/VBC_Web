<?php 
session_start();
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}
include '../main/header.php'; 
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notikums</title>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../database/script.js"></script>
</head>
<body>

<section id="my-event">
    <div class="container p-0 m-0">
        <a href="javascript:history.back()" class="btn mb-3">⬅ Atpakaļ</a>
        <input type="hidden" id="edit-event-id" value="<?= htmlspecialchars($_GET['id']) ?>">

        <div class="card shadow p-4" id="event-details">
            <!-- Event content will be loaded here via JS -->
        </div>
    </div>
</section>

<section id="my-event-info">
    <div class="group">
        <div class="amount-joined shadow p-4">
            <p id="joined-count">0</p>
            <i class="fa-solid fa-people-group"></i>
        </div>
    </div>
</section>
<section id="table">
    <h4 class="mb-3">Pieteikušies</h4>
    <table class="table table-striped" id="waiting-table">
        <thead>
            <tr>
                <th>Nr.</th>
                <th>Lietotājvārds</th>
                <th>E-pasts</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <!-- Waiting users -->
        </tbody>
    </table>

    <h4 class="mb-3 mt-5">Apstiprināti</h4>
    <table class="table table-striped" id="accepted-table">
        <thead>
            <tr>
                <th>Nr.</th>
                <th>Lietotājvārds</th>
                <th>E-pasts</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <!-- Accepted users -->
        </tbody>
    </table>

    <h4 class="mb-3 mt-5">Noraidītie</h4>
    <table class="table table-striped" id="denied-table">
        <thead>
            <tr>
                <th>Nr.</th>
                <th>Lietotājvārds</th>
                <th>E-pasts</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <!-- Denied users -->
        </tbody>
    </table>
</section>

<script>
$(document).ready(function() {
    const eventId = $('#edit-event-id').val();

    function loadJoinedUsers() {
        $.ajax({
            url: '../database/fetch_joined_users.php',
            method: 'GET',
            data: { id: eventId },
            success: function(data) {
                const users = JSON.parse(data);

                $('#joined-count').text(users.length);

                let waitingHtml = '';
                let acceptedHtml = '';
                let deniedHtml = '';

                users.forEach((user, index) => {
                    const row = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${user.username}</td>
                            <td>${user.email}</td>
                            <td>
                                <select class="form-select status-select" data-id="${user.id_volunteer}">
                                    <option value="waiting" ${user.status === 'waiting' ? 'selected' : ''}>Pieteicies</option>
                                    <option value="accepted" ${user.status === 'accepted' ? 'selected' : ''}>Apstiprināts</option>
                                    <option value="denied" ${user.status === 'denied' ? 'selected' : ''}>Noraidīts</option>
                                </select>
                            </td>
                        </tr>
                    `;

                    if (user.status === 'waiting') {
                        waitingHtml += row;
                    } else if (user.status === 'accepted') {
                        acceptedHtml += row;
                    } else if (user.status === 'denied') {
                        deniedHtml += row;
                    }
                });

                $('#waiting-table tbody').hide().html(waitingHtml).fadeIn(300);
                $('#accepted-table tbody').hide().html(acceptedHtml).fadeIn(300);
                $('#denied-table tbody').hide().html(deniedHtml).fadeIn(300);
            }
        });
    }

   
    loadJoinedUsers();

  
    $(document).on('change', '.status-select', function() {
        const volunteerId = $(this).data('id');
        const newStatus = $(this).val();

        $.ajax({
            url: '../database/update_volunteer_status.php',
            method: 'POST',
            data: {
                volunteer_id: volunteerId,
                status: newStatus
            },
            success: function(response) {
                if (response.trim() === 'success') {
                   
                    loadJoinedUsers();
                } else {
                    alert('Kļūda atjauninot statusu: ' + response);
                }
            }
        });
    });
});

</script>

</body>
</html>
