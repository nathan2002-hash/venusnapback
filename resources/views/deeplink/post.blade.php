<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $post->album->name ?? 'Venusnap Post' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            width: 100%;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin: 20px 0;
        }

        .header {
            text-align: center;
            padding: 30px 20px;
            background: rgba(0, 0, 0, 0.2);
        }

        .logo {
            font-size: 36px;
            margin-bottom: 10px;
            color: #ffdd40;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .description {
            font-size: 18px;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
        }

        .content {
            display: flex;
            flex-direction: column;
            padding: 30px;
        }

        .post-preview {
            width: 100%;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .post-preview img {
            width: 100%;
            display: block;
            max-height: 400px;
            object-fit: cover;
        }

        .details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-card {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
        }

        .detail-card i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #ffdd40;
        }

        .detail-card h3 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .detail-card p {
            opacity: 0.8;
        }

        .download-section {
            text-align: center;
            padding: 20px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            margin: 20px 0;
        }

        .download-section h2 {
            font-size: 24px;
            margin-bottom: 15px;
            color: #ffdd40;
        }

        .download-section p {
            font-size: 18px;
            margin-bottom: 20px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        .app-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }

        .app-badge {
            display: inline-block;
            background: #fff;
            color: #333;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .app-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            background: #ffdd40;
        }

        .app-badge i {
            margin-right: 8px;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }

        .feature {
            text-align: center;
            padding: 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
        }

        .feature i {
            font-size: 32px;
            margin-bottom: 15px;
            color: #ffdd40;
        }

        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            font-size: 14px;
            opacity: 0.8;
        }

        @media (max-width: 600px) {
            .details, .features {
                grid-template-columns: 1fr;
            }

            h1 {
                font-size: 24px;
            }

            .description {
                font-size: 16px;
            }

            .app-badges {
                flex-direction: column;
                align-items: center;
            }

            .app-badge {
                width: 80%;
                text-align: center;
            }
        }

        .countdown {
            font-size: 16px;
            margin-top: 10px;
            color: #ffdd40;
        }

        .auto-open {
            margin-top: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">
                <i class="fas fa-camera"></i> Venusnap
            </div>
            <h1>Exclusive Content Inside</h1>
            <p class="description">You've been invited to view a private post from "{{ $post->album->name ?? 'a Venusnap album' }}"</p>
        </div>

        <div class="content">
            <div class="post-preview">
                <img src="{{ $thumbnailUrl ?? 'https://images.unsplash.com/photo-1554080353-321e452ccf19?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80' }}" alt="Post preview">
            </div>

            <div class="details">
                <div class="detail-card">
                    <i class="fas fa-images"></i>
                    <h3>High Quality Content</h3>
                    <p>Premium photos and videos</p>
                </div>
                <div class="detail-card">
                    <i class="fas fa-lock"></i>
                    <h3>Private Album</h3>
                    <p>Exclusive access for you</p>
                </div>
                <div class="detail-card">
                    <i class="fas fa-user-friends"></i>
                    <h3>Community</h3>
                    <p>Connect with other fans</p>
                </div>
            </div>

            <div class="download-section">
                <h2>Download Venusnap to View This Content</h2>
                <p>Join thousands of users enjoying exclusive content from creators worldwide</p>

                <div class="app-badges">
                    <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="app-badge">
                        <i class="fab fa-google-play"></i> Google Play
                    </a>
                    <a href="https://apps.apple.com/app/venusnap" class="app-badge">
                        <i class="fab fa-apple"></i> App Store
                    </a>
                </div>

                <p class="auto-open">We'll try to automatically open the app in <span class="countdown">5</span> seconds</p>
            </div>

            <div class="features">
                <div class="feature">
                    <i class="fas fa-rocket"></i>
                    <h3>Fast Streaming</h3>
                    <p>HD content without delays</p>
                </div>
                <div class="feature">
                    <i class="fas fa-comments"></i>
                    <h3>Direct Messaging</h3>
                    <p>Chat with creators</p>
                </div>
                <div class="feature">
                    <i class="fas fa-gem"></i>
                    <h3>Premium Content</h3>
                    <p>Exclusive photos & videos</p>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2023 Venusnap. All rights reserved.</p>
    </div>

    <script>
        // Deep link and fallback URL
        const deepLink = "venusnap://post/{{ $post->id }}";
        const fallbackUrl = "https://play.google.com/store/apps/details?id=com.venusnap.app";

        // Countdown timer
        let countdown = 5;
        const countdownElement = document.querySelector('.countdown');

        const countdownInterval = setInterval(() => {
            countdown--;
            countdownElement.textContent = countdown;

            if (countdown <= 0) {
                clearInterval(countdownInterval);
                window.location.href = fallbackUrl;
            }
        }, 1000);

        // Try to open the app immediately
        setTimeout(() => {
            window.location.href = deepLink;
        }, 500);

        // Fallback to app store if app isn't installed
        setTimeout(() => {
            window.location.href = fallbackUrl;
        }, 5500);
    </script>
</body>
</html>
