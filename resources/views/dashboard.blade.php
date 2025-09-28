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
            --card-bg: #ffffff;
            --text-dark: #1f2937;
            --text-light: #6b7280;
            --border: #e5e7eb;
        }

        body {
            background: white;
            color: var(--text-dark);
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            min-height: 100vh;
        }

        .header {
            text-align: center;
            margin-bottom: 2rem;
            padding: 2rem 0;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 1rem;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('https://images.unsplash.com/photo-1550684376-efcbd6e3f031?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.2;
        }

        .header-content {
            position: relative;
            z-index: 2;
        }

        .success-icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .success-icon i {
            font-size: 1.5rem;
            color: white;
        }

        h1 {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1rem;
        }

        .user-greeting {
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.75rem 1.25rem;
            border-radius: 2rem;
            display: inline-block;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 1rem;
        }

        .download-app-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
            padding: 0.75rem 1.5rem;
            border-radius: 2rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            backdrop-filter: blur(10px);
        }

        .download-app-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.8);
        }

        .dashboard-section {
            background: var(--card-bg);
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border);
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .section-title i {
            font-size: 1.25rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .stat-card {
            background: #f8fafc;
            border-radius: 0.75rem;
            padding: 1rem;
            text-align: center;
            border: 1px solid var(--border);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 1rem;
        }

        .stat-icon.primary {
            background: var(--primary-light);
            color: white;
        }

        .stat-icon.success {
            background: #10b981;
            color: white;
        }

        .stat-number {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-light);
            font-weight: 500;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }

        .action-card {
            background: var(--card-bg);
            border-radius: 0.75rem;
            padding: 1.25rem;
            text-align: center;
            text-decoration: none;
            color: var(--text-dark);
            border: 1px solid var(--border);
            transition: all 0.3s;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border-color: var(--primary);
        }

        .action-icon {
            font-size: 1.5rem;
            color: var(--primary);
            margin-bottom: 0.75rem;
        }

        .action-title {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .action-description {
            font-size: 0.75rem;
            color: var(--text-light);
        }

        .app-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 1rem;
            padding: 1.5rem;
            color: white;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .app-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }

        .app-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin: 1rem 0;
        }

        .app-card {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: 1.25rem;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .app-card:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }

        .app-icon {
            font-size: 2rem;
            margin-bottom: 0.75rem;
        }

        .app-name {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }

        .app-description {
            font-size: 0.8rem;
            opacity: 0.9;
            margin-bottom: 0.75rem;
        }

        .app-badge {
            background: white;
            color: var(--primary);
            padding: 0.4rem 0.8rem;
            border-radius: 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            .subtitle {
                font-size: 1rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header Section -->
        @if ($browser->isMobile())
           <div class="header fade-in">
            <div class="header-content">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Hello {{ Auth::user()->name }} Welcome to Venusnap</h1>
                <p class="subtitle">Your creative dashboard is ready</p>
                <a href="https://play.google.com/store/apps/details?id=com.venusnap.app"
                class="download-app-btn"
                target="_blank">
                    <i class="fab fa-google-play"></i>
                    Download App
                </a>
            </div>
        </div>
        @else
        <div class="header fade-in">
            <div class="header-content">
                <div class="success-icon">
                    <i class="fas fa-check"></i>
                </div>
                <h1>Welcome to Venusnap! 🎉</h1>
                <p class="subtitle">Your creative dashboard is ready</p>
                <div class="user-greeting">
                    Hello <strong>{{ Auth::user()->name }}</strong>! Start your creative journey
                </div>
                <a href="https://play.google.com/store/apps/details?id=com.venusnap.app"
                class="download-app-btn"
                target="_blank">
                    <i class="fab fa-google-play"></i>
                    Download Venusnap App to Get Started
                </a>
            </div>
        </div>
        @endif

        <!-- Your Stats -->
        <div class="dashboard-section fade-in">
            <h2 class="section-title">
                <i class="fas fa-user"></i>
                Your Profile
            </h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon primary">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stat-number">{{ Auth::user()->points }}</div>
                    <div class="stat-label">Your Points</div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon success">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <div class="stat-number">{{ Auth::user()->albums->count() }}</div>
                    <div class="stat-label">Your Albums</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="dashboard-section fade-in">
            <h2 class="section-title">
                <i class="fas fa-bolt"></i>
                Quick Actions
            </h2>
            <div class="quick-actions">
                <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <div class="action-title">Create Album</div>
                    <div class="action-description">Start your first creative collection</div>
                </a>
                <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-compass"></i>
                    </div>
                    <div class="action-title">Explore</div>
                    <div class="action-description">Discover amazing creations</div>
                </a>
            </div>
        </div>

        <!-- Mobile App Section -->
        <div class="app-section fade-in">
            <h2 class="app-title">Take Venusnap With You</h2>
            <p class="subtitle">Create on the go with our mobile app</p>

            <div class="app-cards">
                <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="app-card">
                    <i class="fab fa-google-play app-icon"></i>
                    <div class="app-name">Android App</div>
                    <div class="app-description">Available on Google Play Store</div>
                    <div class="app-badge">Download Now</div>
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add animation delay to cards
            const cards = document.querySelectorAll('.fade-in');
            cards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
            });
        });
    </script>
</body>
</html>
