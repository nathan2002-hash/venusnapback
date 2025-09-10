<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $title = $post ? "Explore: " . ($post->album->name ?? 'Discover Content') : "Venusnap Explore";
        $ogImage = $thumbnailUrl ?? asset('images/explore-default.jpg');
    @endphp

    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta name="description" content="{{ $description }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        .container {
            max-width: 500px;
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
            padding: 30px 20px 20px;
            background: rgba(0, 0, 0, 0.2);
            position: relative;
        }

        .explore-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(45deg, #ff6b6b, #ff8e53);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #4ecdc4;
            margin: 0 auto 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .explore-title {
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
            color: #4ecdc4;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .post-title {
            font-size: 20px;
            margin-bottom: 10px;
            font-weight: 600;
            color: #ffdd40;
        }

        .explore-description {
            font-size: 16px;
            opacity: 0.9;
            max-width: 400px;
            margin: 0 auto 15px;
            line-height: 1.5;
        }

        .sharing-message {
            font-size: 16px;
            opacity: 0.9;
            font-style: italic;
            padding: 10px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            margin-top: 15px;
        }

        .content {
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 20px 0;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px 25px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .action-btn i {
            margin-right: 10px;
            font-size: 20px;
        }

        .explore-btn {
            background: #4ecdc4;
            color: #fff;
        }

        .explore-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            background: #45b7aa;
        }

        .download-btn {
            background: #fff;
            color: #333;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            background: #ffdd40;
        }

        .more-content {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }

        .feature {
            text-align: center;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
        }

        .feature i {
            font-size: 24px;
            margin-bottom: 10px;
            color: #4ecdc4;
        }

        .feature h3 {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .feature p {
            opacity: 0.9;
            font-size: 12px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 10px;
            font-size: 14px;
            opacity: 0.8;
        }

        @media (max-width: 500px) {
            .container {
                border-radius: 15px;
            }

            .header {
                padding: 25px 15px 15px;
            }

            .avatar {
                width: 80px;
                height: 80px;
            }

            .explore-title {
                font-size: 24px;
            }

            .post-title {
                font-size: 18px;
            }

            .explore-description, .sharing-message {
                font-size: 14px;
            }

            .action-btn {
                padding: 14px 20px;
                font-size: 14px;
            }

            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="explore-badge">Explore Mode</div>

            <div class="avatar">
                <img src="{{ $thumbnailUrl ?? asset('images/explore-default.jpg') }}" alt="Explore preview">
            </div>

            <div class="explore-title">Venusnap Explore</div>

            @if($post)
            <div class="post-title">
                Featured: {{ $post->album->name ?? 'Exclusive Content' }}
            </div>
            @endif

            <div class="explore-description">
                {{ $description }}
            </div>

            <div class="sharing-message">
                Discover amazing content from creators worldwide!
            </div>
        </div>

        <div class="content">
            <div class="action-buttons">
                <a href="venusnap://explore{{ $postId ? '/' . $postId : '' }}{{ $ref ? '?ref=' . $ref : '' }}" class="action-btn explore-btn">
                    <i class="fas fa-compass"></i> Open in Venusnap App
                </a>

                <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="action-btn download-btn">
                    <i class="fab fa-google-play"></i> Download the App
                </a>
            </div>

            <div class="more-content">
                <h2 style="text-align: center; margin-bottom: 15px; font-size: 18px;">What's in Explore?</h2>

                <div class="features">
                    <div class="feature">
                        <i class="fas fa-globe"></i>
                        <h3>Discover</h3>
                        <p>Global content</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-star"></i>
                        <h3>Curated</h3>
                        <p>Best picks</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-users"></i>
                        <h3>Community</h3>
                        <p>Join creators</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-bolt"></i>
                        <h3>Instant</h3>
                        <p>Real-time updates</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2025 Venusnap. All rights reserved.</p>
    </div>

    <script>
        // Simple animation for the action buttons
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.action-btn');
            buttons.forEach((button, index) => {
                button.style.opacity = 0;
                button.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    button.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    button.style.opacity = 1;
                    button.style.transform = 'translateY(0)';
                }, 300 + (index * 200));
            });

            // Auto-redirect to app if on mobile
            const isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
            if (isMobile) {
                setTimeout(() => {
                    window.location.href = "venusnap://explore{{ $postId ? '/' . $postId : '' }}{{ $ref ? '?ref=' . $ref : '' }}";
                }, 1500);

                // Fallback to Play Store if app not installed
                setTimeout(() => {
                    window.location.href = "https://play.google.com/store/apps/details?id=com.venusnap.app";
                }, 2500);
            }
        });
    </script>
</body>
</html>
