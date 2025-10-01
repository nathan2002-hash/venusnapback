<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #7c3aed;
            --primary-dark: #6d28d9;
            --primary-light: #8b5cf6;
            --accent: #d8b4fe;
            --warning: #f59e0b;
            --text-light: #f8fafc;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(30, 27, 75, 0.9)),
                        url('https://images.unsplash.com/photo-1556761175-b413da4baf72?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            color: var(--text-light);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .error-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 3rem;
            width: 90%;
            max-width: 500px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .error-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(to right, var(--primary), var(--accent));
        }

        .error-icon {
            width: 100px;
            height: 100px;
            background: rgba(245, 158, 11, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            border: 4px solid rgba(245, 158, 11, 0.3);
            backdrop-filter: blur(10px);
        }

        .error-icon i {
            font-size: 3rem;
            color: var(--warning);
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
            background: linear-gradient(to right, #fff, var(--warning));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .error-code {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 1.5rem;
            opacity: 0.9;
        }

        .message {
            font-size: 1.2rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            color: #e2e8f0;
            opacity: 0.9;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
            margin: 2rem 0;
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            box-shadow: 0 4px 15px rgba(124, 58, 237, 0.3);
        }

        .btn:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(124, 58, 237, 0.4);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin: 2rem 0;
            text-align: center;
        }

        .stat-item {
            padding: 1rem;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--accent);
            margin-bottom: 0.25rem;
        }

        .stat-label {
            font-size: 0.8rem;
            opacity: 0.8;
            color: #cbd5e1;
        }

        .footer {
            margin-top: 2rem;
            font-size: 0.9rem;
            color: #94a3b8;
            opacity: 0.8;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            animation: fadeInUp 0.8s ease-out;
        }

        @media (max-width: 500px) {
            .error-card {
                padding: 2rem;
            }

            h1 {
                font-size: 2rem;
            }

            .error-icon {
                width: 80px;
                height: 80px;
            }

            .error-icon i {
                font-size: 2.5rem;
            }

            .action-buttons {
                flex-direction: column;
                align-items: center;
            }

            .btn {
                width: 100%;
                max-width: 250px;
                justify-content: center;
            }

            .stats {
                grid-template-columns: 1fr;
                gap: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="error-card fade-in">
        <div class="error-icon">
            <i class="fas fa-exclamation-triangle"></i>
        </div>

        <h1>Oops! Wrong Turn</h1>
        <div class="error-code">404 - Page Not Found</div>

        <div class="message">
            You hit a wrong URL, snap! The page you were looking for doesn't exist or was moved.
        </div>

        <div class="message">
            Don't worry, even the best explorers get lost sometimes. Let's get you back on track!
        </div>

        <div class="action-buttons">
            <a href="/" class="btn">
                <i class="fas fa-home"></i> Go Home
            </a>
            <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="btn btn-secondary">
                <i class="fab fa-google-play"></i> Get the App
            </a>
        </div>

        <div class="footer">
            If you think this is a mistake, please contact support.
            <br>
            <span style="font-size: 0.8rem; margin-top: 0.5rem; display: block;">
                &copy; 2025 Venusnap. All rights reserved.
            </span>
        </div>
    </div>

    <script>
        // Enhanced animations
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.fade-in');
            elements.forEach((element, index) => {
                element.style.opacity = 0;
                element.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    element.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                    element.style.opacity = 1;
                    element.style.transform = 'translateY(0)';
                }, 200 + (index * 150));
            });

            // Add hover effects to stats
            const stats = document.querySelectorAll('.stat-item');
            stats.forEach(stat => {
                stat.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.background = 'rgba(255, 255, 255, 0.08)';
                });
                stat.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.background = 'rgba(255, 255, 255, 0.05)';
                });
            });
        });
    </script>
</body>
</html>
