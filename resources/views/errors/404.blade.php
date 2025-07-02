<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        :root {
            --deep-purple: #2a0845;
            --purple-accent: #7b2cbf;
            --light-accent: #c77dff;
            --text-light: #f8f9fa;
            --warning: #ff9e00;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--deep-purple);
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .notification-card {
            background: linear-gradient(145deg, #3a0c60, #2a0845);
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
            width: 90%;
            max-width: 500px;
            text-align: center;
            border: 1px solid var(--purple-accent);
            position: relative;
            overflow: hidden;
        }

        .notification-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--purple-accent), var(--light-accent));
        }

        h1 {
            color: var(--light-accent);
            margin-top: 0;
            font-weight: 600;
        }

        .icon {
            font-size: 4rem;
            margin-bottom: 1.5rem;
            color: var(--warning);
        }

        .message {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .btn {
            background: var(--purple-accent);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 50px;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn:hover {
            background: var(--light-accent);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(123, 44, 191, 0.3);
        }

        .footer {
            margin-top: 2rem;
            font-size: 0.8rem;
            color: rgba(255, 255, 255, 0.6);
        }
    </style>
</head>
<body>
    <div class="notification-card">
        <div class="icon">🚫</div>
        <h1>404 - Not Found</h1>
        <div class="message">
            You hit a wrong URL, snap!
        </div>
        <div class="message">
            The page you were looking for doesn't exist or was moved.
        </div>
        <a href="/" class="btn">Go Home</a>
        <div class="footer">
            If you think this is a mistake, please contact support.
        </div>
    </div>
</body>
</html>
