<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Notification</title>
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

        .ip-address {
            font-family: monospace;
            background: rgba(0, 0, 0, 0.2);
            padding: 0.5rem;
            border-radius: 4px;
            margin: 1rem 0;
            display: inline-block;
            color: var(--warning);
        }

        .timer {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--light-accent);
            margin: 1.5rem 0;
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

        .btn:disabled {
            background: #5a5a5a;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
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
        <div class="icon">⚠️</div>
        <h1>Request Limit Exceeded</h1>
        <div class="message">
            We detected multiple failed attempts from your IP:
        </div>
        <div class="ip-address">{{ request()->header('cf-connecting-ip') ?? request()->ip() }}</div>
        <div class="message">
            Please wait <span id="countdown">10:00</span> before trying again.
        </div>
        <button class="btn" id="retryBtn" disabled>Try Again</button>
        <div class="footer">
            If you believe this is an error, please contact support
        </div>
    </div>

    <script>
        // Persistent timer using localStorage
        const STORAGE_KEY = 'rateLimitTimer';
        const WAIT_TIME = 300; // 3 minutes in seconds

        // Get elements
        const countdownEl = document.getElementById('countdown');
        const retryBtn = document.getElementById('retryBtn');

        // Check if timer exists in storage
        let storedTime = localStorage.getItem(STORAGE_KEY);
        let endTime;

        if (storedTime) {
            endTime = parseInt(storedTime);
            // Check if timer has already expired
            if (Date.now() >= endTime) {
                localStorage.removeItem(STORAGE_KEY);
                enableRetry();
            } else {
                startCountdown();
            }
        } else {
            // Set new timer
            endTime = Date.now() + (WAIT_TIME * 1000);
            localStorage.setItem(STORAGE_KEY, endTime.toString());
            startCountdown();
        }

        function startCountdown() {
            const updateTimer = () => {
                const now = Date.now();
                const remaining = Math.max(0, endTime - now);

                if (remaining <= 0) {
                    clearInterval(timer);
                    localStorage.removeItem(STORAGE_KEY);
                    enableRetry();
                    return;
                }

                const minutes = Math.floor((remaining / 1000) / 60);
                let seconds = Math.floor((remaining / 1000) % 60);
                seconds = seconds < 10 ? '0' + seconds : seconds;
                countdownEl.textContent = `${minutes}:${seconds}`;
            };

            updateTimer(); // Initial call
            const timer = setInterval(updateTimer, 1000);
        }

        function enableRetry() {
            countdownEl.textContent = "0:00";
            retryBtn.disabled = false;
            countdownEl.parentElement.textContent = "You may now try again.";
        }

        retryBtn.addEventListener('click', () => {
            // Here you would typically reload the page or retry the request
            window.location.reload();
        });
    </script>
</body>
</html>
