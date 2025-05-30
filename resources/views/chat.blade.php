<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Venusnap Live Chat</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: #f9f9f9;
        }
    </style>
</head>
<body>
    <div id="brevo-chat-container" style="width: 100%; height: 100vh;"></div>

    <script>
        (function(d, w, c) {
            w.BrevoConversationsID = '683948ffb662e85b0a0a95a4'; // Your Brevo widget ID
            w[c] = w[c] || function() {
                (w[c].q = w[c].q || []).push(arguments);
            };
            var s = d.createElement('script');
            s.async = true;
            s.src = 'https://conversations-widget.brevo.com/brevo-conversations.js';
            s.crossOrigin = 'anonymous';
            d.head.appendChild(s);
        })(document, window, 'BrevoConversations');
    </script>
</body>
</html>
