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
            position: relative;
        }

        .shared-by {
            position: absolute;
            top: 15px;
            right: 20px;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 14px;
        }

        .shared-by i {
            margin-right: 8px;
            color: #ffdd40;
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
            line-height: 1.5;
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
            position: relative;
        }

        .post-preview img {
            width: 100%;
            display: block;
            max-height: 400px;
            object-fit: cover;
        }

        .preview-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(transparent, rgba(0, 0, 0, 0.8));
            padding: 20px;
            color: white;
        }

        .preview-overlay h3 {
            font-size: 20px;
            margin-bottom: 5px;
        }

        .preview-overlay p {
            font-size: 14px;
            opacity: 0.9;
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
            padding: 30px;
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
            line-height: 1.5;
        }

        .app-badges {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin: 20px 0;
        }

        .app-badge {
            display: inline-flex;
            align-items: center;
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
            font-size: 20px;
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

        .feature h3 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .feature p {
            opacity: 0.9;
            font-size: 14px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            margin-top: 20px;
            font-size: 14px;
            opacity: 0.8;
        }

        .app-open-section {
            text-align: center;
            margin: 20px 0;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
        }

        .open-app-btn {
            display: inline-flex;
            align-items: center;
            background: #ffdd40;
            color: #333;
            padding: 12px 25px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            margin-top: 10px;
        }

        .open-app-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            background: #fff;
        }

        .open-app-btn i {
            margin-right: 8px;
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
                justify-content: center;
            }

            .shared-by {
                position: relative;
                top: 0;
                right: 0;
                justify-content: center;
                margin-bottom: 15px;
            }

            .header {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($share))
            <div class="shared-by">
                <i class="fas fa-share-alt"></i> Shared by {{ $share->user->name }}
            </div>
            @endif

            <div class="logo">
                <i class="fas fa-camera"></i> Venusnap
            </div>
            <h1>Exclusive Content Shared With You</h1>
            <p class="description">
                @if(isset($share))
                {{ $share->user->name }} has shared a special post with you on Venusnap
                @else
                You've been invited to view a private post from "{{ $post->album->name ?? 'a Venusnap album' }}"
                @endif
            </p>
        </div>

        <div class="content">
            <div class="post-preview">
                <img src="{{ $thumbnailUrl ?? 'https://images.unsplash.com/photo-1554080353-321e452ccf19?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80' }}" alt="Post preview">
                <div class="preview-overlay">
                    <h3>{{ $post->album->name ?? 'Exclusive Content' }}</h3>
                    <p>View this content and more in the Venusnap app</p>
                </div>
            </div>

            <div class="app-open-section">
                <p>If you already have Venusnap installed, open the app to view this content</p>
                <a href="venusnap://post/{{ $post->id }}/media/{{ $media->id }}?ref={{ request('ref') }}" class="open-app-btn">
                    <i class="fas fa-external-link-alt"></i> Open in Venusnap App
                </a>
            </div>

            <div class="details">
                <div class="detail-card">
                    <i class="fas fa-images"></i>
                    <h3>High Quality Content</h3>
                    <p>Premium photos and videos</p>
                </div>
                <div class="detail-card">
                    <i class="fas fa-lock"></i>
                    <h3>Private Access</h3>
                    <p>Exclusive content shared with you</p>
                </div>
                <div class="detail-card">
                    <i class="fas fa-user-friends"></i>
                    <h3>Connect</h3>
                    <p>Join our community of creators</p>
                </div>
            </div>

            <div class="download-section">
                <h2>Don't have Venusnap yet?</h2>
                <p>Download the app to view this exclusive content and connect with creators</p>

                <div class="app-badges">
                    <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="app-badge">
                        <i class="fab fa-google-play"></i> Google Play
                    </a>
                    <a href="https://apps.apple.com/app/venusnap" class="app-badge">
                        <i class="fab fa-apple"></i> App Store
                    </a>
                </div>
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
        // No automatic redirects - let the user choose when to download the app

        // Simple animation for the app badges
        document.addEventListener('DOMContentLoaded', function() {
            const badges = document.querySelectorAll('.app-badge');
            badges.forEach((badge, index) => {
                badge.style.opacity = 0;
                badge.style.transform = 'translateY(20px)';

                setTimeout(() => {
                    badge.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    badge.style.opacity = 1;
                    badge.style.transform = 'translateY(0)';
                }, 300 + (index * 200));
            });
        });
    </script>
</body>
</html>
