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
        }

        body {
            background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(30, 27, 75, 0.9)),
                        url('https://images.unsplash.com/photo-1550684376-efcbd6e3f031?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
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
            max-width: 500px;
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
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            animation: pulse 2s infinite;
            box-shadow: 0 10px 30px rgba(124, 58, 237, 0.4);
        }

        .success-icon i {
            font-size: 2rem;
            color: white;
        }

        h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            background: linear-gradient(to right, #fff, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .subtitle {
            font-size: 1.1rem;
            color: #cbd5e1;
            margin-bottom: 2rem;
            line-height: 1.5;
        }

        .download-section {
            margin: 2rem 0;
        }

        .download-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--accent);
        }

        .app-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1.5rem 0;
        }

        .app-card {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 1rem;
            padding: 1.5rem;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
        }

        .app-card:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        .app-icon {
            font-size: 2.5rem;
            color: var(--accent);
        }

        .app-name {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .app-description {
            font-size: 0.9rem;
            color: #94a3b8;
            text-align: center;
        }

        .app-badge {
            background: var(--primary);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 0.5rem;
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
            margin-top: 1rem;
            text-decoration: none;
            display: inline-block;
            width: 100%;
        }

        .continue-button:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(124, 58, 237, 0.3);
        }

        .user-greeting {
            font-size: 1.1rem;
            color: #e2e8f0;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 1rem;
            border-left: 4px solid var(--primary);
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
                font-size: 1.8rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .app-cards {
                grid-template-columns: 1fr;
            }

            .app-card {
                padding: 1.25rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="success-card floating">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>

            <h1>Welcome to Venusnap! 🎉</h1>
            <p class="subtitle">Your creative journey starts now</p>

            <div class="user-greeting">
                Hello <strong>{{ Auth::user()->name }}</strong>! Your account is ready.
            </div>

            <div class="download-section">
                <h2 class="download-title">Get the App</h2>
                <p class="subtitle">Take your creativity everywhere</p>

                <div class="app-cards">
                    <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="app-card">
                        <i class="fab fa-google-play app-icon"></i>
                        <div class="app-name">Android App</div>
                        <div class="app-description">Available on Google Play Store</div>
                        <div class="app-badge">Download</div>
                    </a>

                    <a href="#" class="app-card">
                        <i class="fab fa-apple app-icon"></i>
                        <div class="app-name">iOS App</div>
                        <div class="app-description">Coming soon to App Store</div>
                        <div class="app-badge">Notify Me</div>
                    </a>
                </div>
            </div>

            <a href="/dashboard" class="continue-button">
                Continue to Dashboard
            </a>
        </div>
    </div>

    <script>
        // Simple animation on load
        document.addEventListener('DOMContentLoaded', function() {
            // Add click tracking for app cards
            document.querySelectorAll('.app-card').forEach(card => {
                card.addEventListener('click', function() {
                    const appName = this.querySelector('.app-name').textContent;
                    console.log('App download clicked:', appName);
                    // You can add analytics tracking here
                });
            });
        });
    </script>
</body>
</html>
