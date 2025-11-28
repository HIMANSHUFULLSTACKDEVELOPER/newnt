<!-- messages.php -->
<?php
require_once '../db.php';
checkRole(['student']);

$student_id = $_SESSION['user_id'];
$student = $conn->query("SELECT * FROM students WHERE id = $student_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - Student Portal</title>
    <link rel="icon" href="../Nit_logo.png" type="image/svg+xml" />
    <link rel="stylesheet" href="messages-style.css">
<style>
        /* messages-style.css */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.navbar {
    background: rgba(26, 31, 58, 0.95);
    backdrop-filter: blur(20px);
    padding: 20px 40px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
}

.navbar h1 {
    color: white;
    font-size: 24px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn {
    padding: 10px 20px;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-block;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
}

.back-btn {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    border: 1px solid rgba(255, 255, 255, 0.3);
    padding: 8px 15px;
    border-radius: 8px;
    cursor: pointer;
    display: none;
    align-items: center;
    gap: 5px;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
}

.back-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.chat-container {
    display: grid;
    grid-template-columns: 380px 1fr;
    height: calc(100vh - 84px);
    max-width: 1600px;
    margin: 20px auto;
    gap: 20px;
    padding: 0 20px;
}

.contacts-panel {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.contacts-header {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 25px;
    font-size: 20px;
    font-weight: 700;
}

.contacts-search {
    padding: 15px;
    border-bottom: 1px solid #e0e0e0;
}

.contacts-search input {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s;
}

.contacts-search input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.contacts-list {
    overflow-y: auto;
    height: calc(100vh - 240px);
}

.contacts-list::-webkit-scrollbar {
    width: 6px;
}

.contacts-list::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.contacts-list::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 3px;
}

.contact-item {
    padding: 18px 20px;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 15px;
}

.contact-item:hover {
    background: rgba(102, 126, 234, 0.05);
}

.contact-item.active {
    background: rgba(102, 126, 234, 0.1);
    border-left: 4px solid #667eea;
}

.contact-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #28a745, #20c997);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 20px;
    font-weight: 700;
    flex-shrink: 0;
}

.contact-info {
    flex: 1;
    min-width: 0;
}

.contact-name {
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.contact-time {
    font-size: 11px;
    color: #999;
}

.contact-last-message {
    font-size: 13px;
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.unread-badge {
    background: #ff6b6b;
    color: white;
    border-radius: 50%;
    width: 22px;
    height: 22px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
}

.chat-panel {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
    display: flex;
    flex-direction: column;
}

.chat-header {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 20px 30px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chat-header-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 30px;
    background: linear-gradient(to bottom, #f8f9fa 0%, #ffffff 100%);
}

.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #28a745;
    border-radius: 3px;
}

.message {
    display: flex;
    margin-bottom: 20px;
    animation: messageSlide 0.3s ease;
}

@keyframes messageSlide {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message.sent {
    justify-content: flex-end;
}

.message-content {
    max-width: 60%;
    padding: 12px 18px;
    border-radius: 15px;
    font-size: 14px;
    line-height: 1.6;
    word-wrap: break-word;
}

.message.received .message-content {
    background: rgba(0, 0, 0, 0.05);
    color: #333;
    border-bottom-left-radius: 5px;
}

.message.sent .message-content {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border-bottom-right-radius: 5px;
}

.message-time {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 5px;
    text-align: right;
}

.chat-input-container {
    padding: 20px 30px;
    background: white;
    border-top: 1px solid #e0e0e0;
    display: flex;
    gap: 15px;
    align-items: center;
}

.chat-input {
    flex: 1;
    border: 1px solid #e0e0e0;
    border-radius: 25px;
    padding: 12px 20px;
    font-size: 14px;
    outline: none;
    transition: all 0.3s;
}

.chat-input:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
}

.send-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    cursor: pointer;
    font-size: 20px;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.send-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
}

.send-btn:active {
    transform: scale(0.95);
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100%;
    color: #999;
    font-size: 18px;
}

.empty-state-icon {
    font-size: 80px;
    margin-bottom: 20px;
}

/* ============================================ */
/* TABLET STYLES (≤1024px) */
/* ============================================ */
@media (max-width: 1024px) {
    .chat-container {
        grid-template-columns: 320px 1fr;
        gap: 15px;
        padding: 0 15px;
        margin: 15px auto;
    }

    .navbar {
        padding: 15px 20px;
    }

    .navbar h1 {
        font-size: 20px;
    }

    .contacts-header {
        padding: 20px;
        font-size: 18px;
    }

    .chat-header {
        padding: 15px 20px;
    }

    .message-content {
        max-width: 70%;
    }
}

/* ============================================ */
/* MOBILE STYLES (≤768px) */
/* ============================================ */
@media (max-width: 768px) {
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .navbar {
        padding: 12px 15px;
    }

    .navbar h1 {
        font-size: 18px;
        gap: 8px;
    }

    .btn {
        padding: 8px 15px;
        font-size: 14px;
    }

    .chat-container {
        grid-template-columns: 1fr;
        height: calc(100vh - 70px);
        margin: 0;
        padding: 0;
        gap: 0;
    }

    /* Contacts Panel Mobile */
    .contacts-panel {
        border-radius: 0;
        height: 100%;
        box-shadow: none;
    }

    .contacts-panel.mobile-hidden {
        display: none;
    }

    .contacts-header {
        padding: 20px 15px;
        font-size: 20px;
        background: linear-gradient(135deg, #667eea, #764ba2);
    }

    .contacts-search {
        padding: 12px;
        background: white;
    }

    .contacts-search input {
        padding: 12px 15px;
        font-size: 15px;
        border-radius: 12px;
    }

    .contacts-list {
        height: calc(100vh - 200px);
        background: white;
    }

    .contact-item {
        padding: 15px;
        gap: 12px;
    }

    .contact-avatar {
        width: 48px;
        height: 48px;
        font-size: 19px;
    }

    .contact-name {
        font-size: 15px;
    }

    .contact-last-message {
        font-size: 13px;
    }

    /* Chat Panel Mobile */
    .chat-panel {
        border-radius: 0;
        height: 100%;
        box-shadow: none;
    }

    .chat-panel.mobile-hidden {
        display: none !important;
    }

    .back-btn {
        display: flex;
    }

    .chat-header {
        padding: 15px;
        background: linear-gradient(135deg, #28a745, #20c997);
    }

    .chat-header-info {
        gap: 12px;
        flex: 1;
    }

    .chat-header-info .contact-avatar {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    #chatHeaderName {
        font-size: 16px !important;
    }

    #chatHeaderSubtitle {
        font-size: 12px !important;
    }

    .chat-messages {
        padding: 15px;
    }

    .message {
        margin-bottom: 15px;
    }

    .message-content {
        max-width: 75%;
        padding: 10px 14px;
        font-size: 14px;
    }

    .message-time {
        font-size: 10px;
    }

    .chat-input-container {
        padding: 12px 15px;
        gap: 10px;
        background: white;
    }

    .chat-input {
        padding: 11px 16px;
        font-size: 15px;
        border-radius: 22px;
    }

    .send-btn {
        width: 46px;
        height: 46px;
        font-size: 18px;
    }

    .empty-state {
        font-size: 16px;
    }

    .empty-state-icon {
        font-size: 64px;
        margin-bottom: 15px;
    }
}

/* ============================================ */
/* SMALL MOBILE STYLES (≤480px) */
/* ============================================ */
@media (max-width: 480px) {
    .navbar h1 {
        font-size: 16px;
    }

    .btn {
        padding: 6px 12px;
        font-size: 13px;
    }

    .contacts-header {
        padding: 16px 15px;
        font-size: 18px;
    }

    .contacts-search {
        padding: 10px;
    }

    .contact-item {
        padding: 12px;
    }

    .contact-avatar {
        width: 42px;
        height: 42px;
        font-size: 17px;
    }

    .contact-name {
        font-size: 14px;
    }

    .contact-last-message {
        font-size: 12px;
    }

    .contact-time {
        font-size: 10px;
    }

    .unread-badge {
        width: 20px;
        height: 20px;
        font-size: 10px;
    }

    .back-btn {
        padding: 6px 12px;
        font-size: 13px;
    }

    .chat-header {
        padding: 12px;
    }

    .chat-messages {
        padding: 12px;
    }

    .message-content {
        max-width: 80%;
        padding: 9px 12px;
        font-size: 13px;
    }

    .chat-input-container {
        padding: 10px 12px;
    }

    .chat-input {
        padding: 10px 14px;
        font-size: 14px;
    }

    .send-btn {
        width: 42px;
        height: 42px;
        font-size: 16px;
    }
}

/* ============================================ */
/* LANDSCAPE MOBILE (≤768px landscape) */
/* ============================================ */
@media (max-width: 768px) and (orientation: landscape) {
    .contacts-list {
        height: calc(100vh - 160px);
    }

    .chat-messages {
        padding: 10px 15px;
    }

    .message {
        margin-bottom: 10px;
    }

    .chat-input-container {
        padding: 8px 15px;
    }
}

/* ============================================ */
/* ANIMATIONS */
/* ============================================ */
@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.contacts-panel,
.chat-panel {
    animation: fadeIn 0.3s ease;
}
    </style>
</head>
<body>
    <nav class="navbar">
        <h1>💬 Messages</h1>
        <div>
            <a href="index.php" class="btn btn-primary">🏠 Dashboard</a>
        </div>
    </nav>

    <div class="chat-container">
        <!-- Contacts Panel -->
        <div class="contacts-panel" id="contactsPanel">
            <div class="contacts-header">
                👨‍🏫 Teachers
            </div>
            <div class="contacts-search">
                <input type="text" id="searchContacts" placeholder="🔍 Search teachers...">
            </div>
            <div class="contacts-list" id="contactsList">
                <div class="empty-state">
                    <div class="empty-state-icon">📭</div>
                    <p>Loading contacts...</p>
                </div>
            </div>
        </div>

        <!-- Chat Panel -->
        <div class="chat-panel" id="chatPanel">
            <div id="emptyChatState" class="empty-state">
                <div class="empty-state-icon">💬</div>
                <p>Select a teacher to start messaging</p>
            </div>
            
            <div id="activeChatContainer" style="display: none; height: 100%; display: flex; flex-direction: column;">
                <div class="chat-header">
                    <button class="back-btn" id="backBtn" onclick="goBackToContacts()">← Back</button>
                    <div class="chat-header-info">
                        <div class="contact-avatar" id="chatHeaderAvatar">T</div>
                        <div>
                            <div style="font-size: 18px; font-weight: 700;" id="chatHeaderName">Teacher Name</div>
                            <div style="font-size: 13px; opacity: 0.9;" id="chatHeaderSubtitle">Department</div>
                        </div>
                    </div>
                </div>
                <div class="chat-messages" id="chatMessages"></div>
                <div class="chat-input-container">
                    <input type="text" class="chat-input" id="messageInput" placeholder="Type your message...">
                    <button class="send-btn" id="sendBtn">➤</button>
                </div>
            </div>
        </div>
    </div>

<script>
    // messages-script.js

let currentContact = null;
let contacts = [];
let messageCheckInterval = null;

// Load contacts on page load
loadContacts();

// Search functionality
document.getElementById('searchContacts').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const contactItems = document.querySelectorAll('.contact-item');
    
    contactItems.forEach(item => {
        const name = item.querySelector('.contact-name').textContent.toLowerCase();
        item.style.display = name.includes(searchTerm) ? 'flex' : 'none';
    });
});

// Send message on Enter key
document.getElementById('messageInput').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        sendMessage();
    }
});

