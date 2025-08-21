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
        }

        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #ffdd40;
            margin: 0 auto 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .album-name {
            font-size: 24px;
            margin-bottom: 10px;
            font-weight: 700;
            color: #ffdd40;
        }

        .post-description {
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

        .download-btn {
            background: #fff;
            color: #333;
        }

        .download-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            background: #ffdd40;
        }

        .open-btn {
            background: #ffdd40;
            color: #333;
        }

        .open-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
            background: #fff;
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
            color: #ffdd40;
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

            .album-name {
                font-size: 20px;
            }

            .post-description, .sharing-message {
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
            <div class="avatar">
                <img src="{{ $thumbnailUrl ?? 'https://images.unsplash.com/photo-1554080353-321e452ccf19?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80' }}" alt="Post preview">
            </div>

            <div class="album-name">{{ $post->album->name ?? 'Exclusive Album' }}</div>

            @if(!empty($post->description))
            <div class="post-description">
                "{{ $post->description }}"
            </div>
            @endif

            <div class="sharing-message">
                @if(isset($share))
                {{ $share->user->name }} has shared this photo with you
                @else
                You've been invited to view this private photo
                @endif
            </div>
        </div>

        <div class="content">
            <div class="action-buttons">
                <a href="https://play.google.com/store/apps/details?id=com.venusnap.app" class="action-btn download-btn">
                    <i class="fab fa-google-play"></i> Download the App
                </a>

                <a href="venusnap://post/{{ $post->id }}/media/{{ $media->id }}?ref={{ request('ref') }}" class="action-btn open-btn">
                    <i class="fas fa-external-link-alt"></i> Open in App
                </a>
            </div>

            <div class="more-content">
                <h2 style="text-align: center; margin-bottom: 15px; font-size: 18px;">Why Join Venusnap?</h2>

                <div class="features">
                    <div class="feature">
                        <i class="fas fa-images"></i>
                        <h3>Premium Photos</h3>
                        <p>Exclusive content</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-lock"></i>
                        <h3>Private Access</h3>
                        <p>Secure sharing</p>
                    </div>
                    <div class="feature">
                        <i class="fas fa-user-friends"></i>
                        <h3>Connect</h3>
                        <p>Join creators</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>&copy; 2023 Venusnap. All rights reserved.</p>
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
        });
    </script>
</body>
</html>
