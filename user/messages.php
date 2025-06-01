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
    <style>
        :root {
            --primary-color: #2E7D32;
            --primary-light: #81C784;
            --primary-dark: #1B5E20;
            --secondary-color: #0288D1;
            --secondary-light: #B3E5FC;
            --secondary-dark: #01579B;
            --accent-color: #FFA000;
            --accent-light: #FFD54F;
            --dark-color: #263238;
            --light-color: #ECEFF1;
            --gray-color: #607D8B;
            --light-gray: #CFD8DC;
            --dark-blue:#2c3e50;
            --white: #FFFFFF;
            --black: #212121;
            --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
            --message-sent: var(--primary-light);
            --message-received: var(--white);
            --message-time: var(--gray-color);
        }

        body {
            font-family: 'Quicksand', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            height: 100vh;
            display: flex;
        }

        .contacts {
            width: 320px;
            background: white;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .contact-header {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            background: white;
        }

        .contact-header h2 {
            font-size: 1.25rem;
            color: var(--text-color);
            margin: 0;
        }

        .btn-back {
            color: var(--light-color);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .btn-back:hover {
            color: var(--primary-color);
        }

        #contactSearch {
            border: 1px solid var(--gray-color);
            border-radius: 1.5rem;
            padding: 0.75rem 1rem;
            margin: .5rem;
            width: calc(100% - 2rem);
            transition: all 0.2s;
        }

        #contactSearch:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            outline: none;
        }

        #contactList {
            flex: 1;
            overflow-y: auto;
            padding: 0;
            margin: 0;
        }

        .contact {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            cursor: pointer;
            transition: background-color 0.2s;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .contact:hover {
            background-color: var(--hover-color);
        }

        .contact.active {
            background-color: var(--hover-color);
        }

        .contact img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
        }

        .contact-info {
            flex: 1;
            min-width: 0;
        }

        .contact-info .fw-semibold {
            color: var(--text-color);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
        }

        .contact-info .text-muted {
            font-size: 0.85rem;
            color: var(--message-time);
        }

        .text-ellipsis {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 150px;
            display: inline-block;
        }

        .last-msg-time {
            font-size: 0.8rem;
            color: var(--message-time);
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: white;
        }

        .chat-header {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .chat-header .fw-semibold {
            color: var(--text-color);
            margin: 0;
            font-size: 1rem;
        }

        .chat-header .text-muted {
            font-size: 0.85rem;
            color: var(--message-time);
        }

        .chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8f9fc;
            position: relative;
            height: calc(100vh - 60px); /* Adjust based on header height */
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 80px; /* Add space for input area */
            height: calc(100% - 80px); /* Subtract input area height */
        }

        .message-container {
            display: flex;
            margin-bottom: 0.5rem;
        }

        .message-container.sent {
            justify-content: flex-end;
        }

        .message-container.received {
            justify-content: flex-start;
        }

        .msg-bubble {
            max-width: 70%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            position: relative;
        }

        .msg-sent {
            background-color: var(--message-sent);
            color: var(--dark-color);
            border-bottom-right-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .msg-received {
            background-color: var(--message-received);
            color: var(--dark-color);
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .message-text {
            font-size: 0.95rem;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .message-time {
            font-size: 0.75rem;
            color: var(--message-time);
            margin-top: 0.25rem;
            text-align: right;
        }

        #chatInputWrapper {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            background: white;
            border-top: 1px solid var(--border-color);
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
            z-index: 10;
        }

        #chatInput {
            flex: 1;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 0.75rem 1rem;
            resize: none;
            max-height: 120px;
            min-height: 40px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: all 0.2s;
            overflow-y: hidden; /* Hide scrollbar */
            line-height: 1.4;
        }

        #chatInput::-webkit-scrollbar {
            display: none; /* Hide scrollbar for Chrome, Safari and Opera */
        }

        #chatInput {
            -ms-overflow-style: none;  /* Hide scrollbar for IE and Edge */
            scrollbar-width: none;  /* Hide scrollbar for Firefox */
        }

        #sendChatBtn {
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        #sendChatBtn:hover {
            background: var(--accent-color);
        }

        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--message-time);
        }

        .no-chat-selected i {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .contacts {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                z-index: 1000;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .contacts.show {
                transform: translateX(0);
            }

            .chat-container {
                width: 100%;
            }
        }
    </style>
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
                        <?= htmlspecialchars(date('H:i', strtotime($contact['last_msg']))) ?>
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
    const date = new Date(timestamp);
  
    const timeOptions = {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    };
    
    const dateOptions = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    };

    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    const messageDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const todayDate = new Date(today.getFullYear(), today.getMonth(), today.getDate());
    const yesterdayDate = new Date(yesterday.getFullYear(), yesterday.getMonth(), yesterday.getDate());

    if (messageDate.getTime() === todayDate.getTime()) {
        return new Intl.DateTimeFormat('lv-LV', timeOptions).format(date);
    } else if (messageDate.getTime() === yesterdayDate.getTime()) {
        return 'Vakar ' + new Intl.DateTimeFormat('lv-LV', timeOptions).format(date);
    } else {
        return new Intl.DateTimeFormat('lv-LV', dateOptions).format(date);
    }
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
