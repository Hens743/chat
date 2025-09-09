jQuery(document).ready(function($) {
    var $container = $('#sdg-chatbot-container');
    if ($container.length === 0) return;

    // --- Build the initial UI ---
    var ui = `
        <div id="sdg-chatbot">
            <div class="chatbot-header" 
                 style="background-color:${sdgChatbotData.header_bg_color};color:${sdgChatbotData.title_color};">
                ${sdgChatbotData.logo_url ? `<img class="chatbot-logo" src="${sdgChatbotData.logo_url}" alt="logo">` : ''}
                <span class="chatbot-title" style="font-size:${sdgChatbotData.title_font_size}px;">
                    ${sdgChatbotData.header_title}
                </span>
            </div>
            <div class="chatbot-messages">
                <div class="chatbot-message bot">${sdgChatbotData.welcome_message}</div>
            </div>
            <div class="chatbot-input">
                <input type="text" class="chatbot-text" placeholder="Type your message..." />
                <button class="chatbot-send">Send</button>
            </div>
        </div>
    `;
    $container.html(ui);

    var $messagesContainer = $container.find('.chatbot-messages');

    /**
     * Appends a text message to the chat window.
     * @param {string} text - The message content.
     * @param {string} who - 'user' or 'bot'.
     */
    function appendMessage(text, who) {
        var cls = who === 'user' ? 'user' : 'bot';
        // Use .html() instead of .text() to allow links from the backend.
        var messageHTML = $(`<div class="chatbot-message ${cls}"></div>`).html(text);
        $messagesContainer.append(messageHTML);
        $messagesContainer.scrollTop($messagesContainer[0].scrollHeight);
    }

    /**
     * Appends a set of interactive buttons to the chat window.
     * @param {Array} buttons - An array of button objects from the backend.
     */
    function appendButtons(buttons) {
        var $buttonContainer = $('<div class="chatbot-buttons-container"></div>');
        buttons.forEach(function(buttonInfo) {
            var $button = $('<button class="chatbot-button"></button>')
                .text(buttonInfo.label)
                .attr('data-action', buttonInfo.action);
            $buttonContainer.append($button);
        });
        $messagesContainer.append($buttonContainer);
        $messagesContainer.scrollTop($messagesContainer[0].scrollHeight);
    }

    /**
     * Sends a message to the backend via AJAX.
     * @param {string} msg - The message to send.
     */
    function sendMessage(msg) {
        $.ajax({
            url: sdgChatbotData.rest_url,
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', sdgChatbotData.nonce);
            },
            data: { message: msg },
            success: function(res) {
                var reply = (res && res.reply) ? res.reply : 'No response.';
                appendMessage(reply, 'bot');

                // Check for and display buttons from the response.
                if (res.buttons && Array.isArray(res.buttons) && res.buttons.length > 0) {
                    appendButtons(res.buttons);
                }
            },
            error: function() {
                appendMessage('Error: could not get response.', 'bot');
            }
        });
    }

    /**
     * Handles the user submitting the text input.
     */
    function handleTextInput() {
        var $input = $container.find('.chatbot-text');
        var msg = $input.val().trim();
        if (!msg) return;

        appendMessage(msg, 'user');
        $input.val('');
        sendMessage(msg);
    }

    // --- Event Listeners ---

    // For the "Send" button
    $container.on('click', '.chatbot-send', handleTextInput);

    // For pressing Enter in the input field
    $container.on('keydown', '.chatbot-text', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            handleTextInput();
        }
    });

    // For clicking the new interactive buttons
    $container.on('click', '.chatbot-button', function() {
        var $button = $(this);
        var action = $button.attr('data-action');
        var label = $button.text();

        // Show the user's choice in the chat
        appendMessage(label, 'user');
        
        // Disable all buttons in the same group
        $button.parent().find('.chatbot-button').prop('disabled', true).addClass('disabled');

        // Send the button's action to the backend
        sendMessage(action);
    });
});
