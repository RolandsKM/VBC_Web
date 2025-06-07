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
        e.title AS event_title,
        COUNT(CASE WHEN m.is_read = 0 AND m.to_user_id = ? THEN 1 END) as unread_count
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

$stmt->execute([$currentUserId, $currentUserId, $currentUserId, $currentUserId]);

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
    <!-- <link rel="stylesheet" href="user.css" /> -->
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
            --dark-blue: #2c3e50;
            --red-dark: #bb3f3f;
            --red: #ce4444;
            --white: #FFFFFF;
            --black: #212121;
            --box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            --transition: all 0.3s ease;
        }

        body {
            font-family: 'Quicksand', sans-serif;
            background-color: var(--light-color);
            margin: 0;
            height: 100vh;
            display: flex;
            padding: 5.5rem 0 0 0;
        }

        .contacts {
            width: 320px;
            background: var(--white);
            border-right: 1px solid var(--light-gray);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 5.5rem);
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .contact-header {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
            background: var(--white);
        }

        .contact-header h2 {
            font-size: 1.25rem;
            color: var(--dark-blue);
            margin: 0;
            font-weight: 600;
        }

        .btn-back {
            color: var(--gray-color);
            text-decoration: none;
            font-weight: 500;
            transition: var(--transition);
        }

        .btn-back:hover {
            color: var(--dark-blue);
        }

        .input-group {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            width: 100%;
        }

        .input-group-text {
            display: flex;
            align-items: center;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: var(--message-time);
            text-align: center;
            white-space: nowrap;
            background-color: var(--light-gray);
            border: 1px solid var(--border-color);
        }

        .input-group .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            min-width: 0;
            margin-bottom: 0;
        }

        .input-group .form-control:focus {
            z-index: 3;
        }

        .input-group > .form-control:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .input-group > .input-group-text:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .input-group > .form-control:not(:first-child) {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .input-group > .input-group-text:not(:last-child) {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        #contactList {
            flex: 1;
            overflow-y: auto;
            padding: 0;
            margin: 0;
        }

        .contact {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .contact:hover {
            background-color: var(--light-color);
        }

        .contact.active {
            background-color: var(--light-color);
        }

        .contact img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light-gray);
        }

        .contact-info {
            flex: 1;
            min-width: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .contact-info .fw-semibold {
            color: var(--dark-blue);
            margin-bottom: 0.25rem;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .contact-info .text-muted {
            font-size: 0.85rem;
            color: var(--gray-color);
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
            color: var(--gray-color);
        }

        .chat-container {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--white);
            box-shadow: var(--box-shadow);
        }

        .chat-header {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            gap: 1rem;
            background: var(--white);
        }

        .chat-header img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--light-gray);
        }

        .chat-header .fw-semibold {
            color: var(--dark-blue);
            margin: 0;
            font-size: 1rem;
            font-weight: 600;
        }

        .chat-header .text-muted {
            font-size: 0.85rem;
            color: var(--gray-color);
        }

        .chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--light-color);
            position: relative;
            height: calc(100vh - 5.5rem);
            overflow: hidden;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 3.8rem;
            height: calc(100% - 80px);
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
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .msg-sent {
            background-color: var(--primary-light);
            color: var(--dark-blue);
            border-bottom-right-radius: 4px;
        }

        .msg-received {
            background-color: var(--white);
            color: var(--dark-blue);
            border-bottom-left-radius: 4px;
        }

        .message-text {
            font-size: 0.95rem;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .message-time {
            font-size: 0.75rem;
            color: var(--gray-color);
            margin-top: 0.25rem;
            text-align: right;
        }

        #chatInputWrapper {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            background: var(--white);
            border-top: 1px solid var(--light-gray);
            display: flex;
            gap: 0.5rem;
            align-items: flex-end;
            height: 80px;
            z-index: 10;
        }

        #chatInput {
            flex: 1;
            border: 1px solid var(--light-gray);
            border-radius: 6px;
            padding: 0.75rem 1rem;
            resize: none;
            max-height: 120px;
            min-height: 40px;
            font-family: inherit;
            font-size: 0.95rem;
            transition: var(--transition);
            background-color: var(--light-color);
        }

        #chatInput:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
            outline: none;
        }

        #sendChatBtn {
            background: var(--primary-color);
            color: var(--white);
            border: none;
            border-radius: 6px;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
        }

        #sendChatBtn:hover {
            background: var(--primary-dark);
        }

        .no-chat-selected {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--gray-color);
        }

        .no-chat-selected i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-color);
        }

        @media (max-width: 768px) {
            .contacts {
               
                position: fixed;
                left: 0;
                top: 5.5rem;
                bottom: 0;
                z-index: 1000;
                transform: translateX(-100%);
                width: 100%;
                max-width: 320px;
            }

            .contacts.show {
                transform: translateX(0);
            }

            .chat-container {
                width: 100%;
            }

            .msg-bubble {
                max-width: 85%;
            }

            .chat {
                height: calc(100vh - 5.5rem);
            }

            .chat-messages {
                height: calc(100% - 80px);
            }

            .scroll-to-bottom {
                bottom: 6rem;
            }
        }

        @media (max-width: 480px) {
            .contact {
                padding: 0.75rem;
            }

            .contact img {
                width: 40px;
                height: 40px;
            }

            .msg-bubble {
                max-width: 90%;
            }

            #chatInputWrapper {
                padding: 0.75rem;
                height: 70px;
            }

            .chat-messages {
                padding: 0.75rem;
            }

            .scroll-to-bottom {
                bottom: 5.5rem;
            }
        }

        .date-separator {
            text-align: center;
            margin: 1rem 0;
            position: relative;
        }

        .date-separator span {
            background-color: var(--light-color);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            color: var(--gray-color);
            display: inline-block;
        }

        .date-separator::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background-color: var(--light-gray);
            z-index: -1;
        }

        .search-container {
            position: relative;
            width: 100%;
        }

        .search-container .form-control {
            padding-left: 2rem;
            padding-right: 1rem;
            border-radius: 50px;
            border: 1px solid var(--light-gray);
            transition: var(--transition);
            background-color: var(--light-color);
        }

        .search-container .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
            outline: none;
        }

        .search-icon {
            position: absolute;
            left: .8rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-color);
            pointer-events: none;
        }

        .unread-badge {
            background-color: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            min-width: 1.5rem;
            height: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 0.5rem;
        }

        .scroll-to-bottom {
            position: fixed;
            right: 1rem;
            bottom: 6rem;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--white);
            border: 1px solid var(--light-gray);
            color: var(--primary-color);
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            box-shadow: var(--box-shadow);
            z-index: 20;
        }

        .scroll-to-bottom:hover {
            background-color: var(--primary-color);
            color: var(--white);
            transform: translateY(-2px);
        }

        .scroll-to-bottom.show {
            display: flex;
        }

        .chat-sidebar {
            width: 300px;
            background: var(--white);
            border-right: 1px solid var(--light-gray);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }

        .chat-header {
            padding: 1rem;
            border-bottom: 1px solid var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--primary-color);
            font-size: 1.2rem;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 2px solid var(--light-gray);
        }

        .chat-toggle:hover {
            background: var(--light-gray);
            color: var(--primary-dark);
        }

        @media (max-width: 768px) {
            .contacts{
                display: none;
            }
            .chat-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 1000;
                transform: translateX(-100%);
                display: none;
            }

            .chat-sidebar.active {
                transform: translateX(0);
                display: flex;
            }

            .chat-toggle {
                display: flex;
            }
            .chat-header {
                position: sticky;
                top: 0;
                z-index: 100;
            }

            .chat-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
            }

            .chat-overlay.active {
                display: block;
            }

            .chat {
                margin-left: 0;
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
            <div class="search-container">
                <input type="search" class="form-control rounded-pill" id="contactSearch" placeholder="Meklēt kontaktu..." />
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>
        <ul class="list-group list-group-flush" id="contactList">
            <?php foreach ($contacts as $contact): ?>
                <li class="contact px-3 py-2 d-flex align-items-start gap-2" 
                    data-user-id="<?= $contact['ID_user'] ?>" 
                    data-event-id="<?= $contact['event_id'] ?>">
                    <img src="../functions/assets<?= htmlspecialchars($contact['profile_pic']) ?>" alt="pfp" width="40" height="40" class="rounded-circle mt-1" />
                    <div class="contact-info">
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($contact['username']) ?></div>
                            <div class="text-muted small">
                                Notikums: <span class="text-ellipsis"><?= htmlspecialchars($contact['event_title']) ?></span>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <?php if ($contact['unread_count'] > 0): ?>
                                <span class="unread-badge"><?= $contact['unread_count'] ?></span>
                            <?php endif; ?>
                            <div class="last-msg-time small text-muted text-nowrap ms-2">
                                <?= htmlspecialchars(date('H:i', strtotime($contact['last_msg']))) ?>
                            </div>
                        </div>
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
            <button class="chat-toggle">
                <i class="bi bi-chat-dots"></i>
            </button>
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
            <button class="scroll-to-bottom" id="scrollToBottom" title="Uz jaunākajām ziņām">
                <i class="fas fa-arrow-down"></i>
            </button>
            <div id="chatInputWrapper">
                <textarea id="chatInput" placeholder="Rakstiet ziņu..." rows="1"></textarea>
                <button id="sendChatBtn" title="Send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </main>

    <div class="chat-overlay"></div>

<script>
let currentUserId = <?= json_encode($currentUserId) ?>;
let currentChatId = null;
let currentEventId = null;

// Add chat toggle functionality
document.addEventListener('DOMContentLoaded', function() {
    const chatToggle = document.querySelector('.chat-toggle');
    const contacts = document.querySelector('.contacts');
    const chatOverlay = document.querySelector('.chat-overlay');
    const chatMessages = document.querySelector('.chat-messages');

    function toggleChat() {
        if (contacts.style.display === 'none' || contacts.style.display === '') {
            contacts.style.display = 'flex';
            chatOverlay.style.display = 'block';
        } else {
            contacts.style.display = 'none';
            chatOverlay.style.display = 'none';
        }
    }

    // Add click handler for contacts
    document.querySelectorAll('.contact').forEach(contact => {
        contact.addEventListener('click', function() {
            const userId = this.getAttribute('data-user-id');
            const eventId = this.getAttribute('data-event-id');
            
            // Load messages for this contact
            loadMessages(userId, eventId);
            
            // Update active state
            document.querySelectorAll('.contact').forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            
            // On mobile, close the contacts panel after selection
            if (window.innerWidth <= 768) {
                toggleChat();
            }
        });
    });

    function loadMessages(userId, eventId) {
        if (!userId || !eventId) return;
        
        chatMessages.innerHTML = '<div class="text-center p-3">Loading messages...</div>';
        
        fetch('../functions/chat_functions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=fetch_messages&user1=${currentUserId}&user2=${userId}&event_id=${eventId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                chatMessages.innerHTML = '';
                let currentDate = null;

                data.messages.forEach(msg => {
                    const msgDate = new Date(msg.sent_at);
                    const dateStr = formatMessageDate(msgDate);
                    
                    // Add date separator if date changes
                    if (currentDate !== dateStr) {
                        currentDate = dateStr;
                        chatMessages.innerHTML += `
                            <div class="date-separator">
                                <span>${dateStr}</span>
                            </div>
                        `;
                    }

                    const isUser = msg.from_user_id == currentUserId;
                    const msgClass = isUser ? 'sent' : 'received';
                    const bubbleClass = isUser ? 'msg-bubble msg-sent' : 'msg-bubble msg-received';
                    const timeString = formatMessageTime(msgDate);

                    chatMessages.innerHTML += `
                        <div class="message-container ${msgClass}">
                            <div class="${bubbleClass}">
                                <div class="message-text">${escapeHtml(msg.message)}</div>
                                <div class="message-time">${timeString}</div>
                            </div>
                        </div>
                    `;
                });
                chatMessages.scrollTop = chatMessages.scrollHeight;
            } else {
                chatMessages.innerHTML = '<div class="text-center p-3">Error loading messages</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            chatMessages.innerHTML = '<div class="text-center p-3">Error loading messages</div>';
        });
    }

    function formatMessageDate(date) {
        const today = new Date();
        const yesterday = new Date(today);
        yesterday.setDate(yesterday.getDate() - 1);

        if (date.toDateString() === today.toDateString()) {
            return 'Šodien';
        } else if (date.toDateString() === yesterday.toDateString()) {
            return 'Vakar';
        } else {
            return date.toLocaleDateString('lv-LV', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
    }

    function formatMessageTime(date) {
        return date.toLocaleTimeString('lv-LV', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    chatToggle.addEventListener('click', toggleChat);
    chatOverlay.addEventListener('click', toggleChat);

    // Close chat when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!contacts.contains(e.target) && 
                !chatToggle.contains(e.target) && 
                contacts.style.display === 'flex') {
                toggleChat();
            }
        }
    });
});

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
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

$(document).ready(function() {
    const chatMessages = $('#chatMessages');
    const scrollToBottom = $('#scrollToBottom');
    let lastScrollTop = 0;

    chatMessages.on('scroll', function() {
        const scrollTop = $(this).scrollTop();
        const scrollHeight = $(this)[0].scrollHeight;
        const clientHeight = $(this)[0].clientHeight;
        
        // Show button when not at bottom (with some threshold)
        if (scrollHeight - (scrollTop + clientHeight) > 100) {
            scrollToBottom.addClass('show');
        } else {
            scrollToBottom.removeClass('show');
        }

        // Update last scroll position
        lastScrollTop = scrollTop;
    });

    scrollToBottom.on('click', function() {
        chatMessages.animate({
            scrollTop: chatMessages[0].scrollHeight
        }, 300);
    });
});

</script>
<!--  -->

</body>
</html>
