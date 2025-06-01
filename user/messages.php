<?php


session_start();
date_default_timezone_set('Europe/Riga'); 
if (!isset($_SESSION['ID_user'])) {
    header("Location: ../main/login.php");
    exit();
}

require_once '../config/con_db.php';
include '../css/templates/header.php';  
$currentUserId = $_SESSION['ID_user'];

$stmt = $pdo->prepare("
    SELECT 
        u.ID_user, 
        u.username, 
        u.profile_pic, 
        MAX(m.sent_at) AS last_msg, 
        m.event_id,
        e.title AS event_title
    FROM messages m
    JOIN users u ON (u.ID_user = CASE 
                                    WHEN m.from_user_id = ? THEN m.to_user_id 
                                    ELSE m.from_user_id 
                                  END)
    JOIN Events e ON e.ID_Event = m.event_id
    WHERE m.from_user_id = ? OR m.to_user_id = ?
    GROUP BY u.ID_user, m.event_id, e.title
    ORDER BY last_msg DESC
");

$stmt->execute([$currentUserId, $currentUserId, $currentUserId]);

$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Sarakste</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="user.css" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body id="msg">

    <aside class="contacts show">
        <div class="contact-header p-3 border-bottom bg-light d-flex justify-content-between align-items-center">
            <h2 class="m-0">Sarakstes</h2>
            <a href="javascript:history.back()" class="btn-back mb-3">⬅ Atpakaļ</a>
        </div>
        <div class="p-3 border-bottom">
            <input type="text" class="form-control" id="contactSearch" placeholder="Meklēt kontaktu..." />
        </div>
        <ul class="list-group list-group-flush" id="contactList">
            <?php foreach ($contacts as $contact): ?>
                <li class="contact px-3 py-2 d-flex align-items-start gap-2" 
                    data-user-id="<?= $contact['ID_user'] ?>" 
                    data-event-id="<?= $contact['event_id'] ?>">
                    <img src="../functions/assets<?= htmlspecialchars($contact['profile_pic']) ?>" alt="pfp" width="40" height="40" class="rounded-circle mt-1" />
                    <div class="contact-info flex-grow-1">
                        <div class="fw-semibold"><?= htmlspecialchars($contact['username']) ?></div>
                        <div class="text-muted small">
                            Notikums: <span class="text-ellipsis"><?= htmlspecialchars($contact['event_title']) ?></span>
                        </div>
                    </div>
                    <div class="last-msg-time small text-muted text-nowrap">
                        <?= date('H:i', strtotime($contact['last_msg'])) ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <main class="chat-container">
        <div class="chat-header bg-light">
            <div class="d-flex align-items-center flex-grow-1">
                <img id="chatHeaderPfp" src="" alt="Profile" width="40" height="40" class="rounded-circle me-2" />
                <div>
                    <div id="chatHeaderUsername" class="fw-semibold"></div>
                    <div id="chatHeaderEvent" class="small text-muted"></div>
                </div>
            </div>
        </div>

        <div class="chat">
            <div id="chatArea" class="d-flex flex-column h-100">
                <div class="chat-messages" id="chatMessages">
                    <div class="no-chat-selected text-center text-muted py-5">
                        <i class="bi bi-chat-square-text fs-1"></i>
                        <p class="mt-2">Izvēlieties saraksti, lai sāktu tērzēšanu</p>
                    </div>
                </div>
            </div>

            <div id="chatInputWrapper">
                <textarea id="chatInput" placeholder="Rakstiet ziņu..." rows="1"></textarea>
                <button id="sendChatBtn" title="Send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </main>

<script>
let currentUserId = <?= json_encode($currentUserId) ?>;
let selectedUserId = null;
let selectedEventId = null;
let pollingInterval = null;

function loadMessages(withUserId, eventId) {
    $.post('../functions/chat_functions.php', {
        action: 'fetch_messages',
        user1: currentUserId,
        user2: withUserId,
        event_id: eventId
    }, function(response) {
        const res = JSON.parse(response);
        const chatBox = $('#chatMessages');

        if (res.status === 'success') {
            const isAtBottom = Math.abs(chatBox[0].scrollHeight - chatBox[0].scrollTop - chatBox.outerHeight()) < 10;

            chatBox.find('.no-chat-selected').remove();

            if (res.messages.length === 0) {
                chatBox.html('<div class="text-center text-muted mt-3">Nav ziņu šim notikumam ar šo lietotāju.</div>');
            } else {
                chatBox.empty();
                res.messages.forEach(msg => {
                    const isSent = msg.from_user_id == currentUserId;
                    const bubbleClass = isSent ? 'msg-bubble msg-sent' : 'msg-bubble msg-received';
                    const timeString = formatMessageTime(msg.sent_at);

                    chatBox.append(`
                        <div class="message-container ${isSent ? 'sent' : 'received'}">
                            <div class="${bubbleClass}">
                                <div class="message-text">${$('<div>').text(msg.message).html()}</div>
                                <div class="message-time">${timeString}</div>
                            </div>
                        </div>
                    `);
                });

                if (isAtBottom) {
                    chatBox.scrollTop(chatBox[0].scrollHeight);
                }
            }
        } else {
            chatBox.empty().append(`<div class="text-danger p-3">${res.message}</div>`);
        }
    });
}


function formatMessageTime(timestamp) {
    const rigaTime = new Date(timestamp);

    const formatter = new Intl.DateTimeFormat('lv-LV', {
        timeZone: 'Europe/Riga',
        hour: '2-digit',
        minute: '2-digit'
    });

    return formatter.format(rigaTime);
}


function startPolling() {
    if (pollingInterval) clearInterval(pollingInterval);
    pollingInterval = setInterval(() => {
        if (selectedUserId && selectedEventId) {
            loadMessages(selectedUserId, selectedEventId);
        }
    }, 2000);
}

function sendMessage() {
    const msg = $('#chatInput').val().trim();
    if (!msg || !selectedUserId || !selectedEventId) return;

    $.post('../functions/chat_functions.php', {
        action: 'send_message',
        from_user: currentUserId,
        to_user: selectedUserId,
        event_id: selectedEventId,
        message: msg
    }, function(response) {
        const res = JSON.parse(response);
        if (res.status === 'success') {
            $('#chatInput').val('');
            loadMessages(selectedUserId, selectedEventId);
        }
    });
}

function updateChatHeader(contactElement) {
    selectedUserId = parseInt(contactElement.data('user-id'));
    selectedEventId = parseInt(contactElement.data('event-id'));

    const username = contactElement.find('.fw-semibold').text();
    const eventName = contactElement.find('.text-muted').text().replace('Notikums: ', '');
    const profilePic = contactElement.find('img').attr('src');

    $('#chatHeaderUsername').text(username);
    $('#chatHeaderEvent').text(eventName);
    $('#chatHeaderPfp').attr('src', profilePic);

    $('.chat-header').addClass('active');
    $('#chatInputWrapper').addClass('active');
    startPolling();
}


$('#contactList').on('click', '.contact', function() {
    const contactElement = $(this);
    updateChatHeader(contactElement);
    loadMessages(selectedUserId, selectedEventId);
});

$('#sendChatBtn').on('click', sendMessage);
$('#chatInput').on('keypress', function(e) {
    if (e.which === 13 && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
    }
});


$('#chatInput').on('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});
$('#contactSearch').on('input', function() {
    const query = $(this).val().toLowerCase().trim();

    
    const $contacts = $('#contactList .contact');
    const $matches = $contacts.filter(function() {
        const username = $(this).find('.fw-semibold').text().toLowerCase();
        return username.indexOf(query) !== -1;
    });
    const $nonMatches = $contacts.filter(function() {
        const username = $(this).find('.fw-semibold').text().toLowerCase();
        return username.indexOf(query) === -1;
    });

    
    $('#contactList').empty().append($matches).append($nonMatches);
});



</script>
<!--  -->

</body>
</html>
