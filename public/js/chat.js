document.addEventListener('DOMContentLoaded', function() {
    const chatBtn = document.getElementById('aiChatBtn');
    const chatWindow = document.getElementById('aiChatWindow');
    const chatClose = document.getElementById('aiChatClose');
    const chatInput = document.getElementById('aiChatInput');
    const chatSend = document.getElementById('aiChatSend');
    const chatBody = document.getElementById('aiChatBody');
    const chatLoading = document.getElementById('aiChatLoading');
    
    // Base URL needed for API requests
    const baseUrl = window.APP_BASEURL || (window.location.origin + '/nnaco_sa/public');

    // Safety guard
    if (!chatBtn || !chatWindow) return;

    // Toggle Chat Window
    chatBtn.addEventListener('click', () => {
        chatWindow.classList.add('active');
        chatBtn.style.display = 'none';
        chatInput.focus();
    });

    chatClose.addEventListener('click', () => {
        chatWindow.classList.remove('active');
        setTimeout(() => {
            chatBtn.style.display = 'flex';
        }, 300);
    });

    // Send Message
    const sendMessage = async () => {
        const message = chatInput.value.trim();
        if (!message) return;

        // Add user message to UI
        appendMessage(message, 'user');
        chatInput.value = '';
        chatInput.disabled = true;
        chatSend.disabled = true;

        // Show loading
        chatLoading.style.display = 'block';
        scrollToBottom();

        try {
            const response = await fetch(`${baseUrl}/AIChat/ask`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ message: message })
            });

            const data = await response.json();
            
            chatLoading.style.display = 'none';
            
            if (data.status === 'success') {
                // Parse markdown-like syntax for bolding, inline code, and links
                let formattedResponse = data.response
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/`([^`]+)`/g, '<code style="background: rgba(125,125,125,0.15); padding: 2px 6px; border-radius: 6px; font-size: 0.88em; font-family: monospace;">$1</code>')
                    .replace(/\[([^\]]+)\]\(([^)]+)\)/g, (match, text, url) => {
                        const isInternal = url.startsWith('/') || url.startsWith(window.location.origin) || url.startsWith('#');
                        const targetAttr = isInternal ? '' : ' target="_blank" rel="noopener"';
                        return `<a href="${url}"${targetAttr}>${text}</a>`;
                    })
                    .replace(/(?:^|<br\s*\/?>)\s*[\*\-]\s+(.*?)(?=(?:<br\s*\/?>|$))/gi, '<div class="chat-list-bullet" style="margin-left: 6px; margin-top: 2px;">• $1</div>');
                appendMessage(formattedResponse, 'ai', true);
            } else {
                appendMessage('Oops: ' + data.message, 'ai');
            }
        } catch (error) {
            chatLoading.style.display = 'none';
            appendMessage('Maaf, terjadi kesalahan koneksi. Coba lagi nanti.', 'ai');
            console.error('AI Chat Error:', error);
        } finally {
            chatInput.disabled = false;
            chatSend.disabled = false;
            chatInput.focus();
        }
    };

    chatSend.addEventListener('click', sendMessage);

    chatInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    function appendMessage(text, sender, isHtml = false) {
        const bubble = document.createElement('div');
        bubble.className = `chat-message ${sender}`;
        
        if (isHtml) {
            bubble.innerHTML = text;
        } else {
            bubble.textContent = text;
        }
        
        // Insert before loading indicator
        chatBody.insertBefore(bubble, chatLoading);
        scrollToBottom();
    }

    function scrollToBottom() {
        chatBody.scrollTop = chatBody.scrollHeight;
    }
});