document.getElementById('sendBtn').addEventListener('click', sendMessage);

// Go back to contacts list (Mobile)
function goBackToContacts() {
    if (window.innerWidth <= 768) {
        document.getElementById('contactsPanel').classList.remove('mobile-hidden');
        document.getElementById('chatPanel').classList.add('mobile-hidden');
        
        // Clear active selection
        document.querySelectorAll('.contact-item').forEach(item => {
            item.classList.remove('active');
        });
        
        // Stop polling messages
        if (messageCheckInterval) {
            clearInterval(messageCheckInterval);
            messageCheckInterval = null;
        }
        
        // Reset chat state
        currentContact = null;
        document.getElementById('emptyChatState').style.display = 'flex';
        document.getElementById('activeChatContainer').style.display = 'none';
    }
}

// Load contacts from server
function loadContacts() {
    fetch('../chat_handler.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_contacts'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            contacts = data.contacts;
            renderContacts(data.contacts);
        }
    })
    .catch(error => console.error('Error loading contacts:', error));
}

// Render contacts list
function renderContacts(contactsList) {
    const container = document.getElementById('contactsList');
    
    if (contactsList.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📭</div>
                <p>No teachers found</p>
            </div>
        `;
        return;
    }

    container.innerHTML = contactsList.map(contact => `
        <div class="contact-item" 
             data-id="${contact.id}" 
             data-type="${contact.type}" 
             onclick="selectContact(${contact.id}, '${contact.type}', '${escapeHtml(contact.name)}', '${escapeHtml(contact.subtitle)}')">
            <div class="contact-avatar">${contact.name.charAt(0).toUpperCase()}</div>
            <div class="contact-info">
                <div class="contact-name">
                    <span>${escapeHtml(contact.name)}</span>
                    ${contact.unread_count > 0 ? `<span class="unread-badge">${contact.unread_count}</span>` : ''}
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div class="contact-last-message">${escapeHtml(contact.last_message)}</div>
                    ${contact.last_message_time ? `<div class="contact-time">${contact.last_message_time}</div>` : ''}
                </div>
            </div>
        </div>
    `).join('');
}

// Select a contact and open chat
function selectContact(id, type, name, subtitle) {
    currentContact = { id, type, name, subtitle };
    
    // Mobile view switching
    if (window.innerWidth <= 768) {
        document.getElementById('contactsPanel').classList.add('mobile-hidden');
        document.getElementById('chatPanel').classList.remove('mobile-hidden');
    }
    
    // Update UI - Remove active class from all contacts
    document.querySelectorAll('.contact-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to selected contact
    const selectedItem = document.querySelector(`[data-id="${id}"][data-type="${type}"]`);
    if (selectedItem) {
        selectedItem.classList.add('active');
    }
    
    // Show chat container
    document.getElementById('emptyChatState').style.display = 'none';
    document.getElementById('activeChatContainer').style.display = 'flex';
    
    // Update chat header
    document.getElementById('chatHeaderAvatar').textContent = name.charAt(0).toUpperCase();
    document.getElementById('chatHeaderName').textContent = name;
    document.getElementById('chatHeaderSubtitle').textContent = subtitle;
    
    // Load messages
    loadMessages(id, type);
    markAsRead(id, type);
    
    // Start polling for new messages every 3 seconds
    if (messageCheckInterval) {
        clearInterval(messageCheckInterval);
    }
    messageCheckInterval = setInterval(() => {
        if (currentContact) {
            loadMessages(currentContact.id, currentContact.type);
        }
    }, 3000);
}

// Load messages for selected contact
function loadMessages(contactId, contactType) {
    const formData = new FormData();
    formData.append('action', 'get_messages');
    formData.append('contact_id', contactId);
    formData.append('contact_type', contactType);

    fetch('../chat_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            renderMessages(data.messages);
        }
    })
    .catch(error => console.error('Error loading messages:', error));
}

// Render messages in chat
function renderMessages(messages) {
    const container = document.getElementById('chatMessages');
    const wasScrolledToBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;
    
    if (messages.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">💬</div>
                <p>No messages yet. Start the conversation!</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = messages.map(msg => `
        <div class="message ${msg.is_sent ? 'sent' : 'received'}">
            <div class="message-content">
                ${escapeHtml(msg.message)}
                <div class="message-time">${msg.time}</div>
            </div>
        </div>
    `).join('');
    
    // Auto-scroll to bottom if user was already at bottom or if new messages
    if (wasScrolledToBottom || messages.length > 0) {
        container.scrollTop = container.scrollHeight;
    }
}

// Send a message
function sendMessage() {
    const input = document.getElementById('messageInput');
    const message = input.value.trim();
    
    if (!message || !currentContact) return;
    
    const formData = new FormData();
    formData.append('action', 'send_message');
    formData.append('receiver_id', currentContact.id);
    formData.append('receiver_type', currentContact.type);
    formData.append('message', message);

    fetch('../chat_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            input.value = '';
            loadMessages(currentContact.id, currentContact.type);
            loadContacts(); // Refresh contact list to update last message
        } else {
            alert('Failed to send message. Please try again.');
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Failed to send message. Please try again.');
    });
}

// Mark messages as read
function markAsRead(contactId, contactType) {
    const formData = new FormData();
    formData.append('action', 'mark_read');
    formData.append('contact_id', contactId);
    formData.append('contact_type', contactType);

    fetch('../chat_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(() => {
        // Refresh contacts to update unread count
        loadContacts();
    })
    .catch(error => console.error('Error marking as read:', error));
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
}

// Handle window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        // Reset mobile classes on desktop
        document.getElementById('contactsPanel').classList.remove('mobile-hidden');
        document.getElementById('chatPanel').classList.remove('mobile-hidden');
    }
});

// Refresh contacts every 10 seconds
setInterval(loadContacts, 10000);

// Clean up on page unload
window.addEventListener('beforeunload', function() {
    if (messageCheckInterval) {
        clearInterval(messageCheckInterval);
    }
});
</script>
</body>
</html>