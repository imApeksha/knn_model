<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .chat-container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
        }
        .chat-box {
            border: 1px solid #ccc;
            padding: 10px;
            height: 300px;
            overflow-y: auto;
            margin-bottom: 20px;
        }
        .user-message, .bot-message {
            margin: 10px 0;
        }
        .user-message {
            text-align: right;
        }
        .bot-message {
            text-align: left;
        }
        .message {
            display: inline-block;
            padding: 10px;
            border-radius: 10px;
            max-width: 70%;
        }
        .user-message .message {
            background-color: #007bff;
            color: white;
        }
        .bot-message .message {
            background-color: #f1f1f1;
            color: black;
        }
        .chat-form {
            display: flex;
        }
        .chat-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px 0 0 10px;
        }
        .chat-form button {
            padding: 10px;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 0 10px 10px 0;
        }

        /* Spinner CSS */
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            display: inline-block;
        }

        /* Typing animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .typing {
            font-style: italic;
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-box" id="chat-box"></div>
        <form method="post" id="chat-form" class="chat-form">
            <input type="text" name="message" id="message" placeholder="Ask something...">
            <button type="submit">Send</button>
        </form>
    </div>

    <script>
        document.getElementById("chat-form").addEventListener("submit", function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            var chatBox = document.getElementById("chat-box");
            var userMessage = document.getElementById("message").value;

            // Display user message
            chatBox.innerHTML += <div class="user-message"><div class="message">${userMessage}</div></div>;

            // Display bot typing indicator
            chatBox.innerHTML += <div class="bot-message"><div class="message typing">Bot is typing... <div class="spinner"></div></div></div>;

            // Scroll to bottom
            chatBox.scrollTop = chatBox.scrollHeight;

            // Now make the fetch request
            fetch("chatbot.php", { method: "POST", body: formData })
                .then(response => response.text())
                .then(data => {
                    // Replace bot typing with actual bot response
                    var lastMessage = chatBox.querySelector(".bot-message .typing");
                    lastMessage.innerHTML = data;
                    chatBox.scrollTop = chatBox.scrollHeight; // Scroll to bottom
                });
        });
    </script>
    
</body>
</html>