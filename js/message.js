// --- JavaScript for Live Message Updates ---

// DOM Elements & Global Variables
const messagesList = document.querySelector('.messages-list');
const currentUser = window.currentUserID;
const otherUser = window.otherUserID;
const otherUsername = window.otherUsername;

/**
 * Escapes HTML special characters to prevent XSS attacks.

 */
function escapeHtml(text) {
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Renders an array of message objects into the DOM.

 */
function renderMessages(messages) {
    messagesList.innerHTML = '';

    // Show placeholder if no messages
    if (messages.length === 0) {
        messagesList.innerHTML = "<div style='color:#b8c1ec; text-align:center;'>No messages yet.</div>";
        return;
    }

    // Render each message
    messages.forEach(row => {
        const isYou = row.senderID == currentUser;
        const bubbleClass = isYou ? "you" : "other";
        const sender = isYou ? "You" : otherUsername;

        const div = document.createElement('div');
        div.className = 'message-bubble ' + bubbleClass;
        div.innerHTML =
            `${escapeHtml(row.message)}<span class="message-meta">${sender} &middot; ${escapeHtml(row.date)}</span>`;

        messagesList.appendChild(div);
    });

    // Scroll to the latest message
    messagesList.scrollTop = messagesList.scrollHeight;
}

/**
 * Fetches messages from the server and renders them.
 */
function fetchMessages() {
    fetch('fetchMessages.php?user=' + otherUser)
        .then(res => res.json())
        .then(data => renderMessages(data));
}

// Initial fetch and polling for new messages every 2 seconds
fetchMessages();
setInterval(fetchMessages, 2000);