<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $description = "Check out this post from the '".($post->album->name ?? 'album')."' on Venusnap.";
    @endphp

    <title>{{ $post->album->name ?? 'Venusnap Post' }}</title>
    <meta property="og:title" content="{{ $post->album->name ?? 'Venusnap Post' }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:image" content="{{ $thumbnailUrl ?? asset('default.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="article">
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
            background: linear-gradient(rgba(15, 23, 42, 0.9), rgba(30, 27, 75, 0.9)),
                        url('https://images.unsplash.com/photo-1618005198919-d3d4b5a92ead?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
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
            backdrop-filter: blur(20px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin: 20px 0;
        }

        .header {
            text-align: center;
            padding: 40px 30px 30px;
            background: linear-gradient(135deg, rgba(124, 58, 237, 0.3) 0%, rgba(139, 92, 246, 0.2) 100%);
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
            background: url('https://images.unsplash.com/photo-1618005198919-d3d4b5a92ead?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1974&q=80');
            background-size: cover;
            background-position: center;
            opacity: 0.1;
            z-index: -1;
        }

        .avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.3);
            margin: 0 auto 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.1);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .album-name {
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 800;
            background: linear-gradient(to right, #fff, #d8b4fe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .post-description {
            font-size: 16px;
            opacity: 0.9;
            max-width: 400px;
            margin: 0 auto 20px;
            line-height: 1.6;
            color: #e2e8f0;
        }

        .sharing-message {
            font-size: 16px;
            opacity: 0.9;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            border-left: 4px solid #7c3aed;
            backdrop-filter: blur(10px);
        }

        .content {
            display: flex;
            flex-direction: column;
            padding: 30px;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin: 25px 0;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 18px 30px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
            font-size: 16px;
            border: 2px solid transparent;
        }

        .action-btn i {
            margin-right: 12px;
            font-size: 22px;
        }

        .download-btn {
            background: #7c3aed;
            color: white;
            border-color: #8b5cf6;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(124, 58, 237, 0.4);
            background: #6d28d9;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .stat-item {
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .stat-number {
            font-size: 20px;
            font-weight: 800;
            color: #8b5cf6;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 12px;
            opacity: 0.8;
            color: #cbd5e1;
        }

        .more-content {
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
        }

        .section-title {
            text-align: center;
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: 700;
            color: #d8b4fe;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 15px;
            margin: 25px 0;
        }

        .feature {
            text-align: center;
            padding: 20px 15px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: transform 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .feature:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.12);
        }

        .feature i {
            font-size: 28px;
            margin-bottom: 12px;
            color: #8b5cf6;
        }

        .feature h3 {
            font-size: 15px;
            margin-bottom: 8px;
            font-weight: 600;
            color: white;
        }

        .feature p {
            opacity: 0.8;
            font-size: 13px;
            line-height: 1.4;
            color: #cbd5e1;
        }

        .footer {
            text-align: center;
            padding: 25px;
            margin-top: 20px;
            font-size: 14px;
            opacity: 0.8;
            color: #94a3b8;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
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
            .container {
                border-radius: 15px;
                margin: 10px;
            }

            .header {
                padding: 30px 20px 20px;
            }

            .avatar {
                width: 100px;
                height: 100px;
            }

            .album-name {
                font-size: 24px;
            }

            .post-description, .sharing-message {
                font-size: 14px;
            }

            .action-btn {
                padding: 16px 25px;
                font-size: 15px;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: repeat(3, 1fr);
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container fade-in">
        <div class="header">
            <div class="avatar">
                <img src="{{ $thumbnailUrl ?? 'https://images.unsplash.com/photo-1554080353-321e452ccf19?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80' }}" alt="Post preview">
            </div>

            <div class="album-name">{{ $post->album->name ?? 'Exclusive Album' }}</div>

            @if (!empty($post->description))
            <div class="post-description">
                "{{ $post->description }}"
            </div>
            @else
                <div class="post-description">
                    Check out this snap from the {{ $post->album->name }} album!
                </div>
            @endif

            <div class="sharing-message">
                Post from <strong>{{ $post->album->name ?? 'Exclusive Album' }}</strong>
            </div>
        </div>

        <div class="content">
            <div class="action-buttons">
                <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="action-btn download-btn">
                    <i class="fab fa-google-play"></i> Download Venusnap App
                </a>
            </div>


            <div class="more-content">
                <h2 class="section-title">Why Join Venusnap?</h2>

                <div class="features">
                    <div class="feature">
                        <i class="fas fa-images"></i>
                        <h3>Premium Content</h3>
                        <p>Exclusive photos from creators</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-lock"></i>
                        <h3>Private Access</h3>
                        <p>Secure and private sharing</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-user-friends"></i>
                        <h3>Connect</h3>
                        <p>Join creative community</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer fade-in">
        <p>&copy; 2025 Venusnap. All rights reserved.</p>
        <p style="margin-top: 8px; font-size: 12px; opacity: 0.6;">A home for visual creators</p>
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

            // Add hover effects to features
            const features = document.querySelectorAll('.feature');
            features.forEach(feature => {
                feature.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px) scale(1.02)';
                });
                feature.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });
    </script>
</body>
</html>
