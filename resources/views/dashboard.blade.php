<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Venusnap! 🎉</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --primary-light: #8b5cf6;
            --accent: #d8b4fe;
            --success: #10b981;
        }

        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .confetti {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 1000;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .success-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 2rem;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .success-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--accent));
        }

        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: pulse 2s infinite;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.4);
        }

        .success-icon i {
            font-size: 3rem;
            color: white;
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 1.2rem;
            color: #cbd5e1;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .welcome-message {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            padding: 1.5rem;
            margin: 2rem 0;
            border-left: 4px solid var(--primary);
        }

        .welcome-message p {
            font-size: 1.1rem;
            color: #e2e8f0;
            line-height: 1.6;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }

        .feature-item {
            background: rgba(255, 255, 255, 0.05);
            padding: 1rem;
            border-radius: 1rem;
            transition: transform 0.3s;
        }

        .feature-item:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.1);
        }

        .feature-icon {
            font-size: 1.5rem;
            color: var(--accent);
            margin-bottom: 0.5rem;
        }

        .feature-text {
            font-size: 0.9rem;
            color: #cbd5e1;
        }

        .cta-section {
            margin: 2rem 0;
        }

        .cta-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .download-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 1.5rem;
        }

        .store-button {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            background: rgba(255, 255, 255, 0.1);
            padding: 1rem 1.5rem;
            border-radius: 1rem;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.2);
            min-width: 200px;
        }

        .store-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.3);
        }

        .store-icon {
            font-size: 2rem;
        }

        .store-text {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .store-label {
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .store-name {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .continue-button {
            background: var(--primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 2rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 1.5rem;
            text-decoration: none;
            display: inline-block;
        }

        .continue-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
        }

        .user-stats {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent);
        }

        .stat-label {
            font-size: 0.9rem;
            color: #94a3b8;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .success-card {
                padding: 2rem;
            }

            h1 {
                font-size: 2rem;
            }

            .subtitle {
                font-size: 1.1rem;
            }

            .download-buttons {
                flex-direction: column;
                align-items: center;
            }

            .store-button {
                width: 100%;
                max-width: 300px;
            }

            .user-stats {
                gap: 1.5rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <!-- Confetti Animation -->
    <div class="confetti" id="confetti"></div>

    <div class="container">
        <div class="success-card floating">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>

            <h1>Welcome to Venusnap! 🎉</h1>
            <p class="subtitle">Your creative journey starts now. We're thrilled to have you join our community of visual storytellers.</p>

            <div class="welcome-message">
                <p>Hello <strong id="userName">{{ Auth::user()->name }}</strong>! Your account has been successfully created. Get ready to share your unique perspective with the world and connect with fellow artists who appreciate your vision.</p>
            </div>

            <div class="user-stats">
                <div class="stat-item">
                    <div class="stat-number">67+</div>
                    <div class="stat-label">Active Creators</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label">Artworks Shared</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100+</div>
                    <div class="stat-label">Countries</div>
                </div>
            </div>

            <div class="features-grid">
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="feature-text">Create Albums</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <div class="feature-text">Get Feedback</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="feature-text">Join Community</div>
                </div>
                <div class="feature-item">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="feature-text">Track Growth</div>
                </div>
            </div>

            <div class="cta-section">
                <h2 class="cta-title">Download the App & Start Creating</h2>
                <p class="subtitle">Take Venusnap with you wherever inspiration strikes</p>

                <div class="download-buttons">
                    <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="store-button">
                        <i class="fab fa-google-play store-icon"></i>
                        <div class="store-text">
                            <span class="store-label">GET IT ON</span>
                            <span class="store-name">Google Play</span>
                        </div>
                    </a>
                    <a href="#" class="store-button">
                        <i class="fab fa-apple store-icon"></i>
                        <div class="store-text">
                            <span class="store-label">Download on the</span>
                            <span class="store-name">App Store</span>
                        </div>
                    </a>
                </div>

                <a href="/dashboard" class="continue-button">
                    Continue to Web Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        // Confetti animation
        function createConfetti() {
            const confettiContainer = document.getElementById('confetti');
            const colors = ['#7c3aed', '#8b5cf6', '#d8b4fe', '#10b981', '#f59e0b'];

            for (let i = 0; i < 100; i++) {
                const confetti = document.createElement('div');
                confetti.style.position = 'absolute';
                confetti.style.width = '10px';
                confetti.style.height = '10px';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.borderRadius = Math.random() > 0.5 ? '50%' : '0';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-10px';
                confetti.style.opacity = Math.random() * 0.5 + 0.5;
                confetti.style.transform = `rotate(${Math.random() * 360}deg)`;
                confetti.style.animation = `fall ${Math.random() * 3 + 2}s linear forwards`;

                confettiContainer.appendChild(confetti);

                // Remove confetti after animation
                setTimeout(() => {
                    confetti.remove();
                }, 5000);
            }

            // Add CSS for falling animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes fall {
                    to {
                        transform: translateY(100vh) rotate(${Math.random() * 360}deg);
                    }
                }
            `;
            document.head.appendChild(style);
        }

        // Create confetti on load and every 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            createConfetti();
            setInterval(createConfetti, 3000);

            // Get user name from URL or use default
            const urlParams = new URLSearchParams(window.location.search);
            const userName = urlParams.get('name') || 'Creator';
            document.getElementById('userName').textContent = userName;

            // Track successful registration
            fetch('/track-registration-success', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    user_id: urlParams.get('user_id') || 'unknown',
                    timestamp: new Date().toISOString()
                })
            });
        });
    </script>
</body>
</html>
